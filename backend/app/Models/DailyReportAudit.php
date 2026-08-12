<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReportAudit extends Model
{
    use HasFactory;

    protected $fillable = ['daily_report_id', 'user_id', 'action', 'ip_address', 'old_value', 'new_value'];

    protected function casts(): array { return ['old_value' => 'array', 'new_value' => 'array']; }

    public function report(): BelongsTo { return $this->belongsTo(DailyReport::class, 'daily_report_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
