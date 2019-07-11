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
            'user_id'         => 'required|array|min:1',
            'inspection_group_id'         => 'required',
            'early_inspection_date'         => 'required|array|size:2',
            'early_inspection_date.date'         => 'required',
            'early_inspection_date.contract_id'         => 'required',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required'     => '请选择验货人',
            'user_id.array'     => '验货人格式错误',
            'inspection_group_id.required'     => '请选择验货数据',
            'early_inspection_date.required'     => '验货时间不能为空',
            'early_inspection_date.array'     => '验货时间格式错误',
            'early_inspection_date.size'     => '验货时间格式错误',
            'early_inspection_date.contract_id.required'     => '请选择验货数据',
            'early_inspection_date.date.required'     => '请选择验货时间',
        ];
    }
}
