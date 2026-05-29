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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('sku')->unique();
            $table->string('unit');
            $table->decimal('per_unit_price', 12, 2);
            $table->text('product_details')->nullable();
            $table->text('notes')->nullable();
            $table->text('keyword_search')->nullable();
            $table->text('other_details')->nullable();
            $table->text('specification')->nullable();
            $table->text('quotation_documents')->nullable();
            $table->string('image')->nullable();
            $table->string('do')->nullable();
            $table->json('drow_image_1')->nullable();
            $table->json('drow_image_2')->nullable();
            $table->json('drow_image_3')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
