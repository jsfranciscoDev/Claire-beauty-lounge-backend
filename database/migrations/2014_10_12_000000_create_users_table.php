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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->smallInteger('role_id')->nullable();
            $table->smallInteger('staff_role')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->smallInteger('active')->default(1);
            $table->rememberToken();
            $table->timestamps();
            $table->unsignedBigInteger('contact')->nullable();
            $table->string('expertise')->nullable();
            $table->string('bio')->nullable();
            $table->softDeletes();
        });

        Schema::create('user_profile', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('path')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('user_profile');
    }
};
