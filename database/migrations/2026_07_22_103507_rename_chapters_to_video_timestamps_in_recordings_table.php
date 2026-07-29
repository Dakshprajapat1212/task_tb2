<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recordings', function (Blueprint $table) {
            // Standard MariaDB syntax: CHANGE old_column new_column DATA_TYPE
            DB::statement('ALTER TABLE recordings CHANGE chapters video_timestamps TEXT NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recordings', function (Blueprint $table) {
            // Reverts it back safely if you roll back
            DB::statement('ALTER TABLE recordings CHANGE video_timestamps chapters TEXT NULL');
        });
    }
};
