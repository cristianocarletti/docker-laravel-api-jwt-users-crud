<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Nome: até 100 caracteres
            $table->string('lastname', 100)->default('default_lastname'); // Sobrenome: até 100 caracteres
            $table->string('phone', 20)->default('1234567890'); // Ex: +55 (11) 91234-5678 -> até 20 caracteres
            $table->string('email', 150)->unique(); // Email: 150 é um bom limite
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255)->nullable(); // Hash de senha (normalmente 60+, mas 255 cobre todos os casos)
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
