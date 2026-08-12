<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, Auditable, SoftDeletes;
    protected $fillable = ['code', 'name', 'mm_number', 'description', 'qty_per_box', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean', 'qty_per_box' => 'integer']; }
}
