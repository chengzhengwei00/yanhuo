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
use App\Http\Model\Contract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\EditInspectionGroupRequest;
use App\Http\Requests\UpdateInspectionGroupSortRequest;
use App\Http\Requests\DistributeInspectionRequest;


class InspectionController extends Controller
{




    //  distribute_groups
    /**
     * 提交分组数据
     *
     * @SWG\Post(
     *   path="/api/v1/inspection/distribute_groups",
     *   tags={"验货分组"},
     *   summary="验货分组",
     *   description="验货分组。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Parameter(name="contents", required=true, in="body",
     *     @SWG\Schema(
     *        type="string",
     *         @SWG\Property(
     *            property="firstName",
     *            type="string"
     *          ),
     *         @SWG\Property(
     *            property="lastName",
     *            type="string"
     *          ),
     *         @SWG\Property(
     *            property="sss",
     *            type="array",
         *        @SWG\Items(
         *         @SWG\Property(
         *            property="firstName",
         *            type="string"
         *          ),
         *         @SWG\Property(
         *            property="lastName",
         *            type="string"
         *          ),
         *        )
     *          ),

     *     ),
     *     description="参数",
     *     default="{
                inspection_group_name:哈哈哈哈哈哈,
                contents:[1,2,3]
                }"
     *   ),
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
    public function distribute_groups(ApplyInspectionRequest $request, InspectionGroup $inspectionGroup,ApplyInspection $applyInspection){



        $contents=$request->input('contents');
        $inspection_group_name=$request->input('inspection_group_name');

        DB::beginTransaction();

        try{
            $res=$inspectionGroup->create(['name'=>$inspection_group_name]);

            if(!$res){
                DB::rollBack();
                return [
                    'status'=>0,
                    'message'=>'组名保存失败'
                ];
            }


            $inspection_group_id=$res->id;

            foreach ($contents as $k => $contentValue) {

                $applyInspectionObj=$applyInspection->where('id',$contentValue)->where('status',1)->where('is_reset',0)->where('inspection_group_id',0);
                $res=$applyInspectionObj->first();
                if(!$res){
                    DB::rollBack();
                    return [
                        'status'=>0,
                        'message'=>'数据不存在'
                    ];
                }

                $params=array();
                $params['inspection_group_id']=$inspection_group_id;
                $res=$applyInspectionObj->update($params);
                if(!$res){
                    DB::rollBack();

                    return [
                        'status'=>0,
                        'message'=>'分组失败'
                    ];
                }
            }

            DB::commit();

            return [
                'status'=>1,
                'message'=>'分组成功'
            ];

        }catch (Exception $exception){
            DB::rollBack();
            return [
                'status'=>0,
                'message'=>'分组失败'
            ];
        }











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
        if($order_by){
            $params['order_by']=$order_by;
        }else{
            $params['sort_by']=true;
        }

        $inspections_group_list=$scheduleService->apply_list_by_address($params);

        return $inspections_group_list;

    }

    //更新验货列表排序id）
    public function update_inspections_group_sort(UpdateInspectionGroupSortRequest $request,ApplyInspection $applyInspection){
        $sort_arr=$request->input('sort_arr');

        $res=$applyInspection->update_Batch($sort_arr);
        if($res===false){
            return ['status' => 0, 'message' => '更新失败'];

        }else{
            return ['status' => 1, 'message' => '更新成功'];
        }


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
        try{
            $apply_inspection_data=$applyInspection->where('id',$id)->whereIn('status',array(0,1,2))->where('is_reset',0)->update(array('is_reset'=>1));
            if($apply_inspection_data){
                return ['status'=>1,'message'=>'撤销成功'];
            }
            if(!$apply_inspection_data){
                return ['status'=>0,'message'=>'撤销失败'];

            }

        }catch (Exception $exception){
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
     *         name="apply_id",
     *         in="query",
     *         description="合同id",
     *         type="integer",
     *    ),
     * )
     */
    public function reset_inspection_group(Request $request,ApplyInspection $applyInspection){



        $inspection_group_id=$request->input('inspection_group_id');
        $apply_id=$request->input('apply_id');

        if($inspection_group_id){
            $res=$applyInspection->where('inspection_group_id',$inspection_group_id)
                ->update(['status'=>1,'inspection_group_id'=>0,'probable_inspection_date'=>'']);

            if($res===false){
                return ['status'=>0,'message'=>'撤销失败'];

            }else{
                InspectionGroup::where('id',$inspection_group_id)->delete();
                return ['status'=>1,'message'=>'撤销成功'];
            }
        }

        if($apply_id){
            $res=$applyInspection->where('id',$apply_id)
                ->update(['status'=>1,'inspection_group_id'=>0,'probable_inspection_date'=>'']);

            if($res===false){
                return ['status'=>0,'message'=>'撤销失败'];

            }else{
                return ['status'=>1,'message'=>'撤销成功'];
            }
        }

    }


    //  distribute_inspections
    /**
     * 分配验货
     *
    /**
     * @SWG\Definition(
     *     definition="Person",
     *     type="array",
     *     items="sdds,sds,dfsds",
     *     @SWG\Property(
     *         property="firstName",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="lastName",
     *         type="string"
     *     ),
     * )
     *
     * @SWG\Post(
     *   path="/api/v1/inspection/distribute_inspections",
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
     *   @SWG\Parameter(name="user_idss", required=true, in="formData",type="string",
     *      @SWG\Schema(ref="#/definitions/Person")
     *   ),


     *   @SWG\Parameter(name="user_id", required=true, in="formData",type="array",
     *      @SWG\items(
     *         type="string",
         *     @SWG\Property(
         *         property="firstName",
         *         type="string"
         *     ),
         *     @SWG\Property(
         *         property="lastName",
         *         type="string"
         *     ),
     *     ),

     *     description="验货人id"
     *   ),
     *   @SWG\Parameter(
     *         name="desc",
     *         in="formData",
     *         description="备注",
     *         type="string",
     *    ),
     * )
     */
    public function distribute_inspections(DistributeInspectionRequest $request,InspectionGroup $inspectionGroup,ApplyInspection $applyInspection){
        $user_id=$request->input('user_id');
        $inspection_group_id=$request->input('inspection_group_id');
        $probable_inspection_date=$request->input('probable_inspection_date');

        if(!$probable_inspection_date||!is_array($probable_inspection_date)){
            return [
                'status'=>0,
                'message'=>'验货时间不能为空'
            ];
        }
        $user_id_res=$inspectionGroup->where('id',$inspection_group_id)->select('user_id')->first();
        if(!$user_id_res){
            return [
                'status'=>0,
                'message'=>'数据不存在'
            ];
        }
        if(!isset($user_id_res->user_id)||!$user_id_res->user_id){

            if(!$user_id||count($user_id)<1){
                return [
                    'status'=>0,
                    'message'=>'请选择验货人'
                ];
            }

        }
        foreach ($probable_inspection_date as $i) {

            if(!isset($i['date'])){
                return [
                    'status'=>0,
                    'message'=>'请选择验货时间'
                ];
            }


            if(strtotime($i['date'])<strtotime(date('Y-m-d',time()))){
                return [
                    'status'=>0,
                    'message'=>'验货时间不能早于现在'
                ];
            }
            $apply_id=$i['apply_id'];



            $res=$applyInspection
                ->where('inspection_group_id',$inspection_group_id)
                ->where('status',1)
                ->where('is_reset',0)
                ->where(function ($query){
                    $query->where('probable_inspection_date','0000-00-00 00:00:00')->orWhereNull('probable_inspection_date');
                })
                ->where('id',$apply_id)->update(array('status'=>2,'probable_inspection_date'=>$i['date']));

            if(!$res){
                return [
                    'status'=>0,
                    'message'=>'分配失败'
                ];
            }

        }
        $desc=$request->input('desc');
        if($user_id){
            $res=$inspectionGroup->where('id',$inspection_group_id)->update(array('user_id'=>serialize($user_id),'desc'=>$desc));
        }

        if($res){
            return [
                'status'=>1,
                'message'=>'分配成功'
            ];
        }else{
            return [
                'status'=>0,
                'message'=>'分配失败'
            ];
        }
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
        $res1=$inspectionGroup->where('id',$inspection_group_id)->update(array('user_id'=>'','desc'=>''));
        $res2=$applyInspection->where('inspection_group_id',$inspection_group_id)->where('is_reset',0)->where('status',2)->update(array('status'=>1,'probable_inspection_date'=>''));

        if($res1&&$res2){
            return [
                'status'=>1,
                'message'=>'撤销成功'
            ];
        }else{
            return [
                'status'=>0,
                'message'=>'撤销失败'
            ];
        }

    }
    
    
    //
    /**
     * 修改验货组名
     *
     * @SWG\Post(
     *   path="/api/v1/inspection/editInspectionGroupName",
     *   tags={"修改验货组名"},
     *   summary="修改验货组名",
     *   description="修改验货组名。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Parameter(
     *         name="inspection_group_id",
     *         in="query",
     *         description="验货组id",
     *         required=true,
     *         type="integer",
     *    ),
     *   @SWG\Parameter(
     *         name="inspection_group_name",
     *         in="query",
     *         description="验货组名",
     *         required=true,
     *         type="string",
     *    ),
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
    public function editInspectionGroupName(EditInspectionGroupRequest $request,InspectionGroup $inspectionGroup) {
        $inspection_group_id= $request->input('inspection_group_id');
        $name= $request->input('inspection_group_name');
        $res=$inspectionGroup->where('id',$inspection_group_id)->update(array('name'=>$name));
        if($res===false){

            return [
                'status'=>0,
                'message'=>'修改组名失败'
            ];
        }else{
            return [
                'status'=>1,
                'message'=>'修改组名成功'
            ];
        }
    }

    //修改预计验货时间
    public function editProbableInspectionDate(Request $request,ApplyInspection $applyInspection){
        $date=$request->input('probable_inspection_date');
        if(strtotime($date)<strtotime(date('Y-m-d',time()))){
            return [
                'status'=>0,
                'message'=>'验货时间不能早于现在'
            ];
        }

        $applyInspectionObj=$applyInspection
            ->where('inspection_group_id','>',0)
            ->where('status',2)
            ->where(function ($query){
                $query->where('probable_inspection_date','neq','0000-00-00 00:00:00')->WhereNull('probable_inspection_date');
            });
        $applyInspectionRes=$applyInspectionObj->first();
        if(!$applyInspectionRes){
            return [
                'status'=>0,
                'message'=>'数据不存在'
            ];
        }
        $res=$applyInspectionObj->update(['probable_inspection_date'=>$date]);
        if($res===false){
            return [
                'status'=>0,
                'message'=>'修改失败'
            ];
        }else{
            return [
                'status'=>1,
                'message'=>'修改成功'
            ];
        }

    }







}

