<?php

namespace App\Console\Commands;

use App\Models\Factor;
use nusoap_client;

use Illuminate\Console\Command;

class VerifyUnverifiedTransaction extends Command {
    /**
    * The name and signature of the console command.
    *
    * @var string
    */
    protected $signature = 'verify:unverifiedTransaction';

    /**
    * The console command description.
    *
    * @var string
    */
    protected $description = 'get unverify transactions and verify them';

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
    */

    public function handle() {
        $client = new nusoap_client( config( 'services.zarinpal.url' ).'/pg/services/WebGate/wsdl', 'wsdl' );
        $client->soap_defencoding = 'UTF-8';
        $result = $client->call( 'GetUnverifiedTransactions', [
            [
                'MerchantID'     => config( 'services.zarinpal.id' ),
            ],
        ] );
        $authorities = json_decode($result['Authorities']);
        foreach($authorities as $authority)
        {
            $factor = Factor::where('factorable_type' , str_replace('"', '', $authority->Authority))->first();
            if($factor && $factor->status != 1)
            {
                $factor->update(['status' => 1 , 'refnumber' => 'verfiyUnverify']);
                $factor::savePayment($factor);
            }
        }
    }
}
