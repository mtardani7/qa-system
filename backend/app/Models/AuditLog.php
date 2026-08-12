<?php
namespace App\Models;
class AuditLog extends \Illuminate\Database\Eloquent\Model { protected $fillable=['user_id','action','ip_address','old_values','new_values']; protected $casts=['old_values'=>'array','new_values'=>'array']; public function user(){return $this->belongsTo(User::class);} public function auditable(){return $this->morphTo();} }
