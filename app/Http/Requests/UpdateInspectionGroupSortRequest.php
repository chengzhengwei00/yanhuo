<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInspectionGroupSortRequest extends FormRequest
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
            'sort_arr'         => 'bail|required|array',
            'sort_arr.*'         => 'required|array|size:2',
            'sort_arr.*.id'         => 'required',
            'sort_arr.*.sort'         => 'required',
        ];
    }

    public function messages()
    {
        return [
            'sort_arr.required'     => '排序数据不能为空',
            'sort_arr.array'     => '参数格式不对',
            'sort_arr.*.array'     => '参数格式不对',
            'sort_arr.*.size'         => '参数格式不对2',
            'sort_arr.*.id.required'     => 'id不能为空',
            'sort_arr.*.sort.required'     => '排序字段不能为空',
        ];
    }
}
