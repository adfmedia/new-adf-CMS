<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Forms\Models\FormSubmission;

class GdprFormLogClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature    = 'gdpr:form_log_clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description  = 'Remove old form logs based on ENV controlled storage period';

    /**
     * Execute the command
     * 
     * @return void
     */
    public function handle(): void
    {
        $retention_date = date("Y-m-d", strtotime("-" . config('app.form_log_retention_period')));
        $SubmissionsToClear = FormSubmission::whereDate('created_at', '<=', $retention_date)->get();
        foreach ($SubmissionsToClear as $Submission) {
            foreach ($Submission->fields as $StoredFieldInfo) {
                $StoredFieldInfo->delete();
            }
            foreach ($Submission->tracking_data as $StoredTrackInfo) {
                $StoredTrackInfo->delete();
            }

            $Submission->delete();
        }
    }
}