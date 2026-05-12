<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * internships — vacancy listings managed by PPAIP.
     * job_description stored as JSON array of bullet strings.
     */
    public function up(): void
    {
        Schema::create('internships', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('position');                     // internship title/role
            $table->text('description');                    // general description
            $table->text('vacancy_details')->nullable();    // e.g. "2 posisi, WFO, 3 bulan"
            $table->json('job_description')->nullable();    // array of bullet strings
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
