<?php
namespace App\Models;
class SystemSetting extends \Illuminate\Database\Eloquent\Model { protected $fillable=['company_id','key','value','type']; public function company(){return $this->belongsTo(Company::class);} public function typedValue(): mixed { return match($this->type){'boolean'=>(bool) filter_var($this->value,FILTER_VALIDATE_BOOLEAN),'integer'=>(int)$this->value,'float'=>(float)$this->value,'json'=>json_decode($this->value,true),'string'=>(string)$this->value,default=>$this->value}; } }
