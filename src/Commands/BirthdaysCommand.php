<?php

namespace Kotik\KemonoBirthday\Commands;

use DateTime;
use Kotik\KemonoBirthday\Commands\BaseCommand;
use Kotik\KemonoBirthday\Objects\Birthday;

class BirthdaysCommand extends BaseCommand
{
    public string $name = 'birthdays';

    public string $description = 'Print birthdays of Kemono friends';

    public function execute(): void
    {
        $birthdays = $this->app->config()['birthdays'];

        $birthdays = array_map(
            fn(array $data, int $id) => new Birthday($id, $data),
            $birthdays,
            array_keys($birthdays)
        );

        $maxTabs = array_reduce(
            $birthdays,
            fn(int $carry, Birthday $birthday) => max($carry, ceil(mb_strlen($birthday->name) / 8)),
            1
        );

        usort($birthdays, function (Birthday $a, Birthday $b) {
            $timestampA = $a->date
                ->setDate((new DateTime())->format('Y'), $a->date->format('n'), $a->date->format('j'))
                ->getTimestamp();

            $timestampB = $b->date
                ->setDate((new DateTime())->format('Y'), $b->date->format('n'), $b->date->format('j'))
                ->getTimestamp();

            if ($timestampA > $timestampB)
                return 1;
            if ($timestampA < $timestampB)
                return -1;
            return 0;
        });

        foreach ($birthdays as $birthday) {
            echo "$birthday->name";

            $tabLength = floor(mb_strlen($birthday->name) / 8);

            for ($i = 0; $i < $maxTabs - $tabLength; $i++)
                echo "\t";

            $date = $birthday->date->format('d.m');

            echo "$date\t";

            $diff = $birthday->date->diff(new DateTime());

            if ($diff->invert)
                echo "$diff->days days left\n";
            else
                echo "PASSED\n";
        }
    }
}