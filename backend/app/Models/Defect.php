<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Defect extends Model
{
    use HasFactory, Auditable, SoftDeletes;
    protected $fillable = ['code', 'name', 'category', 'description', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
}
