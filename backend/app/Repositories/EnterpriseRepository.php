<?php
namespace App\Repositories;
use Illuminate\Database\Eloquent\Builder; use Illuminate\Database\Eloquent\Model;
class EnterpriseRepository extends BaseRepository { public function __construct(Model $model){parent::__construct($model);} protected function applyFilters(Builder $query,array $filters):Builder{return $query->when($filters['search']??null,fn(Builder $q,string $v)=>$q->where(fn(Builder $s)=>$s->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($v).'%'])))->when(array_key_exists('is_active',$filters),fn(Builder $q)=>$q->where('is_active',(bool)$filters['is_active']));} protected function allowedSorts():array{return [$this->model->getKeyName(),'name','code','created_at'];} }
