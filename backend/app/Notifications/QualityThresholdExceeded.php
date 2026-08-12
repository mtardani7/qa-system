<?php
namespace App\Notifications;
use App\Models\DailyReport; use Illuminate\Bus\Queueable; use Illuminate\Contracts\Queue\ShouldQueue; use Illuminate\Notifications\Notification;
class QualityThresholdExceeded extends Notification implements ShouldQueue { use Queueable; public function __construct(public readonly DailyReport $report,public readonly float $defectRate){} public function via(object $notifiable):array{return ['database'];} public function toArray(object $notifiable):array{return ['type'=>'quality_threshold_exceeded','daily_report_id'=>$this->report->id,'defect_rate'=>$this->defectRate,'message'=>'Daily report defect rate exceeded the configured threshold.'];} }
