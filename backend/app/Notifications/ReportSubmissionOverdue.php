<?php
namespace App\Notifications;
use App\Models\DailyReport; use Illuminate\Bus\Queueable; use Illuminate\Contracts\Queue\ShouldQueue; use Illuminate\Notifications\Notification;
class ReportSubmissionOverdue extends Notification implements ShouldQueue { use Queueable; public function __construct(public readonly DailyReport $report){} public function via(object $notifiable):array{return ['database'];} public function toArray(object $notifiable):array{return ['type'=>'report_submission_overdue','daily_report_id'=>$this->report->id,'message'=>'A daily QA report has not been submitted.'];} }
