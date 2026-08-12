<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\DailyReportStatus;

class DailyReport extends Model
{
    use HasFactory, Auditable;
    protected $fillable = ['plant_id', 'line_id', 'machine_id', 'shift_id', 'product_id', 'product_type', 'checker_id', 'checker_2_id', 'qa_checker_2_id', 'mm_number', 'po_number', 'output_box', 'qty_per_box', 'output_pcs', 'defect_id', 'quantity_defect', 'finding_range_box', 'finding_observation', 'category', 'qa_checker_id', 'production_date', 'remarks', 'result', 'status'];
    protected function casts(): array { return ['production_date' => 'date', 'output_box' => 'integer', 'qty_per_box' => 'integer', 'output_pcs' => 'integer', 'quantity_defect' => 'integer', 'status' => DailyReportStatus::class]; }
    public function plant(): BelongsTo { return $this->belongsTo(Plant::class); }
    public function line(): BelongsTo { return $this->belongsTo(Line::class); }
    public function machine(): BelongsTo { return $this->belongsTo(Machine::class); }
    public function shift(): BelongsTo { return $this->belongsTo(Shift::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function defects(): HasMany { return $this->hasMany(DailyReportDefect::class); }
    public function checker(): BelongsTo { return $this->belongsTo(QaChecker::class, 'checker_id'); }
    public function checker2(): BelongsTo { return $this->belongsTo(QaChecker::class, 'checker_2_id'); }
    public function defect(): BelongsTo { return $this->belongsTo(Defect::class); }
    public function qaChecker(): BelongsTo { return $this->belongsTo(User::class, 'qa_checker_id'); }
    public function qaChecker2(): BelongsTo { return $this->belongsTo(User::class, 'qa_checker_2_id'); }
    public function audits(): HasMany { return $this->hasMany(DailyReportAudit::class); }
}
