<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Role extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'permissions' => 'array'
    ];

    protected $appends = [
        'homeUrl',
    ];

    public function checkPermissions(Request $request): bool
    {
        return in_array($request->path(), $this->permissions);
    }

    public function getHomeUrlAttribute(): string
    {
        $urls = [
            RoleEnum::ADMIN->name => '/',
            RoleEnum::MANAGER->name => '/',
            RoleEnum::USER->name => '/gpt/settings',
        ];

        return $urls[$this->name];
    }
}
