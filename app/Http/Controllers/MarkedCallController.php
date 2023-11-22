<?php

namespace App\Http\Controllers;

use App\Http\Requests\Marked\StartMarkedRequest;
use App\Jobs\MarkedCall;
use App\Jobs\StarterMarkupDataJob;
use App\Jobs\StartSummaryMarkupJob;
use App\Models\CallUserGkProject;
use App\Models\Events;
use App\Models\LkUsers;
use App\Models\Owner;
use App\Models\User;
use App\Services\Owners;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
                    $data['date_interval'] = [
                        date('Y-m-d', $items[0]->datetime_event),
                        date('Y-m-d', $items[0]->datetime_event)
                    ];
                }
            } else {
                $start = Carbon::parse($data['date_interval'][0]);
                $end = Carbon::parse($data['date_interval'][1]);
                $data['date_interval'] = [
                    $start->format('Y-m-d'),
                    $end->format('Y-m-d')
                ];
                $items = Owners::getCalls($data['owner'], null, [$start->unix(), $end->unix()]);
            }

            StarterMarkupDataJob::dispatch($items->toArray(), $data, $authUser)->onQueue('marked_call');

            return $this->success(['status' => 'ok', 'items' => $items]);
        } catch (\Exception $e) {
            return $this->error(500, 'Внутренняя ошибка сервера', $e);
        }
    }
}
