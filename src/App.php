<?php

namespace Kotik\KemonoBirthday;

use Carbon\Carbon;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Discord\Parts\Embed\Image;
use Discord\Parts\User\Member;
use Discord\WebSockets\Intents;
use Kotik\KemonoBirthday\Commands\BaseCommand;
use Kotik\KemonoBirthday\Objects\Birthday;

class App
{
    protected array $config = [];

    protected int $messagesOrdered = 0;

    protected int $messagesSent = 0;

    public array $commands = [];

    public function config()
    {
        if (!$this->config)
            $this->config = include(__DIR__ . '/../config.php');

        return $this->config;
    }

    protected function filterGuilds(Discord $discord): array
    {
        $result = [];

        foreach (
            $discord->guilds->filter(fn($guild) => in_array($guild->name, array_keys($this->config()['guilds'])))
            as $guild
        ) {
            $result[] = [
                'guild'    => $guild,
                'channels' => $guild
                    ->channels
                    ->filter(fn($channel) => in_array($channel->name, $this->config()['guilds'][$guild->name]))
            ];
        }

        return $result;
    }

    protected function makeMessage(Discord $discord, Birthday $birthday, Member $member): MessageBuilder
    {
        return MessageBuilder::new()
                             ->addEmbed(new Embed($discord, [
                                 'title'       => 'День рождения!',
                                 'image'       => new Image($discord, [
                                     'url' => $this->config()['host'] . '/images/' . $birthday->image
                                 ]),
                                 'description' => "Сегодня день рождения у нашей любимой <@!$birthday->id>!\nДавайте поздравим нашу кемошку и пожелаем ей всего самого лучшего.\n@everyone",
                                 'fields'      => [
                                     new Field($discord, [
                                         'name'   => 'Кемошка',
                                         'value'  => $birthday->kemono->name(),
                                         'inline' => true
                                     ]),
                                     new Field($discord, [
                                         'name'   => 'Возраст',
                                         'value'  => (new Carbon($birthday->date))->longAbsoluteDiffForHumans(),
                                         'inline' => true
                                     ]),
                                     new Field($discord, [
                                         'name'   => 'С нами',
                                         'value'  => (new Carbon($member->joined_at))->longAbsoluteDiffForHumans(2),
                                         'inline' => true
                                     ]),
                                 ]
                             ]));
    }

    public function run()
    {
        Carbon::setLocale('ru-RU');

        $discord = new Discord([
            'token'          => $this->config()['token'],
            'intents'        => Intents::getDefaultIntents() | Intents::GUILD_MEMBERS,
            'loadAllMembers' => true
        ]);

        $discord->on('ready', function (Discord $discord) {
            $filteredGuilds = $this->filterGuilds($discord);

            foreach ($filteredGuilds as $group) {
                $guild = $group['guild'];

                foreach ($group['channels'] as $channel) {
                    foreach ($this->config()['birthdays'] as $id => $birthday) {
                        $member = $guild->members->filter(fn($member) => $member->user->id == $id)->first();

                        $birthday = new Birthday($id, $birthday);

                        $channel->sendMessage(
                            $this->makeMessage($discord, $birthday, $member)
                        );
                    }
                }
            }
        });
    }

    public function schedule()
    {
        Carbon::setLocale('ru-RU');

        $discord = new Discord([
            'token'          => $this->config()['token'],
            'intents'        => Intents::getDefaultIntents() | Intents::GUILD_MEMBERS,
            'loadAllMembers' => true
        ]);

        $discord->on('ready', function (Discord $discord) {
            $filteredGuilds = $this->filterGuilds($discord);

            foreach ($filteredGuilds as $group) {
                $guild = $group['guild'];

                foreach ($group['channels'] as $channel) {
                    foreach ($this->config()['birthdays'] as $id => $birthday) {
                        $birthday = new Birthday($id, $birthday);

                        if (!($diff = (new Carbon($birthday->date))->diff()) || $diff->m != 0 || $diff->d > 0)
                            continue;

                        $member = $guild->members->filter(fn($member) => $member->user->id == $id)->first();

                        $this->messagesOrdered++;

                        $channel->sendMessage(
                            $this->makeMessage($discord, $birthday, $member)
                        )->done(function () use ($discord) {
                            $this->messagesSent++;
                            $this->checkSent($discord);
                        });
                    }
                }
            }
        });
    }

    protected function checkSent($discord): void
    {
        if ($this->messagesSent >= $this->messagesOrdered)
            $discord->close();
    }

    public function test(): void
    {
        foreach ($this->config()['birthdays'] as $id => $birthday) {
            $birthday = new Birthday($id, $birthday);

            if (!($diff = (new Carbon($birthday->date))->diff()) || $diff->m != 0 || $diff->d > 0)
                continue;

            echo $birthday->name . "\n";
        }
    }

    public function console(): void
    {
        $this->commands = $this->findCommands();

        if ($_SERVER['argc'] < 2) {
            $this->executeCommand('help');
            return;
        }

        [$console, $command] = $_SERVER['argv'];
        $this->executeCommand($command);
    }

    protected function findCommands(): array
    {
        $files = scandir(__DIR__ . '/Commands');
        $commands = [];

        foreach ($files as $file) {
            if (in_array($file, ['.', '..']))
                continue;

            [$name] = explode('.', $file);

            $class = "Kotik\\KemonoBirthday\\Commands\\$name";

            if (!is_subclass_of($class, BaseCommand::class))
                continue;

            $command = new $class($this);

            $commands[$command->name] = $class;
        }

        return $commands;
    }

    protected function executeCommand(string $name): void
    {
        if (!isset($this->commands[$name])) {
            echo "Command \"$name\" not exists.\n";
            return;
        }

        $command = new $this->commands[$name]($this);
        $command->execute();
    }
}