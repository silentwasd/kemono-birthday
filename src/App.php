<?php

namespace Kotik\KemonoBirthday;

use Carbon\Carbon;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Discord\Parts\Embed\Image;
use Discord\WebSockets\Intents;

class App
{
    protected array $config = [];

    protected int $messagesOrdered = 0;

    protected int $messagesSent = 0;

    public function config()
    {
        if (!$this->config)
            $this->config = include(__DIR__ . '/../config.php');

        return $this->config;
    }

    public function run()
    {
        Carbon::setLocale('ru-RU');

        $discord = new Discord([
            'token' => $this->config()['token'],
            'intents' => Intents::getDefaultIntents() | Intents::GUILD_MEMBERS,
            'loadAllMembers' => true
        ]);

        $discord->on('ready', function (Discord $discord) {
            foreach ($discord->guilds as $guild) {
                echo $guild->name . "\n";

                foreach ($guild->channels as $channel) {
                    if ($channel->type != Channel::TYPE_TEXT || $channel->name != $this->config()['channel'])
                        continue;


                    foreach ($this->config()['birthdays'] as $id => $birthday) {
                        $member = $guild->members->filter(fn($member) => $member->user->id == $id)->first();

                        $channel->sendMessage(
                            MessageBuilder::new()
                                ->addEmbed(new Embed($discord, [
                                    'title' => 'День рождения!',
                                    'image' => new Image($discord, [
                                        'url' => 'https://kemono.vrkitty.ru/images/' . $birthday['image']
                                    ]),
                                    'description' => "Сегодня день рождения у нашей любимой <@!$id>!\nДавайте поздравим нашу кемошку и пожелаем ей всего самого лучшего.",
                                    'fields' => [
                                        new Field($discord, [
                                            'name' => 'Кемошка',
                                            'value' => $birthday['kemono']->name(),
                                            'inline' => true
                                        ]),
                                        new Field($discord, [
                                            'name' => 'Возраст',
                                            'value' => (new Carbon($birthday['date']))->longAbsoluteDiffForHumans(),
                                            'inline' => true
                                        ]),
                                        new Field($discord, [
                                            'name' => 'С нами',
                                            'value' => (new Carbon($member->joined_at))->longAbsoluteDiffForHumans(2),
                                            'inline' => true
                                        ]),
                                    ]
                                ]))
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
            'token' => $this->config()['token'],
            'intents' => Intents::getDefaultIntents() | Intents::GUILD_MEMBERS,
            'loadAllMembers' => true
        ]);

        $discord->on('ready', function (Discord $discord) {
            foreach ($discord->guilds as $guild) {
                echo $guild->name . "\n";

                foreach ($guild->channels as $channel) {
                    if ($channel->type != Channel::TYPE_TEXT || $channel->name != $this->config()['channel'])
                        continue;

                    foreach ($this->config()['birthdays'] as $id => $birthday) {
                        if (!($diff = (new Carbon($birthday['date']))->diff()) || $diff->m != 0 || $diff->d > 0)
                            continue;

                        $member = $guild->members->filter(fn($member) => $member->user->id == $id)->first();

                        $this->messagesOrdered++;

                        $channel->sendMessage(
                            MessageBuilder::new()
                                ->addEmbed(new Embed($discord, [
                                    'title' => 'День рождения!',
                                    'image' => new Image($discord, [
                                        'url' => 'https://kemono.vrkitty.ru/images/' . $birthday['image']
                                    ]),
                                    'description' => "Сегодня день рождения у нашей любимой <@!$id>!\nДавайте поздравим нашу кемошку и пожелаем ей всего самого лучшего.",
                                    'fields' => [
                                        new Field($discord, [
                                            'name' => 'Кемошка',
                                            'value' => $birthday['kemono']->name(),
                                            'inline' => true
                                        ]),
                                        new Field($discord, [
                                            'name' => 'Возраст',
                                            'value' => (new Carbon($birthday['date']))->longAbsoluteDiffForHumans(),
                                            'inline' => true
                                        ]),
                                        new Field($discord, [
                                            'name' => 'С нами',
                                            'value' => (new Carbon($member->joined_at))->longAbsoluteDiffForHumans(),
                                            'inline' => true
                                        ]),
                                    ]
                                ]))
                        )->done(function () use ($discord) {
                            $this->messagesSent++;
                            $this->checkSent($discord);
                        });
                    }
                }
            }
        });
    }

    protected function checkSent($discord)
    {
        if ($this->messagesSent >= $this->messagesOrdered)
            $discord->close();
    }

}