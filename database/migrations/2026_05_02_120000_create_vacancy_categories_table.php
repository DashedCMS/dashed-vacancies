<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('dashed__vacancy_categories')) {
            \Dashed\DashedCore\Classes\Migrations::createTableForVisitableModel('dashed__vacancy_categories');

            Schema::table('dashed__vacancy_categories', function (Blueprint $table) {
                $table->json('image')->nullable();
                $table->json('meta_title')->nullable();
                $table->json('meta_description')->nullable();
                $table->json('meta_image')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dashed__vacancy_categories');
    }
};
