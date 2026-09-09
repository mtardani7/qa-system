<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory, Auditable, SoftDeletes;
    protected $fillable = ['plant_id', 'code', 'name', 'mm_number', 'description', 'qty_per_box', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean', 'qty_per_box' => 'integer']; }
    public function plant(): BelongsTo { return $this->belongsTo(Plant::class); }
}
