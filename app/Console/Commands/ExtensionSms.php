<?php

namespace App\Console\Commands;

use App\Events\ExtensionDate;
use App\Http\Controllers\Admin\ContextController;
use App\Models\Context;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ExtensionSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:extension-sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $today = Carbon::today();
        $expire_at = $today->addDays(3);
        $expire_dates = Context::whereIn('key', array_merge(ContextController::DOMAINS_EXPIRE_DATE_KEYS, ContextController::OTHER_EXPIRE_DATE_KEYS))->whereDate('value', '<=', $expire_at->format('Y-m-d'))->get();
        $items_title=array();
        foreach ($expire_dates as $expire_date_item){
            if (Str::startsWith($expire_date_item->key, 'domain_expire_date')) {
                $items_title[]=explode('_',$expire_date_item->key)[3];
            } else  {
                $items_title[]=explode('_',$expire_date_item->key)[0];
            }

        }
        $all_items_need_extension=implode('|',$items_title);
        event(new ExtensionDate(config('app.extensions_remind_to') , $all_items_need_extension));
    }
}
