<?php

declare(strict_types=1);

namespace App\Repository;


abstract class BaseRepository
{
    public function __construct(protected \MysqliDb $db)
    {
    }

}
