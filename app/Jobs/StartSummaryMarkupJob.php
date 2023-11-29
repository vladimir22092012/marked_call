<?php

namespace App\Jobs;

use App\Events\LayoutNotifyEvent;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StartSummaryMarkupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    public $authUser;

    public function __construct($data, $authUser)
    {
        $this->data = $data;
        $this->authUser = $authUser;
    }

    public function handle(): void
    {
        $text = "<p>Разметка по оунеру <b>{$this->data['owner']}</b> завершена.</p>";
        if ($this->data['type'] == 'call') {
            $text .= "<p>Звонок <b>#{$this->data['call_id']}</b> успешно размечен.</p>";
        } else {
            $start = Carbon::parse($this->data['date_interval'][0])->format('d.m.Y H:i');
            $end = Carbon::parse($this->data['date_interval'][1])->format('d.m.Y H:i');
            $text .= "<p>Звонки за период <b>$start - $end</b> успешно размечены.</p>";
        }
        LayoutNotifyEvent::dispatch('marked_call.end', $this->authUser->id, $text);
        Log::debug('Start Summary: '.Carbon::now()->format('d-m-Y H:i:s'));
        Log::debug(var_export($this->data, true));
        $data = [
            'dateStart' => $this->data['summary_date_interval'][0],
            'dateEnd' => $this->data['summary_date_interval'][1],
            'ownerId' => $this->data['owner'],
            'daterange' => $this->data['summary_date_interval'][0].'-'.$this->data['summary_date_interval'][1],
        ];

        $request = Http::post('https://lk.sales-management-center.com/calls/summary', $data);
        Log::debug(var_export($request->body(), true));
        Log::debug('End Summary: '.Carbon::now()->format('d-m-Y H:i:s'));

    }

}
