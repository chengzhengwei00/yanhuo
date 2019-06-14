<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Model\ApplyInspection;
use App\Http\Model\InspectionGroup;
use App\Http\Requests\InspectionRequest;
use App\Http\Service\ScheduleService;
use Exception;
use App\Http\Service\InspectionService;
use Illuminate\Http\Request;
use App\Http\Requests\ApplyInspectionRequest;
use App\Http\Model\ContractInspectionGroup;
use Illuminate\Http\Response;
use App\Http\Requests\InspectionGroupRequest;

class InspectionController extends Controller
{


    //分配验货
    public function distribute_inspections(ApplyInspectionRequest $request, InspectionGroup $inspectionGroup,ApplyInspection $applyInspection){
        $contents=$request->input('contents');
        $inspection_group_name=$request->input('inspection_group_name');
        $res=$inspectionGroup->create(['name'=>$inspection_group_name]);
        if(!$res){
            return [
                'status'=>0,
                'message'=>'组名保存失败'
            ];
        }
        $inspection_group_id=$res->id;

        foreach ($contents as $k => $contentValue) {
            $params=array();
//            if(!$contentValue['contract_id']){
//                return [
//                    'status'=>0,
//                    'message'=>'合同id不能为空'
//                ];;
//            }


            $params['inspection_group_id']=$inspection_group_id;


            $applyInspection->where('id',$contentValue)->update($params);

        }

        return [
            'status'=>1,
            'message'=>'分配验货成功'
        ];


    }

    //获得需要分配验货的列表
    public function inspections_group_list(Request $request,Response $response){

        $scheduleService=new ScheduleService($request,$response);
        $scheduleService->status=1;
        $where[]=array('inspection_group_id','=',0);
        $inspections_group_list=$scheduleService->apply_list($where);
        return $inspections_group_list;

    }

    //获得各组以及其包含的数据
    public function inspections_group(InspectionService $inspectionService){
        return $inspectionService->inspection_groups_list();

    }

    //修改组名
    public function edit_inspections_group_name(InspectionGroupRequest $request,InspectionGroup $inspectionGroup){
        $id=$request->input('inspection_group_id');
        $name=$request->input('inspection_group_name');
        $res=$inspectionGroup->where('id',$id)->first();
        if(!$res){
            return [
                'status'=>1,
                'message'=>'数据不存在'
            ];
        }
        $inspectionGroup->where('id',$id)->update(['name'=>$name]);

        return [
            'status'=>1,
            'message'=>'修改成功'
        ];

    }

    //修改组数据
    public function store_inspections_group(Request $request,ApplyInspection $applyInspection){
        $inspection_group_id=$request->input('inspection_group_id');
        $contract_ids=$request->input('contract_ids');

        if(!$inspection_group_id||!$contract_ids||!is_array($contract_ids)){
            return [
                'status'=>0,
                'message'=>'参数错误'
            ];
        }

        //获得该组下面所有合同id
        $idsRes=$applyInspection->where('inspection_group_id',$inspection_group_id)->select('contract_id')->paginate(100);

        $ids=array();
        foreach ($idsRes as $id) {
            $ids[]=$id['contract_id'];
        }
        $del_arr=array_diff($ids,$contract_ids);
        $add_arr=array_diff($contract_ids,$ids);
        $applyInspection->whereIn('contract_id',$del_arr)->where('inspection_group_id',$inspection_group_id)->update(['inspection_group_id'=>0]);
        $applyInspection->whereIn('contract_id',$add_arr)->where('inspection_group_id',0)->update(['inspection_group_id'=>$inspection_group_id]);

        return [
            'status'=>1,
            'message'=>'修改成功'
        ];
    }



    //修改组功能展示界面
    public function edit_inspections_group(Request $request,Response $response,InspectionGroup $inspectionGroup){
        $inspection_group_id=$request->input('inspection_group_id');

        $scheduleService=new ScheduleService($request,$response);
        $scheduleService->status=1;
        $where[]=array('inspection_group_id','=',0);


        $whereParam=function ($query) use($where,$inspection_group_id){
            $query->where($where)
                ->orWhere('inspection_group_id',$inspection_group_id);
        };

        $inspections_group_list=$scheduleService->apply_list($whereParam);



        //获得组名
        $inspection_group_name=$inspectionGroup->where('id',$inspection_group_id)->value('name');
        $res['data']['group_data']=$inspections_group_list['data'];
        $res['data']['inspection_group_name']=$inspection_group_name;
        $res['data']['inspection_group_id']=$inspection_group_id;
        return $res;
    }



}
