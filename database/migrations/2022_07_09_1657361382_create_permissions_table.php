<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {

		$table->integer('id',10)->unsigned();
		$table->string('name');
		$table->string('display_name')->nullable()->default('NULL');
		$table->string('description')->nullable()->default('NULL');
		$table->integer('cat_id',11)->nullable()->default('NULL');
        $table->foreign('cat_id')->references('id')->on('categories')->onDelete('cascade');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');

        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $table->integer('permission_id')->unsigned();
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            $table->primary(['service_id' , 'organization_id']);
        });

        Schema::create('permission_user', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('permission_id')->unsigned();
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            $table->primary(['service_id' , 'organization_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}
