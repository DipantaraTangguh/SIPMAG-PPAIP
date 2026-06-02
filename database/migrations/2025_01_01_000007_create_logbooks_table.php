<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('tanggal');                        // date of activity
            $table->text('kegiatan_harian');                // daily activities description
            $table->text('hasil');                          // outcomes / results
            $table->enum('status', [
                'PendingReview',
                'Approved',
                'Rejected',
            ])->default('PendingReview');
            $table->string('dpm_note')->nullable();         // DPM rejection note
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};
