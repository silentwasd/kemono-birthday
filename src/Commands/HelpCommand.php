<?php

namespace Kotik\KemonoBirthday\Commands;

class HelpCommand extends BaseCommand
{
    public string $name = 'help';

    public string $description = 'Print list of available commands';

    public function execute(): void
    {
        echo "Available commands:\n";

        foreach ($this->app->commands as $name => $class) {
            $command = new $class($this->app);
            echo "$name\t\t\t" . $command->description . "\n";
        }
    }
}