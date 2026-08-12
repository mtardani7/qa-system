<?php
namespace App\Models;
class UserActivity extends \Illuminate\Database\Eloquent\Model { public $timestamps=false; protected $fillable=['user_id','event','route','method','ip_address','user_agent','metadata','created_at']; protected $casts=['metadata'=>'array','created_at'=>'datetime']; public function user(){return $this->belongsTo(User::class);} }
