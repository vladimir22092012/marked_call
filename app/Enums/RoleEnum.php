<?php
namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'Админ';

    case MANAGER = 'Менеджер';

    case USER = 'Пользователь';

}
