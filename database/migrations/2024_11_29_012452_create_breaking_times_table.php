<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('breaking_times', function (Blueprint $table) {
            $table->id();
            $table->timestamp('create_at', 3);
            $table->string('breaking_time_id');
            $table->string('user_id');
            $table->string('username');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('reason');
            $table->date('start_date');
            $table->time('start_time');
            $table->date('end_date');
            $table->time('end_time');
            $table->string('manager_id');
            $table->string('manager_username');
            $table->string('manager_email');
            $table->string('hr_id');
            $table->string('hr_username');
            $table->string('hr_email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breaking_times');
    }
};
