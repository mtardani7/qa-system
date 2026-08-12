<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QaChecker extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $fillable = ['employee_number', 'name', 'position', 'plant_id', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function plant(): BelongsTo { return $this->belongsTo(Plant::class); }
}
