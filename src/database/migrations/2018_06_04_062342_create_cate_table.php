<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateCateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cate', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('parent_id')->unsigned()->nullable()->comment('上層分類ID');
            $table->dateTime('created_at')->comment('建立資料時間');
            $table->dateTime('updated_at')->comment('最後編輯資料時間');
            $table->string('name', 12)->comment('分類名稱');
            $table->text('select_photo')->comment('精選圖片');
            $table->bigInteger('order')->unsigned()->comment('排序');
            $table->string('use_sn', 12)->comment('使用套件SN(vendor)');;
            $table->tinyInteger('enable')->unsigned()->comment('0:停用 1:啟用');
            $table->dateTime('delete')->nullable()->comment('刪除時間');
            $table->integer('creat_admin_id')->unsigned()->nullable()->comment('建立資料管理員ID');
            $table->integer('update_admin_id')->unsigned()->nullable()->comment('最夠更新資料管理員ID');
        });
        Schema::create('cate_lang', function (Blueprint $table) {
            $table->bigInteger('cate_id')->unsigned()->comment('上層分類ID');
            $table->string('lang',3)->comment('上層分類ID');
            $table->dateTime('created_at')->comment('建立資料時間');
            $table->dateTime('updated_at')->comment('最後編輯資料時間');
            $table->string('name', 12)->comment('分類名稱');
            $table->text('select_photo')->comment('精選圖片');
            $table->integer('creat_admin_id')->unsigned()->nullable()->comment('建立資料管理員ID');
            $table->integer('update_admin_id')->unsigned()->nullable()->comment('最夠更新資料管理員ID');
            $table->primary(['cate_id', 'lang']);
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cate');
        Schema::dropIfExists('cate_lang');
    }
}