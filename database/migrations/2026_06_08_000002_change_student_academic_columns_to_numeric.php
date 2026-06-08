<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedTinyInteger('semester')->nullable()->change();
            $table->unsignedSmallInteger('jumlah_sks')->nullable()->change();
            $table->decimal('ipk', 3, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('semester')->nullable()->change();
            $table->string('jumlah_sks')->nullable()->change();
            $table->string('ipk')->nullable()->change();
        });
    }
};
