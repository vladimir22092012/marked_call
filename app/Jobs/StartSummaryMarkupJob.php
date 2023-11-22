<?php

namespace App\Jobs;

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

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        Log::debug('Start Summary: '.Carbon::now()->format('d-m-Y H:i:s'));
        Log::debug(var_export($this->data, true));
        $data = [
            'dateStart' => $this->data['date_interval'][0],
            'dateEnd' => $this->data['date_interval'][1],
            'ownerId' => $this->data['owner']
        ];

        $request = Http::post('https://lk.sales-management-center.com/calls/summary-after-markup', $data);
        Log::debug(var_export($request->body(), true));
        Log::debug('End Summary: '.Carbon::now()->format('d-m-Y H:i:s'));

    }

}
