<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DistributeInspectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'inspection_group_id'         => 'required',
            'early_inspection_date'         => 'required|array',
            'early_inspection_date.*.date'         => 'required',
            'early_inspection_date.*.contract_id'         => 'required',
        ];
    }

    public function messages()
    {
        return [
            'inspection_group_id.required'     => '请选择验货数据',
            'early_inspection_date.required'     => '验货时间不能为空',
            'early_inspection_date.array'     => '验货时间格式错误',
            'early_inspection_date.*.contract_id.required'     => '请选择验货数据',
            'early_inspection_date.*.date.required'     => '请选择验货时间',
        ];
    }
}
