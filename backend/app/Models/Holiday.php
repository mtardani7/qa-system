<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\SoftDeletes;
class Holiday extends \Illuminate\Database\Eloquent\Model { use HasFactory, SoftDeletes; protected $table='holidays'; protected $fillable=['company_id','plant_id','holiday_date','name','is_working_day']; protected function casts(): array{return ['holiday_date'=>'date','is_working_day'=>'boolean'];} public function company(){return $this->belongsTo(Company::class);} public function plant(){return $this->belongsTo(Plant::class);} }
