<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use phplusir\smsir\Smsir;

class SendSMS extends Job
{
    const TEMPLATES = [
        'verification' => 3330,
    ];
    private $params = [];
    private $template = '';
    private $receiver = '';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params, $template, $receiver)
    {
        $this->params = $params;
        $this->template = $template;
        $this->receiver = $receiver;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = Smsir::ultraFastSend($this->params, $this->template, $this->receiver);
        if (array_has($response, 'IsSuccessful') && $response['IsSuccessful'] === false) {
            
        }
    }
}
