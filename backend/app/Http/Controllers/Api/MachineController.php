<?php
namespace App\Http\Controllers\Api;
use App\Exports\MachineImportTemplateExport;
use App\Imports\MachineImport;
use App\Http\Requests\MachineRequest;
use App\Http\Requests\MasterIndexRequest;
use App\Http\Resources\MachineResource;
use App\Models\Machine;
use App\Services\MachineService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
class MachineController extends BaseApiController
{
    public function __construct(private readonly MachineService $service) {}
    public function import(Request $request) { $this->authorize('create', Machine::class); $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls', 'max:51200']]); Excel::import(new MachineImport(), $request->file('file')); return $this->respondSuccess(null, 'Machines imported successfully.'); }
    public function template(Request $request) { $this->authorize('create', Machine::class); return Excel::download(new MachineImportTemplateExport(), 'machine-import-template.xlsx'); }
    public function index(MasterIndexRequest $request) { $this->authorize('viewAny', Machine::class); return $this->respondWithResource(MachineResource::collection($this->service->paginate($request->validated())), 'Machines retrieved successfully.'); }
    public function store(MachineRequest $request) { return $this->respondSuccess(new MachineResource($this->service->create($request->validated())), 'Machine created successfully.', 201); }
    public function show(Machine $machine) { $this->authorize('view', $machine); return $this->respondSuccess(new MachineResource($machine->load('plant')), 'Machine retrieved successfully.'); }
    public function update(MachineRequest $request, Machine $machine) { return $this->respondSuccess(new MachineResource($this->service->update($machine, $request->validated())), 'Machine updated successfully.'); }
    public function destroy(Machine $machine) { $this->authorize('delete', $machine); $this->service->delete($machine); return $this->respondSuccess(null, 'Machine deleted successfully.'); }
}
