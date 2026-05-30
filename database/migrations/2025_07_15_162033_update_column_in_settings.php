<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('key')->nullable();
            $table->text('value')->nullable();
            $table->nullableMorphs('settingable');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // حذف morphs
            $table->dropMorphs('settingable');

            // حذف ستون‌ها
            $table->dropColumn(['key', 'value']);
        });
    }
};
