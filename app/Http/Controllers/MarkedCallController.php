<?php

namespace App\Http\Controllers;

use App\Http\Requests\Marked\StartMarkedRequest;
use App\Jobs\MarkedCall;
use App\Models\CallUserGkProject;
use App\Models\Events;
use App\Models\LkUsers;
use App\Models\Owner;
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
                $start = Carbon::parse($data['date_interval'][0])->unix();
                $end = Carbon::parse($data['date_interval'][1])->unix();
                $items = Owners::getCalls($data['owner'], null, [$start, $end]);
            }

            foreach ($items as $event) {
                $user = LkUsers::query()->where('owner', '=', $data['owner'])->first();
                $gkprojectid = 0;
                if ($user) {
                    $projects = CallUserGkProject::query()->where('user_id', '=', $user->id)->get();
                    foreach ($projects as $project) {
                        $gkprojectid = $project->project_id;
                        if ((isset($event->project_id)) && ($project->division_id == $event->project_id)) {
                            $gkprojectid = $project->project_id;
                            break;
                        }
                    }
                }
                MarkedCall::dispatch(
                    $event->toArray(),
                    $gkprojectid,
                    $data['owner'],
                    $request->user()
                )->onQueue('marked_call');
            }

            return $this->success(['status' => 'ok']);
        } catch (\Exception $e) {
            return $this->error(500, 'Внутренняя ошибка сервера', $e);
        }
    }
}
