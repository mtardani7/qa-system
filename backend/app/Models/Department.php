<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\SoftDeletes;
class Department extends \Illuminate\Database\Eloquent\Model { use HasFactory, SoftDeletes; protected $fillable=['company_id','plant_id','code','name','is_active']; protected function casts(): array{return ['is_active'=>'boolean'];} public function company(){return $this->belongsTo(Company::class);} public function plant(){return $this->belongsTo(Plant::class);} }
