<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\SoftDeletes;
class Company extends \Illuminate\Database\Eloquent\Model { use HasFactory, SoftDeletes; protected $fillable=['code','name','address','logo_path','timezone','is_active']; protected function casts(): array { return ['is_active'=>'boolean']; } public function plants(){return $this->hasMany(Plant::class);} public function departments(){return $this->hasMany(Department::class);} public function settings(){return $this->hasMany(SystemSetting::class);} }
