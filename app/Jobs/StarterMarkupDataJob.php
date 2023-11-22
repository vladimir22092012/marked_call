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

class StarterMarkupDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $items;
    private array $data;
    private User $authUser;

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
            if ($user && $eventRecord) {
                $projects = CallUserGkProject::query()->where('user_id', '=', $user->id)->get();
                foreach ($projects as $project) {
                    $gkprojectid = $project->project_id;
                    if ((isset($eventRecord->project_id)) && ($project->division_id == $eventRecord->project_id)) {
                        $gkprojectid = $project->project_id;
                        break;
                    }
                }
            }
            MarkedCall::dispatch($event, $gkprojectid, $this->data['owner'], $this->authUser)->onQueue('marked_call');
        }
        StartSummaryMarkupJob::dispatch($this->data)->onQueue('marked_call');
    }

}