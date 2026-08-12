<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReportDefect extends Model
{
    use HasFactory;

    protected $fillable = ['daily_report_id', 'defect_id', 'quantity', 'remarks'];

    protected function casts(): array { return ['quantity' => 'integer']; }

    public function dailyReport(): BelongsTo { return $this->belongsTo(DailyReport::class); }
    public function defect(): BelongsTo { return $this->belongsTo(Defect::class); }
}
