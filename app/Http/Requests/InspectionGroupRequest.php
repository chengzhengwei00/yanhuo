<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InspectionGroupRequest extends FormRequest
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
            'inspection_group_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'inspection_group_name.required'     => '名字不能为空',
            'inspection_group_id.required'     => 'id不能为空',
        ];
    }
}
