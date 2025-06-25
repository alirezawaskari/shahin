<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('logo');
            $table->text('footer_logo');
            $table->text('fav');
            $table->text('samandehi')->nullable(true);
            $table->text('enamad')->nullable(true);
            $table->text('extra_logo')->nullable(true);
            $table->text('copyright');
            $table->string('call')->nullable(true);
            $table->string('phone')->nullable(true);
            $table->string('email')->nullable(true);
            $table->text('description')->nullable(true);
            $table->text('keywords')->nullable(true);
            $table->text('telegram')->nullable(true);
            $table->text('instagram')->nullable(true);
            $table->text('linkedin')->nullable(true);
            $table->text('twitter')->nullable(true);
            $table->text('map')->nullable(true);
            $table->text('index_title');
            $table->text('index_description');
            $table->text('address')->nullable(true);
            $table->text('video')->nullable(true);
            $table->text('agency')->nullable(true);
            $table->text('rules')->nullable(true);
            $table->text('card_number')->nullable(true);

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
