<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;

    public function __construct($owner)
    {
        $this->table = "events_call_".sprintf('%08d', $owner);
    }

    protected $connection = 'ai_mysql';

}
