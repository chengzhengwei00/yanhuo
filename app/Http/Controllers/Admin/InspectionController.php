<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Model\ApplyInspection;
use App\Http\Model\InspectionGroup;
use App\Http\Model\InspectionGroupsUser;
use App\Http\Service\ScheduleService;
use App\Http\Service\UserService;
use Exception;
use App\Http\Service\InspectionService;
use Illuminate\Http\Request;
use App\Http\Requests\ApplyInspectionRequest;
use App\Http\Model\ContractInspectionGroup;
use Illuminate\Http\Response;
use App\Http\Model\Contract;
use Illuminate\Support\Facades\Auth;
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


        $search_array=['contract_no','manufacturer','factory_simple_address'];
        $keywords=$request->input('keywords');
        $type=$request->input('type');

        foreach($search_array as $search_type)
        {
            if ($type==$search_type && $keywords!='') {

                $params['search'][$search_type]=$keywords;
            }



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


//        $search_array=['contract_no','manufacturer','factory_simple_address'];
//        $keywords=$request->input('keywords');
//        $type=$request->input('type');
//
//        foreach($search_array as $search_type)
//        {
//            if ($type==$search_type && $keywords!='') {
//
//                $params['search'][$search_type]=$keywords;
//            }
//
//
//
//        }


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
            $apply_inspection_data=$applyInspection->where('id',$id)->whereIn('status',array(0,1,2,3))->where('is_reset',0)->update(array('is_reset'=>1));
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

            $applyInspectionObj=$applyInspection->where('inspection_group_id',$inspection_group_id);
            if(!$applyInspectionObj->first()){
                return ['status'=>0,'message'=>'数据不存在'];
            }

            $res=$applyInspection->where('inspection_group_id',$inspection_group_id)
                ->update(['status'=>1,'inspection_group_id'=>0]);

            if($res===false){
                return ['status'=>0,'message'=>'撤销失败'];

            }else{
                InspectionGroup::where('id',$inspection_group_id)->delete();
                return ['status'=>1,'message'=>'撤销成功'];
            }
        }

        if($apply_id){
            $applyInspectionObj=$applyInspection->where('id',$apply_id);
            if(!$applyInspectionObj->first()){
                return ['status'=>0,'message'=>'数据不存在'];
            }

            $res=$applyInspectionObj
                ->update(['status'=>1,'inspection_group_id'=>0]);
            if($res===false){
                return ['status'=>0,'message'=>'撤销失败'];

            }else{
                return ['status'=>1,'message'=>'撤销成功'];
            }
        }

        return ['status'=>0,'message'=>'参数出错'];

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
        $user_id_res=InspectionGroupsUser::where('inspection_group_id',$inspection_group_id)->select('user_id')->first();


        if(!isset($user_id_res->user_id)||!$user_id_res->user_id){

            if(!$user_id||count($user_id)<1){
                return [
                    'status'=>0,
                    'message'=>'请选择验货人'
                ];
            }

        }
        foreach ($probable_inspection_date as $i) {




            if(!isset($i['date_end'])){
                $i['date_end']='';
            }else{
                if(strtotime($i['date_end'])<strtotime(date('Y-m-d',time()))){
                    return [
                        'status'=>0,
                        'message'=>'验货时间不能早于现在'
                    ];
                }
            }

            if(strtotime($i['date_start'])<strtotime(date('Y-m-d',time()))){
                return [
                    'status'=>0,
                    'message'=>'验货时间不能早于现在'
                ];
            }




            $apply_id=$i['apply_id'];


            if(!isset($i['contract_desc'])){
                $contract_desc='';
            }else{
                $contract_desc=$i['contract_desc'];
            }
            $applyInspectionObj=$applyInspection
                ->where('inspection_group_id',$inspection_group_id)
                ->where('status',1)
                ->where('is_reset',0)
                ->where(function ($query){
                    $query->where('probable_inspection_date_start','0000-00-00 00:00:00')->orWhereNull('probable_inspection_date_start');
                })
                ->where('id',$apply_id);
            if(!$applyInspectionObj->first()){
                return [
                    'status'=>0,
                    'message'=>'数据不存在'
                ];
            }
            $res=$applyInspectionObj->update(array('status'=>2,'probable_inspection_date_start'=>$i['date_start'],'probable_inspection_date_end'=>$i['date_end'],'contract_desc'=>$contract_desc));

            if($res===false){
                return [
                    'status'=>0,
                    'message'=>'分配失败'
                ];
            }

        }

        if($user_id){


            foreach ($user_id as $item) {
                InspectionGroupsUser::create(['inspection_group_id'=>$inspection_group_id,'user_id'=>$item]);
            }


        }

        $desc=$request->input('desc');
        if($desc){
            $res=$inspectionGroup->where('id',$inspection_group_id)->update(array('desc'=>$desc));
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
        $res1=$inspectionGroup->where('id',$inspection_group_id)->update(array('desc'=>''));
        InspectionGroupsUser::where('inspection_group_id',$inspection_group_id)->delete();
        $res2=$applyInspection->where('inspection_group_id',$inspection_group_id)->where('is_reset',0)->where('status',2)->update(array('contract_desc'=>'','status'=>1,'probable_inspection_date_start'=>null,'probable_inspection_date_end'=>null));

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
                $query->where('probable_inspection_date','!=','0000-00-00 00:00:00')->WhereNull('probable_inspection_date');
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


    //获得验货人需要的验货数据
    public function getInspectionTaskData(Request $request,InspectionService $inspectionService){
        //获得验货人id
//        $user=Auth::user();
//        $user_id=$user->id;
//        $user_id=78;
//        $inspection_task_data=InspectionGroupsUser::where('user_id',$user_id)->with(['inspection_group'=>function($query){
//                $query->with(['apply_inspections'=>function($query){
//                    $query->with('contract');
//                }]);
//            }])->get();
//        foreach ($inspection_task_data as $i) {
//            foreach ($i->inspection_group->apply_inspections as $i2) {
//                $json_data=json_decode($i2->contract->json_data);
//                $sku_infos=$json_data->SkuInfos;
//                foreach ($sku_infos as $i3) {
//                  if($i3->Data){
//                      $i3->isData=true;
//                  }else{
//                      $i3->isData=false;
//                  }
//                  if($i3->accessory){
//                      $i3->isAccessory=true;
//                  }else{
//                      $i3->isAccessory=false;
//                  }
//                }
//                return $sku_infos;
//            }
//        }




        $where['status']=2;
        $order_by=$request->input('order_by');
        $where['order_by']=$order_by;

        //获得验货人id
        $user=Auth::user();
        $user_id=$user->id;
        $user_id=78;
        $where['user_id']=$user_id;


        return $inspectionService->inspection_groups_list($where);



//$s=$inspection_task_data[0]->inspection_group->apply_inspections[0]->contract->json_data;
//        $s=json_decode($s);
//        return $s->SkuInfos;




//        以产品为例
//1.产品SKU：ProductCode
//2.箱率  RateContainer
//3.中文名称  ChineseName
//4.条形码（内箱）  BarCode
//5.外箱条码  OutsideBarCode
//6.单个净重(KG) SinglePacking
//7.单个毛重(KG) RoughWeight
//8.外箱净重(KG)  PackingWeight
//9.外箱毛重(KG) NetWeight
//10.产品尺寸（CM） 长*宽*高 ProductSizeLength  ProductSizeWidth  ProductSizeHight
//11.外箱尺寸(CM) 长*宽*高 PackingSizeLength  PackingSizeWidth  PackingSizeHight
//12.单个包装尺寸(CM) 长*宽*高SinglePackingSizeLength  SinglePackingSizeWidth  SinglePackingSizeHight
//13.产品中文描述 ChineseDescription
//14.材质 TextTure
//15.包装方式 PackingType
//16.图片 picturefile  附件图片读取，这是一个数组
//17.配件 replacement 这是一个数组，一个产品可能会有多个配件 ，配件也是要具体检验的：
//17-1：配件编号 AccessoryCode
//17-2  配件中文品名  AccessoryName
//17-3 配件描述  ChineseDescription
//17-4 配件包装   PackingType
//17-5 配件条形码  BarCode
//17-6 配件数量  StockDetailNum
//
//备注
//一个产品可能会对应多个配件
//配件也有具体信息
//
//
//
//以合同为例（合同其实就 是获取工厂信息）
//1.供方（工厂名称） Factory
//2.地址  FactoryAddress
//3.联系人  FactoryContacts
//4.电话/传真  FactoryPhone_Fax
//5.E-MAIL  FactoryEmail
//6.预计交货日期  PlanDeliveryTime
//7.总体积（m³）  TotalVolume
//8.总毛重（KG）  TotalNetWeight
//9.货物总箱数  TotalCount
//




    }



    //确认已分配验货
    public function confirm_inspection(Request $request,ApplyInspection $applyInspection){
        $inspection_group_id=$request->input('inspection_group_id');
        $applyInspectionObj=$applyInspection->whereHas('inspection_group',function ($query) use($inspection_group_id){
            $query->whereHas('inspection_group_user',function ($query) use($inspection_group_id){
                $query->where('inspection_group_id',$inspection_group_id);
            })->where('id',$inspection_group_id);
        })->where('status',2)
            ->where('is_reset',0)
            ->where('probable_inspection_date_start','!=','0000-00-00 00:00:00')
            ->whereNotNull('probable_inspection_date_start');
        if(!count($applyInspectionObj->get())){
            return [
                'status'=>0,
                'message'=>'数据不存在'
            ];
        }
        $res=$applyInspectionObj->update(['status'=>3]);
        if($res===false){
            return [
                'status'=>0,
                'message'=>'确认失败'
            ];
        }else{
            return [
                'status'=>1,
                'message'=>'确认成功'
            ];
        }

    }









}

