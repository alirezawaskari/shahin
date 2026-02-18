// database/migrations/xxxx_xx_xx_create_themes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThemesTable extends Migration
{
    public function up()
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique();
            $table->string('template_name' , 100), //productSales || service
            $table->string('color_primary', 7);   // مانند #1a2b3c
            $table->string('color_secondary', 7); // مانند #080808
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('themes');
    }
}
