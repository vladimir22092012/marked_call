<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallUserGkProject extends Model
{
    use HasFactory;

    protected $table = 'call_user_gk_project';

    protected $connection = 'lk_sales_mysql';
}
