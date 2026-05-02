<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('dashed__vacancies')) {
            \Dashed\DashedCore\Classes\Migrations::createTableForVisitableModel('dashed__vacancies');

            Schema::table('dashed__vacancies', function (Blueprint $table) {
                // Translatable extras
                $table->json('excerpt')->nullable();
                $table->json('description')->nullable();
                $table->json('responsibilities')->nullable();
                $table->json('requirements')->nullable();
                $table->json('benefits')->nullable();
                $table->json('qualifications')->nullable();
                $table->json('skills')->nullable();
                $table->json('education_requirements')->nullable();
                $table->json('experience_requirements')->nullable();
                $table->json('industry')->nullable();

                // Visuals
                $table->json('image')->nullable();

                // Optional category
                $table->foreignId('category_id')->nullable()->constrained('dashed__vacancy_categories')->nullOnDelete();

                // Optional form attachment (from dashed-forms)
                $table->unsignedBigInteger('form_id')->nullable()->index();

                // Hiring organisation overrides
                $table->string('hiring_organization_name')->nullable();
                $table->string('hiring_organization_url')->nullable();
                $table->string('hiring_organization_logo')->nullable();

                // Job location
                $table->string('job_location_type')->nullable();
                $table->string('street_address')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('city')->nullable();
                $table->string('region')->nullable();
                $table->string('country')->nullable();
                $table->string('applicant_location_requirements')->nullable();

                // Employment details
                $table->json('employment_types')->nullable();
                $table->string('experience_level')->nullable();
                $table->integer('work_hours_min')->nullable();
                $table->integer('work_hours_max')->nullable();

                // Salary
                $table->decimal('salary_min', 12, 2)->nullable();
                $table->decimal('salary_max', 12, 2)->nullable();
                $table->string('salary_currency', 8)->nullable();
                $table->string('salary_unit_text')->nullable();

                // Application
                $table->dateTime('application_deadline')->nullable();
                $table->dateTime('valid_through')->nullable();
                $table->boolean('direct_apply')->default(false);
                $table->string('application_url')->nullable();
                $table->string('application_email')->nullable();
                $table->string('identifier_value')->nullable();

                // SEO
                $table->json('meta_title')->nullable();
                $table->json('meta_description')->nullable();
                $table->json('meta_image')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dashed__vacancies');
    }
};
