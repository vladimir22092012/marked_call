<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRecord extends Model
{
    use HasFactory;

    public int $owner;

    public function set_table($owner): void
    {
        $this->owner = $owner;
        $this->table = "events_".sprintf('%08d', $this->owner);
    }

    protected $connection = 'ai_mysql';

}
