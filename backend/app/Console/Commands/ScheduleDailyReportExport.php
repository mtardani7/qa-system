<?php
namespace App\Console\Commands;
use App\Jobs\GenerateDailyReportExport; use App\Models\DailyReportExport; use Illuminate\Console\Command;
class ScheduleDailyReportExport extends Command { protected $signature='qms:export-yesterday'; protected $description='Queue the previous day daily report export.'; public function handle():int{$date=now()->subDay()->toDateString();$export=DailyReportExport::create(['status'=>'queued','filters'=>['date_from'=>$date,'date_to'=>$date],'file_path'=>'exports/daily-'.$date.'.xlsx']);GenerateDailyReportExport::dispatch($export->id,['date_from'=>$date,'date_to'=>$date],$export->file_path);$this->info("Export {$export->id} queued.");return self::SUCCESS;} }
