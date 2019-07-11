<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyInspectionRequest extends FormRequest
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
            'inspection_group_name'         => 'required',
            'contents'         => 'required|array|min:1'
        ];
    }

    public function messages()
    {
        return [
            'inspection_group_name.required'     => '分配组名不能为空',
            'contents.required'     => '请填写分配验货数据',
            'contents.array'     => '分配验货格式不对',
        ];
    }
}
