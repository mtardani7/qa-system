<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plant extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $fillable = ['company_id', 'code', 'name', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
