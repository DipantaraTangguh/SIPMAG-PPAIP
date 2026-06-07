<?php

namespace App\Observers;

use App\Models\Application;
use Illuminate\Support\Facades\Log;

class ApplicationObserver
{
    /**
     * Cancel all other "Applied" applications for the same student
     * when one application transitions to "Accepted".
     *
     * PRD: "When one application status changes to Accepted,
     *       all other Applied applications for that student are set to Canceled."
     */
    public function updated(Application $application): void
    {
        if (! $application->wasChanged('status')) {
            return;
        }

        if ($application->status !== 'Accepted') {
            return;
        }

        $canceledCount = Application::where('student_id', $application->student_id)
            ->where('id', '!=', $application->id)
            ->where('status', 'Applied')
            ->update(['status' => 'Canceled']);

        if ($canceledCount > 0) {
            Log::info('application.auto_canceled', [
                'accepted_application_id' => $application->id,
                'student_id'              => $application->student_id,
                'canceled_count'          => $canceledCount,
            ]);
        }
    }
}
