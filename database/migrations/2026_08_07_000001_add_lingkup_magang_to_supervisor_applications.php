<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supervisor_applications', function (Blueprint $table) {
            $table->text('lingkup_magang')->nullable()->after('company_contact');
        });
    }

    public function down(): void
    {
        Schema::table('supervisor_applications', function (Blueprint $table) {
            $table->dropColumn('lingkup_magang');
        });
    }
};
