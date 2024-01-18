<?php

namespace Kotik\KemonoBirthday\Commands;

use Kotik\KemonoBirthday\App;

abstract class BaseCommand
{
    public string $name = '';

    public string $description = '';

    public function __construct(
        public App $app
    )
    {
    }

    public function execute(): void
    {
    }
}