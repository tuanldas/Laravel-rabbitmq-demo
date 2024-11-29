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
        Schema::create('accept_breaking_time', function (Blueprint $table) {
            $table->id();
            $table->string('channel_id');
            $table->string('team_id');
            $table->string('ticket_id');
            $table->timestamp('create_at', 3);
            $table->string('user_id');
            $table->string('username');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accept_breaking_time');
    }
};
