<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('message_id');
            $table->string('gmail_message_id');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('template_id')->nullable();
            $table->string('gmail_draft_id')->nullable()->index();
            $table->string('customer_email')->nullable();
            $table->string('subject');
            $table->longText('body');
            $table->string('attachment')->nullable();
            $table->enum('status', ['draft', 'sent', 'failed'])->default('draft')->index();
            $table->timestamps();

            $table->index(['message_id', 'gmail_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
