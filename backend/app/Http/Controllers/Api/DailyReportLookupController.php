<?php

namespace App\Http\Controllers\Api;

use App\Models\Defect;
use App\Models\Machine;
use App\Models\Line;
use App\Models\Plant;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use App\Models\QaChecker;
use Illuminate\Http\Request;

class DailyReportLookupController extends BaseApiController
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Models\DailyReport::class);
        $user = $request->user();
        $allowedPlantIds = $user?->hasRole('Super Admin') ? null : $user?->plants()->where('plants.is_active', true)->pluck('plants.id')->all();
        $qaCheckers = QaChecker::query()->where('is_active', true)->when($allowedPlantIds !== null, fn ($query) => $query->whereIn('plant_id', $allowedPlantIds))->orderBy('name')->get(['id', 'plant_id', 'employee_number', 'name', 'position']);
        return $this->respondSuccess([
            'plants' => Plant::query()->where('is_active', true)->when($allowedPlantIds !== null, fn ($query) => $query->whereIn('id', $allowedPlantIds))->orderBy('name')->get(['id', 'code', 'name']),
            'lines' => Line::query()->where('is_active', true)->when($allowedPlantIds !== null, fn ($query) => $query->whereIn('plant_id', $allowedPlantIds))->orderBy('name')->get(['id', 'plant_id', 'code', 'name']),
            'machines' => Machine::query()->where('is_active', true)->when($allowedPlantIds !== null, fn ($query) => $query->whereIn('plant_id', $allowedPlantIds))->orderBy('name')->get(['id', 'plant_id', 'line_id', 'code', 'name', 'machine_number']),
            'shifts' => Shift::query()->where('is_active', true)->orderBy('name')->get(['id', 'plant_id', 'code', 'name']),
            'products' => Product::query()->where('is_active', true)->when($allowedPlantIds !== null, fn ($query) => $query->where(fn ($inner) => $inner->whereIn('plant_id', $allowedPlantIds)->orWhereNull('plant_id')))->orderBy('mm_number')->get(['id', 'plant_id', 'code', 'name', 'mm_number', 'description', 'qty_per_box']),
            'defects' => Defect::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'description', 'category']),
            'qa_checkers' => $qaCheckers,
        ], 'Daily report lookups retrieved successfully.');
    }

    public function products(Request $request)
    {
        $this->authorize('viewAny', \App\Models\DailyReport::class);
        $search = trim((string) $request->query('search', ''));
        $operator = Product::query()->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $plantId = $request->integer('plant_id');
        $products = Product::query()
            ->where('is_active', true)
            ->when($plantId, fn ($query) => $query->where(fn ($inner) => $inner->where('plant_id', $plantId)->orWhereNull('plant_id')))
            ->when($search !== '', function ($query) use ($operator, $search): void {
                $term = "%{$search}%";
                $query->where(function ($inner) use ($operator, $term): void {
                    $inner->where('mm_number', $operator, $term)
                        ->orWhere('name', $operator, $term)
                        ->orWhere('description', $operator, $term);
                });
            })
            ->orderBy('mm_number')
            ->limit(50)
            ->get(['id', 'plant_id', 'code', 'name', 'mm_number', 'description', 'qty_per_box']);
        return $this->respondSuccess($products, 'Product lookup retrieved successfully.');
    }
}
