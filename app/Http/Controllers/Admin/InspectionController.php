<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Model\ApplyInspection;
use App\Http\Model\InspectionGroup;
use App\Http\Service\ScheduleService;
use App\Http\Service\UserService;
use Exception;
use App\Http\Service\InspectionService;
use Illuminate\Http\Request;
use App\Http\Requests\ApplyInspectionRequest;
use App\Http\Model\ContractInspectionGroup;
use Illuminate\Http\Response;


class InspectionController extends Controller
{

    //
    /**
     * 修改组名
     *
     * @SWG\Post(
     *   path="/api/v1/inspection/edit_inspections_group_name",
     *   tags={"修改组名"},
     *   summary="修改组名",
     *   description="修改组名。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(response="default", description="操作成功"),
     *   @SWG\Parameter(
     *         name="inspection_group_id",
     *         in="formData",
     *         description="验货组id",
     *         required=true,
     *         type="integer",
     *    ),
     *   @SWG\Parameter(
     *         name="inspection_group_name",
     *         in="formData",
     *         description="验货组名称",
     *         required=true,
     *         type="string",
     *    ),
     * )
     */
//    public function edit_inspections_group_name(InspectionGroupRequest $request,InspectionGroup $inspectionGroup){
//
//        $id=$request->input('inspection_group_id');
//        $name=$request->input('inspection_group_name');
//        $res=$inspectionGroup->where('id',$id)->first();
//        if(!$res){
//            return [
//                'status'=>1,
//                'message'=>'数据不存在'
//            ];
//        }
//        $inspectionGroup->where('id',$id)->update(['name'=>$name]);
//
//        return [
//            'status'=>1,
//            'message'=>'修改成功'
//        ];
//
//    }

    //
    /**
     * 修改验货组数据
     *
     * @SWG\Post(
     *   path="/api/v1/inspection/store_inspections_group",
     *   tags={"修改验货组数据"},
     *   summary="修改验货组数据",
     *   description="修改验货组数据。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(response="default", description="操作成功"),
     *   @SWG\Parameter(
     *         name="inspection_group_id",
     *         in="formData",
     *         description="验货组id",
     *         required=true,
     *         type="integer",
     *    ),
     *   @SWG\Parameter(name="contract_ids", required=true, in="body",type="array",
     *     @SWG\Schema(
     *     type="array",
     *     @SWG\Items(
     *     required={"contract_ids"},
     *     )
     *     ),
     *     description="合同id"
     *   ),
     * )
     */

//    public function store_inspections_group(Request $request,ApplyInspection $applyInspection){
//        $inspection_group_id=$request->input('inspection_group_id');
//        $contract_ids=$request->input('contract_ids');
//
//        if(!$inspection_group_id||!$contract_ids||!is_array($contract_ids)){
//            return [
//                'status'=>0,
//                'message'=>'参数错误'
//            ];
//        }
//
//        //获得该组下面所有合同id
//        $idsRes=$applyInspection->where('inspection_group_id',$inspection_group_id)->select('contract_id')->paginate(100);
//
//        $ids=array();
//        foreach ($idsRes as $id) {
//            $ids[]=$id['contract_id'];
//        }
//        $del_arr=array_diff($ids,$contract_ids);
//        $add_arr=array_diff($contract_ids,$ids);
//        $applyInspection->whereIn('contract_id',$del_arr)->where('inspection_group_id',$inspection_group_id)->update(['inspection_group_id'=>0]);
//        $applyInspection->whereIn('contract_id',$add_arr)->where('inspection_group_id',0)->update(['inspection_group_id'=>$inspection_group_id]);
//
//        return [
//            'status'=>1,
//            'message'=>'修改成功'
//        ];
//    }



    //
    /**
     * 修改组功能展示界面
     *
     * @SWG\Get(
     *   path="/api/v1/inspection/edit_inspections_group",
     *   tags={"修改组功能展示界面"},
     *   summary="修改组功能展示界面",
     *   description="修改组功能展示界面。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(response="default", description="操作成功"),
     *   @SWG\Parameter(
     *         name="inspection_group_id",
     *         in="query",
     *         description="验货组id",
     *         required=true,
     *         type="integer",
     *    )
     * )
     */
//    public function edit_inspections_group(Request $request,Response $response,InspectionGroup $inspectionGroup){
//        $inspection_group_id=$request->input('inspection_group_id');
//
//        $scheduleService=new ScheduleService($request,$response);
//        $scheduleService->status=1;
//        $where[]=array('inspection_group_id','=',0);
//
//
//        $whereParam=function ($query) use($where,$inspection_group_id){
//            $query->where($where)
//                ->orWhere('inspection_group_id',$inspection_group_id);
//        };
//
//        $inspections_group_list=$scheduleService->apply_list($whereParam);
//
//
//
//        //获得组名
//        $inspection_group_name=$inspectionGroup->where('id',$inspection_group_id)->value('name');
//        $res['data']['group_data']=$inspections_group_list['data'];
//        $res['data']['inspection_group_name']=$inspection_group_name;
//        $res['data']['inspection_group_id']=$inspection_group_id;
//        return $res;
//    }





    //  distribute_groups
    /**
     * 提交分组数据
     *
     * @SWG\Post(
     *   path="/api/v1/inspection/distribute_inspections",
     *   tags={"分组"},
     *   summary="分组",
     *   description="分组。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(
     *     response="default",
     *     description="操作失败",
     *     @SWG\Schema(
     *          @SWG\Property(
     *               property="status",
     *               type="integer"
     *          ),
     *          @SWG\Property(
     *               property="message",
     *               type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Parameter(
     *         name="inspection_group_name",
     *         in="formData",
     *         description="验货组名",
     *         required=true,
     *         type="integer",
     *    ),
     *   @SWG\Parameter(name="contents", required=true, in="body",type="array",
     *     @SWG\Schema(
     *     type="array",
     *     @SWG\Items(
     *     required={"contract_ids"},
     *     )
     *     ),
     *     description="合同id"
     *   ),
     * )
     */
    public function distribute_groups(ApplyInspectionRequest $request, InspectionGroup $inspectionGroup,ApplyInspection $applyInspection){
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


            $params['inspection_group_id']=$inspection_group_id;


            $applyInspection->where('id',$contentValue)->update($params);

        }

        return [
            'status'=>1,
            'message'=>'分组成功'
        ];


    }

    //
    /**
     * 验货批次列表
     *
     * @SWG\Get(
     *   path="/api/v1/inspection/inspections_group_list",
     *   tags={"验货批次列表"},
     *   summary="验货批次列表",
     *   description="验货批次列表。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(
     *     response="default",
     *     description="操作失败",
     *     @SWG\Schema(
     *          @SWG\Property(
     *               property="status",
     *               type="integer",
     *          ),
     *          @SWG\Property(
     *               property="message",
     *               type="string",
     *          )
     *      )
     *   ),
     *   @SWG\Parameter(
     *         name="order_by",
     *         in="formData",
     *         description="排序",
     *         type="string",
     *    ),
     * )
     */
    public function inspections_group_list(Request $request,Response $response){

        $scheduleService=new ScheduleService($request,$response);
        $where[]=array('apply_inspections.inspection_group_id','=',0);

        $params['status']=1;
        $params['where']=$where;
        $order_by=$request->input('order_by');
        if(isset($order_by)){
            $params['order_by']=$order_by;
        }

        $inspections_group_list=$scheduleService->apply_list_by_address($params);

        return $inspections_group_list;

    }

    //
    /**
     * 待分配验货列表
     *
     * @SWG\Get(
     *   path="/api/v1/inspection/inspections_group",
     *   tags={"获得分配验货数据"},
     *   summary="获得分配验货数据",
     *   description="获得分配验货数据。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(
     *     response="default",
     *     description="操作失败",
     *     @SWG\Schema(
     *          @SWG\Property(
     *               property="status",
     *               type="integer"
     *          ),
     *          @SWG\Property(
     *               property="message",
     *               type="string"
     *          )
     *      )
     *   ),
     * )
     */
    public function inspections_group(Request $request,InspectionService $inspectionService){
        $where['status']=1;
        $order_by=$request->input('order_by');
        $where['order_by']=$order_by;
        return $inspectionService->inspection_groups_list($where);

    }










    //
    /**
     * 验货用户列表
     *
     * @SWG\Get(
     *   path="/api/v1/inspection/select_group_useranddate_list",
     *   tags={"验货用户列表"},
     *   summary="验货用户列表",
     *   description="验货用户列表。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(response="default", description="操作成功"),
     * )
     */
    public function select_group_useranddate_list(UserService $userService){

        //$name='质检部';
        $data=$userService->get_user_by_role('质检部');
        return [
            'status'=>1,
            'message'=>'选择成功',
            'data'=>$data
        ];
        return $data;
    }


     //
    /**
     * 显示已分配验货的列表
     *
     * @SWG\Get(
     *   path="/api/v1/inspection/select_distributed_list",
     *   tags={"显示已分配验货的列表"},
     *   summary="显示已分配验货的列表",
     *   description="显示已分配验货的列表。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(response="default", description="操作成功"),
     * )
     */
    public function select_distributed_list(Request $request,InspectionService $inspectionService){
        $where['status']=2;
        $order_by=$request->input('order_by');
        $where['order_by']=$order_by;
        return $inspectionService->inspection_groups_list($where);
        //return $inspectionService->select_distributed_list();
    }


    //
    /**
     * 撤销申请验货
     *
     * @SWG\Get(
     *   path="/api/v1/inspection/reset_apply_inspection",
     *   tags={"撤销申请验货"},
     *   summary="撤销申请验货",
     *   description="撤销申请验货。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(
     *     response="default",
     *     description="操作失败",
     *     @SWG\Schema(
     *          @SWG\Property(
     *               property="status",
     *               type="integer"
     *          ),
     *          @SWG\Property(
     *               property="message",
     *               type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Parameter(
     *         name="id",
     *         in="query",
     *         description="申请验货id",
     *         required=true,
     *         type="integer",
     *    ),
     * )
     */
    public function reset_apply_inspection(Request $request,Response $response,ApplyInspection $applyInspection){


        $id=$request->input('id');
        $apply_inspection_data=$applyInspection->where('id',$id)->whereIn('status',array(0,1,2))->where('is_reset',0)->update(array('is_reset'=>1));
        if($apply_inspection_data){
            return ['status'=>1,'message'=>'撤销成功'];
        }
        if(!$apply_inspection_data){
            return ['status'=>0,'message'=>'撤销失败'];

        }

    }

    //
    /**
     * 撤销验货组回验货批次
     *
     * @SWG\Get(
     *   path="/api/v1/inspection/reset_inspection_group",
     *   tags={"撤销验货组回验货批次"},
     *   summary="撤销验货组回验货批次",
     *   description="撤销验货组回验货批次。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(
     *     response="default",
     *     description="操作失败",
     *     @SWG\Schema(
     *          @SWG\Property(
     *               property="status",
     *               type="integer"
     *          ),
     *          @SWG\Property(
     *               property="message",
     *               type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Parameter(
     *         name="inspection_group_id",
     *         in="query",
     *         description="验货组id",
     *         type="integer",
     *    ),
     *   @SWG\Parameter(
     *         name="contract_id",
     *         in="query",
     *         description="合同id",
     *         type="integer",
     *    ),
     * )
     */
    public function reset_inspection_group(Request $request,ApplyInspection $applyInspection){
        //获得组id
        $inspection_group_id=$request->input('inspection_group_id');
        $contract_id=$request->input('contract_id');

        if($inspection_group_id){
            $res=$applyInspection->where('inspection_group_id',$inspection_group_id)
                ->update(['status'=>1,'inspection_group_id'=>0]);
            InspectionGroup::where('id',$inspection_group_id)->delete();
        }

        if($contract_id){
            $res=$applyInspection->where('contract_id',$contract_id)
                ->update(['status'=>1,'inspection_group_id'=>0]);
        }
        if($res){

            return ['status'=>1,'message'=>'撤销成功'];
        }else{
            return ['status'=>0,'message'=>'撤销失败'];
        }
    }


    //  distribute_inspections
    /**
     * 分配验货
     *
     * @SWG\Post(
     *   path="/api/v1/inspection/select_group_user",
     *   tags={"分配验货"},
     *   summary="分配验货",
     *   description="分配验货。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(
     *     response="default",
     *     description="操作失败",
     *     @SWG\Schema(
     *          @SWG\Property(
     *               property="status",
     *               type="integer"
     *          ),
     *          @SWG\Property(
     *               property="message",
     *               type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Parameter(
     *         name="inspection_group_id",
     *         in="formData",
     *         description="验货组id",
     *         required=true,
     *         type="integer",
     *    ),
     *   @SWG\Parameter(name="user_id", required=true, in="body",type="array",
     *     @SWG\Schema(
     *     type="array",
     *     @SWG\Items(
     *     required={"user_id"},
     *     )
     *     ),
     *     description="验货人id"
     *   ),
     *   @SWG\Parameter(
     *         name="early_inspection_date",
     *         in="formData",
     *         description="最早验货日期",
     *         required=true,
     *         type="string",
     *    ),
     *   @SWG\Parameter(
     *         name="desc",
     *         in="formData",
     *         description="备注",
     *         type="string",
     *    ),
     * )
     */
    public function distribute_inspections(Request $request,InspectionGroup $inspectionGroup,ApplyInspection $applyInspection){
        $contract_id=$request->input('contract_id');
        $user_id=$request->input('user_id');
        $inspection_group_id=$request->input('inspection_group_id');
        //$probable_inspection_date=$request->input('probable_inspection_date');
        $early_inspection_date=$request->input('early_inspection_date');
        if(strtotime($early_inspection_date)<time()){
            return [
                'status'=>0,
                'message'=>'验货时间不能早于现在'
            ];
        }

        $desc=$request->input('desc');
        $id=$applyInspection->where('contract_id',$contract_id)->first();
        if(!$id){
            return [
                'status'=>0,
                'message'=>'数据不存在'
            ];
        }

        if(!is_array($user_id)){
            $user_id=(array)$user_id;
        }

        $inspectionGroup->where('id',$inspection_group_id)->update(array('user_id'=>serialize($user_id),'desc'=>$desc));

        $applyInspection->where('inspection_group_id',$inspection_group_id)->where('contract_id',$contract_id)->update(array('status'=>2,'early_inspection_date'=>$early_inspection_date));
        return [
            'status'=>1,
            'message'=>'选择成功'
        ];
    }

    //
    /**
     * 撤销已分配任务
     *
     * @SWG\Get(
     *   path="/api/v1/inspection/reset_distribute_inspections",
     *   tags={"撤销已分配任务"},
     *   summary="撤销已分配任务",
     *   description="撤销已分配任务。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(
     *     response="default",
     *     description="操作失败",
     *     @SWG\Schema(
     *          @SWG\Property(
     *               property="status",
     *               type="integer"
     *          ),
     *          @SWG\Property(
     *               property="message",
     *               type="string"
     *          )
     *      )
     *   ),
     *   @SWG\Parameter(
     *         name="inspection_group_id",
     *         in="query",
     *         description="验货组id",
     *         required=true,
     *         type="integer",
     *    ),
     * )
     */
    public function reset_distribute_inspections(Request $request,InspectionGroup $inspectionGroup,ApplyInspection $applyInspection){
        //
        $inspection_group_id=$request->input('inspection_group_id');
        $inspectionGroup->where('id',$inspection_group_id)->update(array('user_id'=>'','desc'=>''));
        $applyInspection->where('inspection_group_id',$inspection_group_id)->update(array('status'=>1,'early_inspection_date'=>''));
        return [
            'status'=>1,
            'message'=>'撤销成功'
        ];
    }





}

