<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DailyReportImport extends Model { protected $fillable=['created_by','plant_id','line_id','file_name','file_path','status','processed_rows','imported_reports','failed_rows','errors','error_message']; protected $casts=['errors'=>'array']; public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');} public function plant():BelongsTo{return $this->belongsTo(Plant::class);} public function line():BelongsTo{return $this->belongsTo(Line::class);} }
