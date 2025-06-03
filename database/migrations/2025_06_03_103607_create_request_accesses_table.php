<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('request_accesses', function (Blueprint $table) {
            $table->id();
            $table->string('request_access_code');
            $table->string('request_access_type');
            $table->string('message');
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->foreignId('user_id')->constrained("users", "id")->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_accesses');
    }
};
