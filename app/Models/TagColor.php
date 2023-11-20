<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagColor extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = false;

    public int $owner;

    public function set_table($owner): void
    {
        $this->owner = $owner;
        $this->table = "tag_color_".sprintf('%08d', $this->owner);
    }

    protected $connection = 'ai_mysql';

}
