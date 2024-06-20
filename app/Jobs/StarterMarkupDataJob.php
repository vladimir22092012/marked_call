<?php

namespace App\Jobs;

use App\Models\CallUserGkProject;
use App\Models\EventRecord;
use App\Models\LkUsers;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class StarterMarkupDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $items;
    public array $data;
    public User $authUser;

    public function __construct($items, $data, $authUser)
    {
        $this->items = $items;
        $this->data = $data;
        $this->authUser = $authUser;
    }

    public function handle(): void
    {
        foreach ($this->items as $event) {
            $user = LkUsers::query()->where('owner', '=', $this->data['owner'])->first();
            $eventRecordModel = new EventRecord();
            $eventRecordModel->set_table($this->data['owner']);
            $eventRecord = $eventRecordModel->newQuery()->where('id', '=', $event['id'])->first();


            $gkprojectid = 0;
            if ($user) {
                $projects = CallUserGkProject::query()->where('user_id', '=', $user->id)->get();
                foreach ($projects as $project) {
                    $gkprojectid = $project->project_id;
                    if ($eventRecord && isset($eventRecord->project_id) && ($project->division_id == $eventRecord->project_id)) {
                        $gkprojectid = $project->project_id;
                        break;
                    }
                }
            }
            MarkedCall::dispatchSync($event, $gkprojectid, $this->data['owner'], $this->authUser);//->onQueue('marked_call');
        }
        StartSummaryMarkupJob::dispatch($this->data, $this->authUser)->onQueue('marked_call');
    }

}
