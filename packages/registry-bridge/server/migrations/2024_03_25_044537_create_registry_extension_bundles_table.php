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
        Schema::create('registry_extension_bundles', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->index(); 
            $table->uuid('public_id')->index(); 
            $table->uuid('bundle_id')->index(); 
            $table->uuid('company_uuid'); 
            $table->uuid('created_by_uuid')->nullable();
            $table->uuid('extension_uuid')->nullable();
            $table->uuid('bundle_uuid')->nullable();
            $table->string('bundle_number')->nullable();
            $table->string('version')->nullable();
            $table->string('status')->default('pending'); // pending -> in_review -> approved
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_uuid')->references('uuid')->on('companies')->onDelete('cascade');
            $table->foreign('created_by_uuid')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('extension_uuid')->references('uuid')->on('registry_extensions')->onDelete('cascade');
            $table->foreign('bundle_uuid')->references('uuid')->on('files')->onDelete('cascade');
        });

        Schema::table('registry_extensions', function (Blueprint $table) {
            $table->foreign('current_bundle_uuid')->references('uuid')->on('registry_extension_bundles')->onDelete('cascade');
            $table->foreign('next_bundle_uuid')->references('uuid')->on('registry_extension_bundles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registry_extensions', function (Blueprint $table) {
            $table->dropForeign(['current_bundle_uuid']);
            $table->dropForeign(['next_bundle_uuid']);
        });

        Schema::dropIfExists('registry_extension_bundles');
    }
};
