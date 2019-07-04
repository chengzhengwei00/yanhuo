<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Model\ContractSchedule;
use App\Http\Service\ContractService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Model\Contract;
use App\Http\Model\Standard;
use Illuminate\Support\Facades\DB;
use App\Http\Service\ScheduleService;
use App\Http\Service\ContractScheduleService;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{



    public function __construct(ScheduleService $scheduleService,Request $request)
    {
        $this->scheduleService=$scheduleService;
        $this->request=$request;
    }
    /**
     * @SWG\Get(path="/api/v1/schedule",
     *   tags={"状态列表"},
     *   summary="状态列表",
     *   description="状态列表",
     *   operationId="getContractsForApi",
     *   produces={"application/json"},
     *   @SWG\Response(response="default", description="操作成功"),
     *   @SWG\Response(
     *     response=200,
     *     description="状态列表"
     *   ),
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     * )
     */
    public function index()
    {
        //
        return $this->scheduleService->list();

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return json_encode(array(1,1));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        return $this->scheduleService->edit();
    }


    /**
     * 展示订单进度状态
     *
     * @SWG\Get(
     *   path="/api/v1/schedule/{id}",
     *   tags={"展示订单进度状态"},
     *   summary="展示订单进度状态",
     *   description="展示订单进度状态。",
     *   operationId="getschedule",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(response="default", description="操作成功"),
     *   @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="合同id",
     *         required=true,
     *         type="integer",
     *     ),
     * )
     */
    public function show($id)
    {
        return $this->scheduleService->view($id);
    }





    //历史跟单记录
    public function getHistory( )
    {

        return $this->scheduleService->history();
    }
    //历史跟单记录
    public function getHistoryView( )
    {

        return $this->scheduleService->history_view();
    }
    //
    /**
     * 获得合同列表
     *
     * @SWG\Get(
     *   path="/api/v1/schedule/contract-list",
     *   tags={"获得合同列表"},
     *   summary="获得合同列表",
     *   description="获得合同列表",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(response="default", description="操作成功"),
     * )
     */
    public function getContractList( )
    {

        return $this->scheduleService->contract_list();
    }


    //申请验货
    public function postApplyInspection( )
    {

        return $this->scheduleService->apply_inspection();
    }








    /**
     * 申请验货列表
     *
     * @SWG\Get(
     *   path="/api/v1/schedule/apply-inspection-list",
     *   tags={"申请验货列表"},
     *   summary="申请验货列表",
     *   description="申请验货列表",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(response="default", description="操作成功"),
     * )
     */
    public function getApplyInspectionList(Request $request)
    {
//        $where[]=array('apply_inspections.inspection_group_id','=',0);
//
//        $params['status']=0;
//        $params['where']=$where;






        $params['status']=array(0,1,2);
        $order_by=$request->input('order_by');
        if(isset($order_by)){
            $params['order_by']=$order_by;
        }
        return $this->scheduleService->apply_list_by_address($params);

        //return $this->scheduleService->apply_inspection_list();
    }
    //
    /**
     * 提交质检部
     *
     * @SWG\Post(
     *   path="/api/v1/schedule/post-inspection-department",
     *   tags={"提交质检部"},
     *   summary="提交质检部",
     *   description="提交质检部。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(response="default", description="操作成功"),
     *   @SWG\Parameter(
     *         name="id",
     *         in="formData",
     *         description="验货id",
     *         required=true,
     *         type="integer",
     *    ),
     * )
     */
    public function postPostInspectionDepartment( )
    {

        return $this->scheduleService->post_inspection_department();
    }


    //获得验货列表
    public function getApplyDepartmentList( Request $request)
    {
        //return $this->scheduleService->apply_department_list();

        $params['status']=array(1,2);
        $order_by=$request->input('order_by');
        if(isset($order_by)){
            $params['order_by']=$order_by;
        }

        return $this->scheduleService->apply_list_by_address($params);
    }


    //展示合同对schedule的需求状况
    public function getScheduleIsNeed(ContractScheduleService $contractScheduleService){
        $contract_id=$this->request->get('contract_id');

        //获得合同信息
        $contractService=new ContractService($this->request);
        $res=$contractService->contract_info($contract_id);
        $arr['contract_no']=$res->InspectionRequiremenCode;
        $arr['factory_name']=$res->FactoryName;
        $arr['sku_list']=$contractService->sku_list($contract_id);
        $arr['schedule_list_select']=$contractScheduleService->getScheduleIsNeedSelect($contract_id);
        return $arr;



    }

    //修改合同对schedule的需求状况
    public function updateScheduleIsNeed(ContractScheduleService $contractScheduleService){
        $contract_id=$this->request->post('contract_id');
        $need_params=$this->request->post('need_params');

        $res=$contractScheduleService->updateScheduleIsNeed($contract_id,$need_params);
        return $res;
    }



    //设置延迟跟踪
    public function setDelayTrack(Request $request,ScheduleService $scheduleService){
       //获得合同id
        $contract_id=$request->contract_id;
        $res=Contract::where('id',$contract_id)->first();
        if(isset($res->delay_track)&&$res->delay_track==1){
            return ['status'=>'0','message'=>'已经在延迟跟踪了'];
        }

        //$scheduleService=new ScheduleService();
        $plan_data=$scheduleService->getPlanDay($contract_id);
        //return $plan_data;
        if($plan_data){
            if($plan_data>-60){
                return ['status'=>'0','message'=>'超出约定交货时长60天以内不能延迟跟踪'];
            }

            Contract::where('id',$contract_id)->update(array('delay_track'=>1));
            return ['status'=>'1','message'=>'延迟跟踪成功'];
        }else{
            return ['status'=>'0','message'=>'合同不存在'];
        }


    }


    //恢复跟踪
    public function setTrack(Request $request){
        $contract_id=$request->contract_id;
        $res=Contract::where('id',$contract_id)->first();
        if(isset($res->delay_track)&&$res->delay_track==0){
            return ['status'=>'0','message'=>'已经在恢复跟踪了'];
        }
        Contract::where('id',$contract_id)->update(array('delay_track'=>0));
        return ['status'=>'1','message'=>'恢复跟踪成功'];
    }

    //60天内必须恢复跟踪
    public function setTrackAll(){
        //获得60天后的时间
        $time=time()+3600*24*60;
        $date=date('Y-m-d H:i:s',$time);
        //return $date;
        Contract::where('plan_delivery_time','<',$date)->update(array('delay_track'=>0));
        $log='恢复跟踪成功'.date('Y-m-d',$time);
        Log::info($log);
    }

    //更新合同表的进度
    public function update_schedule_contracts_all(ScheduleService $scheduleService){
        return $scheduleService->update_schedule_contracts_all();
    }











}
