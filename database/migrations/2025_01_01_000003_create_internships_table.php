<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internships', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('position');                     // internship title/role
            $table->text('description');                    // general description
            $table->string('capacity')->nullable();         // e.g. "3 Posisi"
            $table->string('duration')->nullable();         // e.g. "3 Bulan"
            $table->string('bidang')->nullable();           // e.g. "Software Engineering"
            $table->date('start_date')->nullable();         // start of internship period
            $table->json('job_description')->nullable();    // array of bullet strings
            $table->json('skills')->nullable();             // array - Keahlian Utama
            $table->json('requirements')->nullable();       // array - Persyaratan
            $table->string('minimum_education')->nullable();
            $table->string('sistem_kerja')->nullable();     // WFO / WFH / Hybrid
            $table->string('location')->nullable();
            $table->date('deadline');
            $table->boolean('is_active')->default(true);   // controls portal visibility
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internships');
    }
};
