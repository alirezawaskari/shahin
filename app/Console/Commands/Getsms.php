<?php

namespace App\Console\Commands;

use App\Models\sms;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class Getsms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get new sms from api';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        //get messages sent by users && create record in sms table
        $linenumber = config('services.sms.linenumber');

        $headers = [
            'apikey' => config('services.sms.apikey'),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $client = new Client([
            'headers' => $headers
        ]);
        $body = [
            'linenumber' => $linenumber,
            'isread' => 0
        ];
        $r = $client->request('POST', 'http://api.parsasms.com/v2/sms/recive', [
            'form_params' => $body
        ]);

        $response = json_decode($r->getBody()->getContents());
        if ($response->result == 'success'){
            if (!is_null($response->list)){
                foreach (array_reverse($response->list) as $item) {
                    sms::create([
                        'mid' => $item->MessageID,
                        'recipient_number' => $item->RecipientNumber,
                        'sender_number' => $item->SenderNumber,
                        'body' => trim($item->Body),
                        'date' => Carbon::createFromTimestamp( $item->ReceiveDate)->toDateTimeString(),
                        'status' => 0,
                    ]);
                }
            }
            $this->info("command done!");
        }else{
            $this->info("command error!");
        }

    }
}
