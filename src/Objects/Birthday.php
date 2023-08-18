<?php

namespace Kotik\KemonoBirthday\Objects;

use DateTime;
use Kotik\KemonoBirthday\Enums\Kemono;

class Birthday
{
    public int $id;

    public DateTime $date;

    public Kemono $kemono;

    public string $image;

    public function __construct(int $id, array $data)
    {
        $this->id     = $id;
        $this->date   = $data['date'];
        $this->kemono = $data['kemono'];
        $this->image  = $data['image'];
    }
}