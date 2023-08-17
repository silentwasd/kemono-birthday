# Kemono Birthday

Install Composer dependencies:

`composer install`

Make config file:
```php
<?php

return [
    'token'  => 'DISCORD_BOT_TOKEN',
    'host'   => 'BASE_HOST',
    'guilds' => [
        'SERVER_NAME' => [
            'CHANNEL_NAME'
        ]
    ],

    'birthdays' => [
        // Every element is user id as key and array of data as value
        123456789012345678 => [
            'date' => new DateTime('1999/01/01 00:00:00'), // birthday date
            'kemono' => Kemono::Shoebill, // Kemono type
            'image' => 'shoebill.jpg' // image of kemono, that must placed under public/images
        ]
];
```

Run `index.php` for test. All birthdays will be shown.

Run `schedule.php` by CRON for notify your Kemono Friends with birthdays!

CRON example (every day at 7 am they check birthdays):

`0 7 * * * /usr/bin/php /path/to/your/project/schedule.php`