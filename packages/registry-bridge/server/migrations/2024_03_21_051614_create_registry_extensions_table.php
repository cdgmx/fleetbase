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
        Schema::create('registry_extensions', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->index(); 
            $table->uuid('company_uuid'); 
            $table->uuid('created_by_uuid')->nullable();
            $table->uuid('registry_user_uuid')->nullable();
            $table->uuid('current_bundle_uuid')->nullable();
            $table->uuid('next_bundle_uuid')->nullable();
            $table->uuid('icon_uuid')->nullable();
            $table->uuid('category_uuid')->nullable();
            $table->string('public_id')->nullable()->index();
            $table->string('stripe_product_id')->nullable();
            $table->string('name');
            $table->string('subtitle')->nullable();
            $table->boolean('payment_required')->default(0);
            $table->integer('price')->nullable();
            $table->integer('sale_price')->nullable();
            $table->boolean('on_sale')->default(0);
            $table->boolean('subscription_required')->default(0);
            $table->string('subscription_billing_period')->nullable();
            $table->string('subscription_model')->nullable(); // flat_rate, usage, tiered
            $table->integer('subscription_amount')->nullable();
            $table->json('subscription_tiers')->nullable(); // [{ first: 1, last: 5, per_unit: 100, flat_fee: 0 }]
            $table->string('currency')->default('USD');
            $table->string('slug');
            $table->string('version')->nullable();
            $table->string('fa_icon')->nullable();
            $table->mediumText('description')->nullable();
            $table->mediumText('promotional_text')->nullable();
            $table->string('website_url')->nullable();
            $table->string('repo_url')->nullable();
            $table->string('support_url')->nullable();
            $table->string('privacy_policy_url')->nullable();
            $table->string('tos_url')->nullable();
            $table->string('copyright')->nullable();
            $table->string('primary_language')->nullable();
            $table->json('tags')->nullable();
            $table->json('languages')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('core_extension')->default(0);
            $table->string('status')->default('pending'); // pending, in_review, rejected, published
            $table->timestamp('published_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_uuid')->references('uuid')->on('companies')->onDelete('cascade');
            $table->foreign('created_by_uuid')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('registry_user_uuid')->references('uuid')->on('registry_users')->onDelete('cascade');
            $table->foreign('icon_uuid')->references('uuid')->on('files')->onDelete('cascade');
            $table->foreign('category_uuid')->references('uuid')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registry_extensions');
    }
};
