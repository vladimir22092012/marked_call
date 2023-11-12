<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Отдаём в случае ошибки
     *
     * @param  int  $code | http код ответа
     * @param  string  $message | Сообщение с ошибкой
     * @param  \Exception  $ex | Трассировка
     */
    public function error(int $code, string $message, \Exception $ex): JsonResponse
    {
        return response()
            ->json(compact('code', 'message', 'ex'))
            ->header('Content-Type', 'application/json');
    }

    /**
     * @param  array|null  $data | массив для ответа клиенту
     * @param  string|null  $message | Сообщение с успешным ответом, может быть пустым
     */
    public function success(array $data = null, string $message = null, int $code = null): JsonResponse
    {
        $code ??= 200;
        $message ??= '';
        $data ??= [];

        return response()
            ->json(compact('code', 'message', 'data'))
            ->setStatusCode($code)
            ->header('Content-Type', 'application/json');
    }
}
