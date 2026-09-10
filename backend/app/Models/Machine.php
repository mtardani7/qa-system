<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory, Auditable, SoftDeletes;
    protected $fillable = ['plant_id', 'line_id', 'code', 'name', 'section', 'machine_number', 'description', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function plant(): BelongsTo { return $this->belongsTo(Plant::class); }
    public function line(): BelongsTo { return $this->belongsTo(Line::class); }
}
