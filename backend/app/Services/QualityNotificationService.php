<?php
namespace App\Services;
use App\Models\DailyReport; use App\Models\SystemSetting; use App\Models\User; use App\Notifications\QualityThresholdExceeded;
class QualityNotificationService { public function evaluate(DailyReport $report):void{$output=(int)$report->output_pcs;$defects=(int)$report->quantity_defect;if($output<1)return;$target=(float)(SystemSetting::query()->where('key','defect_target')->where(fn($q)=>$q->whereNull('company_id')->orWhere('company_id',$report->plant?->company_id))->value('value')??env('QMS_TARGET_DEFECT_RATE',2));$rate=($defects/$output)*100;if($rate>$target){User::query()->whereHas('roles',fn($q)=>$q->whereIn('name',['QA Supervisor','QA Manager']))->get()->each(fn(User $user)=>$user->notify(new QualityThresholdExceeded($report,round($rate,2))));}} }
