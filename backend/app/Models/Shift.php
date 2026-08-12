<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory, Auditable, SoftDeletes;
    protected $fillable = ['plant_id', 'code', 'name', 'start_time', 'end_time', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean', 'start_time' => 'datetime:H:i', 'end_time' => 'datetime:H:i']; }
    public function plant(): BelongsTo { return $this->belongsTo(Plant::class); }
}
