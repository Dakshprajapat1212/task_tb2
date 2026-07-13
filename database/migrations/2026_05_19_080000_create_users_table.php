<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            $table->foreignId('role_id')
                  ->constrained('mas_roles')
                  ->onDelete('cascade');

            $table->string('name', 50);

            $table->string('email', 50)->unique();

        $table->string('password', 255);

            $table->char('phone_no', 10);

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};