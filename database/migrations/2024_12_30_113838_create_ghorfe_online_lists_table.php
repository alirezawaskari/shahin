<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ghorfe_online_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('title');
            $table->string('en_title');
            $table->string('slug');
            $table->string('type_work');//service || product || all
            $table->string('type_sales')->default('all');//online || person || all
            $table->string('call');
            $table->text('address');
            $table->text('map_iframe_link');
            $table->text('google_map_link');
            $table->text('waze_map_link');
            $table->text('copyright');
            $table->text('index_title');
            $table->text('index_description');
            $table->text('footer_tag');
            $table->text('sms_code');
            $table->string('postal_code')->length(10);
            $table->timestamp('contract_start_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('contract_expiration_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('instagram')->nullable(true);
            $table->text('telegram')->nullable(true);
            $table->text('aparat')->nullable(true);
            $table->text('enamad')->nullable(true);
            $table->text('samandehi')->nullable(true);
            $table->string('email')->nullable(true);
            $table->text('description')->nullable(true);
            $table->text('keywords')->nullable(true);
            $table->integer('status');
            $table->string('domain_active')->length(100);
            $table->decimal('lat', 10, 7);
            $table->decimal('lon', 10, 7);
            $table->string('card_number')->nullable(true);
            $table->string('shaba_number')->nullable(true);
            $table->string('onwer_name')->nullable(true);
            $table->foreignUuid('fav_id')->nullable()
                ->constrained('media_libraries')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignUuid('logo_id')->nullable()
                ->constrained('media_libraries')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignUuid('footer_logo_id')->nullable()
                ->constrained('media_libraries')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignUuid('large_vertical_image_id')->nullable()
                ->constrained('media_libraries')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignUuid('large_horizontal_image_id')->nullable()
                ->constrained('media_libraries')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignUuid('left_small_horizontal_image_id')->nullable()
                ->constrained('media_libraries')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignUuid('right_small_horizontal_image_id')->nullable()
                ->constrained('media_libraries')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignUuid('addvertisment_slider_id')->nullable()
                ->constrained('media_libraries')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignUuid('tax_certificate_image_id')->nullable()
                ->constrained('media_libraries')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignUuid('mohr_id')->nullable()
                ->constrained('media_libraries')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->softDeletes();
            
            $table->timestamps();
        });

        Schema::create('ghorfe_online_list_service', function (Blueprint $table) {
            $table->integer('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

            $table->integer('ghorfe_online_list_id')->unsigned();
            $table->foreign('ghorfe_online_list_id')->references('id')->on('ghorfe_online_lists')->onDelete('cascade');

            $table->primary(['service_id' , 'ghorfe_online_list_id']);
        });

        Schema::create('category_ghorfe_online_list', function (Blueprint $table) {
            $table->integer('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            $table->integer('ghorfe_online_list_id')->unsigned();
            $table->foreign('ghorfe_online_list_id')->references('id')->on('ghorfe_online_lists')->onDelete('cascade');

            $table->primary(['category_id' , 'ghorfe_online_list_id']);
        });

        Schema::create('ghorfe_online_list_product', function (Blueprint $table) {
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->integer('ghorfe_online_list_id')->unsigned();
            $table->foreign('ghorfe_online_list_id')->references('id')->on('ghorfe_online_lists')->onDelete('cascade');

            $table->primary(['product_id' , 'ghorfe_online_list_id']);
        });

        Schema::create('factor_ghorfe_online_list', function (Blueprint $table) {
            $table->integer('factor_id')->unsigned();
            $table->foreign('factor_id')->references('id')->on('factors')->onDelete('cascade');

            $table->integer('ghorfe_online_list_id')->unsigned();
            $table->foreign('ghorfe_online_list_id')->references('id')->on('ghorfe_online_lists')->onDelete('cascade');

            $table->primary(['factor_id' , 'ghorfe_online_list_id']);
        });

        Schema::create('callcenter_ghorfe_online_list', function (Blueprint $table) {
            $table->integer('callcenter_id')->unsigned();
            $table->foreign('callcenter_id')->references('id')->on('callcenters')->onDelete('cascade');

            $table->integer('ghorfe_online_list_id')->unsigned();
            $table->foreign('ghorfe_online_list_id')->references('id')->on('ghorfe_online_lists')->onDelete('cascade');

            $table->primary(['callcenter_id' , 'ghorfe_online_list_id']);
        });

        Schema::create('ghorfe_online_list_user', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('ghorfe_online_list_id')->unsigned();
            $table->foreign('ghorfe_online_list_id')->references('id')->on('ghorfe_online_lists')->onDelete('cascade');

            $table->primary(['user_id' , 'ghorfe_online_list_id']);
        });

        Schema::create('financial_plans_type_ghorfe_online_list', function (Blueprint $table) {
            $table->integer('financial_plans_type_id')->unsigned();
            $table->foreign('financial_plans_type_id')->references('id')->on('financial_plans_types')->onDelete('cascade');

            $table->integer('ghorfe_online_list_id')->unsigned();
            $table->foreign('ghorfe_online_list_id')->references('id')->on('ghorfe_online_lists')->onDelete('cascade');

            $table->primary(['financial_plans_type_id' , 'ghorfe_online_list_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ghorfe_online_lists');
    }
};
