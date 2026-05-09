<?php

namespace App\Console\Commands;

use App\Classes\External\SendSms;
use App\Helpers\CalculationHelper;
use App\Models\BeforeForm;
use App\Models\BeforeFormItem;
use App\Models\FactorItem;
use Illuminate\Console\Command;
use App\Models\Callcenter;
use App\Models\ServiceTariff;

class RunQuery extends Command {
    /**
    * The name and signature of the console command.
    *
    * @var string
    */
    protected $signature = 'command:query';

    /**
    * The console command description.
    *
    * @var string
    */
    protected $description = 'run query for new admin panel';

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
        $service_tariffs = ServiceTariff::where( 'service_id', 211 )->get();
        foreach ($service_tariffs as $item) {
            $inputs[ 'selling_price' ] = $inputs[ 'selling_price' ] ? ( float ) str_replace( ',', '', $inputs[ 'selling_price' ] ) : 0;
        $inputs[ 'purchase_price' ] = ( float ) str_replace( ',', '', $inputs[ 'purchase_price' ] );

            $service_tariff = ServiceTariff::create( [
            'name' => $item->name,
            'purchase_price' => $item->purchase_price,
            'cash_price' => CalculationHelper::getServiceTariffSellingPrice( $item->selling_price, $item->purchase_price ),
            'price' => $item->price,
            'session_number' => $item->session_number,
            'treatment_duration' => $item->treatment_duration,
            'multi_stage' => $item->multi_stage,
            'tariff_id' => 25,
            'service_id' => 1,
        ] );
        $service_tariff->centers()->attach( 236 );

        }
        dd( 'success' );
        $before_forms = BeforeForm::where( 'beforeformable_id', 10 )->get();
        foreach ( $before_forms as $item ) {
            $before_form_items = $item->beforeformitems;
            BeforeForm::create( [
                'beforeformable_id' => 151,
                'beforeformable_type' => 'App\Models\Service',
                'type' => $item->type,
                'file' => null,
                'placeholder' => $item->placeholder,
                'name' => $item->name,
                'required' => $item->required,
                'value' => $item->value,
                'class' => 'form-control',
                'help' => $item->help,
                'level' => $item->level,
                'data_save_level' => $item->data_save_level,
                'data_display_level' => $item->data_display_level,
            ] );
            foreach ( $before_form_items as $before_form_item ) {
                BeforeFormItem::create( [
                    'title' => $before_form_item->title,
                    'value' => $before_form_item->value,
                    'beforeformitemable_id'=>$before_form_item->beforeformitemable_id,
                    'beforeformitemable_type'=>$before_form_item->beforeformitemable_type ]
                );
            }
        }
    }
}
