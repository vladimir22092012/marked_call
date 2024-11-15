<?php

namespace App\Http\Controllers;

use App\Http\Requests\Marked\StartMarkedRequest;
use App\Jobs\StarterMarkupDataJob;
use App\Models\User;
use App\Services\Owners;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
            if (Auth::user()) {
                $authUser = Auth::user();
            } else {
                $authUser = User::query()->first();
            }
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
                if (count($items) > 0) {
                    $data['summary_date_interval'] = [
                        date('d.m.Y', $items[0]->datetime_event),
                        date('d.m.Y', $items[0]->datetime_event)
                    ];
                }
            } else {
                $start = Carbon::parse($data['date_interval'][0])->subHours(3)->format('d.m.Y H:i:s');
                $end = Carbon::parse($data['date_interval'][1])->subHours(3)->format('d.m.Y H:i:s');
                $items = Owners::getCalls($data['owner'], null, [strtotime($start), strtotime($end)]);
                $data['summary_date_interval'] = [
                    Carbon::parse($data['date_interval'][0])->format('Y-m-d'),
                    Carbon::parse($data['date_interval'][1])->format('Y-m-d')
                ];
            }
            if (count($items) > 0) {
                StarterMarkupDataJob::dispatchSync($items->toArray(), $data, $authUser);//->onQueue('marked_call');

                return $this->success(['status' => 'ok', 'items' => $items]);
            } else {
                return $this->success(['status' => 'error', 'custom_message' => 'Нет звонков!']);
            }
        } catch (\Exception $e) {
            return $this->error(500, 'Внутренняя ошибка сервера', $e);
        }
    }

}
