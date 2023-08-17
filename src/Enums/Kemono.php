<?php

namespace Kotik\KemonoBirthday\Enums;

enum Kemono
{
    case Unknown;
    case Shoebill;
    case NothernOwl;
    case SilverFox;
    case Tsuchinoko;
    case Kitakitsune;
    case SmallClawedOtter;
    case IslandFox;
    case AraiSan;
    case AmurTiger;

    public function name(): string
    {
        return match($this) {
            self::Unknown => 'Незнакомка',
            self::Shoebill => 'Китоглав',
            self::NothernOwl => 'Северная белолицая сова',
            self::SilverFox => 'Черно-бурая лиса',
            self::Tsuchinoko => 'Цутиноко',
            self::Kitakitsune => 'Рыжая лиса',
            self::SmallClawedOtter => 'Выдра',
            self::IslandFox => 'Островная лиса',
            self::AraiSan => 'Енот',
            self::AmurTiger => 'Амурский тигр'
        };
    }
}
