<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    use HasFactory;

    public int $owner;
    public $timestamps = false;


    protected $guarded = [];

    public function set_table($owner): void
    {
        $this->owner = $owner;
        $this->table = "tags_".sprintf('%08d', $this->owner);
    }

    protected $connection = 'ai_mysql';

}
