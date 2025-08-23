<?php

namespace App\Console\Commands;

use nusoap_client;
use App\Models\sms;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use SoapClient;

class Getsms extends Command {
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

    public function __construct() {
        parent::__construct();
    }

    /**
    * Execute the console command.
    *
    * @return mixed
    * @throws \GuzzleHttp\Exception\GuzzleException
    */

    public function handle() {
        $this->getSmsFromIranSmsPanel();
        $this->getSmsFromNeginSmsPanel();
    }

    private function getSmsFromIranSmsPanel() {
        //get messages sent by users && create record in sms table
        $linenumber = config( 'services.iranSms.linenumber' );

        $headers = [
            'apikey' => config( 'services.iranSms.apikey' ),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $client = new Client( [
            'headers' => $headers
        ] );
        $body = [
            'linenumber' => $linenumber,
            'isread' => 0
        ];
        $r = $client->request( 'POST', 'http://api.parsasms.com/v2/sms/recive', [
            'form_params' => $body
        ] );

        $response = json_decode( $r->getBody()->getContents() );
        if ( $response->result == 'success' ) {
            if ( !is_null( $response->list ) ) {
                foreach ( array_reverse( $response->list ) as $item ) {
                    sms::create( [
                        'mid' => $item->MessageID,
                        'recipient_number' => $item->RecipientNumber,
                        'sender_number' => $item->SenderNumber,
                        'body' => trim( $item->Body ),
                        'date' => Carbon::createFromTimestamp( $item->ReceiveDate )->toDateTimeString(),
                        'status' => 0,
                    ] );
                }
            }
            $this->info( 'command done!' );
        } else {
            $this->info( 'command error!' );
        }
    }

    private function getSmsFromNeginSmsPanel() {
        $wsdl = 'http://dev.homais.com/services/sms/index.asmx?WSDL';
        try {
            $client = new SoapClient( $wsdl, [
                'trace' => true,
                'exceptions' => true
            ] );
            $params = [
                'PortalCode' => config( 'services.neginSms.portalcode' ),
                'UserName' => config( 'services.neginSms.username' ),
                'PassWord' => config( 'services.neginSms.password' ),
                'lastItems' => 30,
                'indexID' => -1,
                'resultCode' => 0
            ];
            $response = $client->__soapCall( 'getInbox', [ $params ] );
            foreach ( json_decode($response->getInboxResult) as $item ) {
                if (!SMS::where('mid' , $item->id)->exists()) {
                    sms::create( [
                        'mid' => $item->id,
                        'recipient_number' => $item->Number,
                        'sender_number' => $item->Mobile,
                        'body' => trim( $item->Message ),
                        'date' => Carbon::createFromTimestamp( $item->InsDate )->toDateTimeString(),
                        'status' => 0,
                    ] );
                    $this->info( 'command done!' );
                }
            }
        } catch ( SoapFault $fault ) {
            echo "Error: {$fault->getMessage()}";
        }
    }
}
