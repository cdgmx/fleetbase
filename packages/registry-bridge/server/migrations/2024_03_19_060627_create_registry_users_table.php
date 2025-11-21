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
        Schema::create('registry_users', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->index(); 
            $table->uuid('company_uuid'); 
            $table->uuid('user_uuid'); 
            $table->string('public_id')->nullable()->index();
            $table->string('token'); 
            $table->string('scope')->nullable();
            $table->timestamp('expires_at')->nullable(); 
            $table->timestamp('last_used_at')->nullable(); 
            $table->string('name')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('revoked')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_uuid')->references('uuid')->on('companies')->onDelete('cascade');
            $table->foreign('user_uuid')->references('uuid')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registry_users');
    }
};
