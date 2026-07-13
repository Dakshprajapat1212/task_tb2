<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY password VARCHAR(255) NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY phone_no CHAR(10) NULL");

        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_id');
        });

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY phone_no CHAR(10) NOT NULL");
    }
};
