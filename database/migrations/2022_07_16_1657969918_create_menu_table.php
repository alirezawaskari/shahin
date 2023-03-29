<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    public function up()
    {
        Schema::create('menu', function (Blueprint $table) {

		$table->integer('id');
        $table->unsignedBigInteger('menuable_id');
        $table->string('menuable_type');//Service Models || ServiceItem Models || Category Models
        $table->index(['menuable_id', 'menuable_type']);
		$table->integer('parent_id')->nullable()->default(NULL);//Service Models || ServiceItem Models || Category Models
		$table->integer('position')->nullable()->default(NULL);
		$table->string('title',199)->nullable()->default(NULL);
		$table->integer('depth')->nullable()->default(NULL);
		$table->integer('duplicate')->default('0');
		$table->tinyInteger('duplicate_subset')->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu');
    }
}
