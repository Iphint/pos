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
        // Remove product_image from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('product_image');
        });

        // Remove photo from customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('photo');
        });

        // Remove photo from suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back product_image to products table
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_image')->nullable();
        });

        // Add back photo to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->string('photo')->nullable();
        });

        // Add back photo to suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('photo')->nullable();
        });
    }
};
