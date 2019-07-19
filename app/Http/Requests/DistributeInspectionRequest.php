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
//    public function rules()
//    {
//        return [
//            //
//            'inspection_group_id'         => 'required',
//            'probable_inspection_date'         => 'required|array',
//            'probable_inspection_date.*.date_start'         => 'required_without:probable_inspection_date.*.date',
//            'probable_inspection_date.*.date_end'         => 'required_without:probable_inspection_date.*.date',
//            'probable_inspection_date.*.date'         => 'required_without_all:probable_inspection_date.*.date_start,probable_inspection_date.*.date_end',
//            'probable_inspection_date.*.apply_id'         => 'required',
//        ];
//    }
//
//    public function messages()
//    {
//        return [
//            'inspection_group_id.required'     => '请选择验货数据',
//            'probable_inspection_date.required'     => '验货时间不能为空',
//            'probable_inspection_date.array'     => '验货时间格式错误',
//            'probable_inspection_date.*.apply_id.required'     => '请选择验货数据',
//            'probable_inspection_date.*.date_start.required'     => '请选择验货时间',
//            'probable_inspection_date.*.date_end.required'     => '请选择验货时间',
//            'probable_inspection_date.*.date.required'     => '请选择验货时间',
//        ];
//    }

    public function rules()
    {
        return [
            //
            'inspection_group_id'         => 'required',
            'probable_inspection_date'         => 'required|array',
            'probable_inspection_date.*.date_start'         => 'required',
            'probable_inspection_date.*.apply_id'         => 'required',
        ];
    }

    public function messages()
    {
        return [
            'inspection_group_id.required'     => '请选择验货数据',
            'probable_inspection_date.required'     => '验货时间不能为空',
            'probable_inspection_date.array'     => '验货时间格式错误',
            'probable_inspection_date.*.apply_id.required'     => '请选择验货数据',
            'probable_inspection_date.*.date_start.required'     => '请选择验货时间',
        ];
    }
}
