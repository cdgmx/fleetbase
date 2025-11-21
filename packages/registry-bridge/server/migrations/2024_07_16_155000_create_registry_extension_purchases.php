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
        Schema::create('registry_extension_purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->index(); 
            $table->uuid('company_uuid'); 
            $table->uuid('extension_uuid'); 
            $table->string('stripe_checkout_session_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->boolean('is_subcription')->default(0); 
            $table->integer('locked_price')->nullable();
            $table->string('subscription_billing_period')->nullable();
            $table->string('subscription_model')->nullable(); // flat_rate, usage, tiered
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_uuid')->references('uuid')->on('companies')->onDelete('cascade');
            $table->foreign('extension_uuid')->references('uuid')->on('registry_extensions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registry_extension_purchases');
    }
};
