<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUploadToLaravel extends Migration
{
    /**
     * @var string
     */
    private $table;

    public function __construct()
    {
        $this->table = config('uploadtolaravel.table');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->integer('upload_status')->default(0);
            $table->timestamp('uploaded_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn($this->table, 'upload_status')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('upload_status');
            });
        }
        if (Schema::hasColumn($this->table, 'uploaded_at')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('uploaded_at');
            });
        }
    }
}
