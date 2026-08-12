<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest; use Illuminate\Validation\Rule;
class SystemSettingRequest extends FormRequest { public function authorize():bool{return $this->user()?->can('enterprise.manage')??false;} public function rules():array{return ['company_id'=>['nullable','exists:companies,id'],'key'=>['required','string','max:100',Rule::unique('system_settings','key')->where(fn($q)=>$q->where('company_id',$this->input('company_id')))->ignore($this->route('system_setting')?->id)],'value'=>['nullable','string'],'type'=>['required',Rule::in(['string','boolean','integer','float','json'])]];} }
