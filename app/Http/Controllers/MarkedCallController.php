<?php

namespace App\Http\Controllers;

use App\Http\Requests\Marked\StartMarkedRequest;
use App\Jobs\MarkedCall;
use App\Models\Events;
use App\Services\Owners;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use function Termwind\render;

class MarkedCallController extends Controller
{
    /**
     * Показываем форму для выбора параметров разметки
     * @param Request $request
     * @return \Inertia\Response
     */
    public function form(Request $request): \Inertia\Response
    {
        return Inertia::render('Marked/Form', [
            'owners' => Owners::getOwners(),
        ]);
    }

    /**
     * Получили звонки
     * Запустили очередь
     * Вернулись на форму
     * @param StartMarkedRequest $request
     */
    public function startMarked(StartMarkedRequest $request)
    {
        try {
            $data = $request->validated();
            if ($data['type'] == 'interval' && !$data['date_interval']) {
                return $this->success(['status' => 'error', 'errors' => [
                    'date_interval' => 'Не выбран интерва'
                ]]);
            }
            if ($data['type'] == 'call' && !$data['call_id']) {
                return $this->success(['status' => 'error', 'errors' => [
                    'call_id' => 'Введите звонок'
                ]]);
            }

            if ($data['type'] == 'call') {
                $items = Owners::getCalls($data['owner'], $data['call_id']);
            } else {
                $start = Carbon::parse($data['date_interval'][0])->format('d.m.Y H:i:s');
                $end = Carbon::parse($data['date_interval'][1])->format('d.m.Y H:i:s');
                $items = Owners::getCalls($data['owner'], $start.'-'.$end);
            }

            foreach ($items as $item) {
                MarkedCall::dispatch($item['event'], $item['gkprojectid'], $data['owner'])->onQueue('marked_call');
            }

            return $this->success(['status' => 'ok']);
        } catch (\Exception $e) {
            return $this->error(500, 'Внутренняя ошибка сервера', $e);
        }
    }
}
