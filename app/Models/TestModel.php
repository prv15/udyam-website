<?php

declare(strict_types=1);

namespace App\Models;

class TestModel extends Model
{
    public function getTime(): ?array
    {
        return $this->db->first("SELECT NOW() AS server_time");
    }
}