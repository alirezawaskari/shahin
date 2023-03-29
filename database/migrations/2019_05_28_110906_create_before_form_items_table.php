<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBeforeFormItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('before_form_items', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('beforeformitemable', 'before_form_itemable_index');//BeforeForm Models || ContractForm Models
            $table->string('title');
            $table->string('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('before_form_items');
    }
}
