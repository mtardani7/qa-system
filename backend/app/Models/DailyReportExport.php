<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReportExport extends Model
{
    use HasFactory;

    protected $fillable = ['created_by', 'status', 'filters', 'file_path', 'error_message'];

    protected function casts(): array { return ['filters' => 'array']; }

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
