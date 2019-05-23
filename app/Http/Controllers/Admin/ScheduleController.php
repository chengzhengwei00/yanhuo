<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Model\ContractSchedule;
use Illuminate\Http\Request;
use App\Http\Model\Contract;
use App\Http\Model\Standard;
use Illuminate\Support\Facades\DB;
use App\Http\Service\ScheduleService;
use App\Http\Service\ContractScheduleService;

class ScheduleController extends Controller
{



    public function __construct(ScheduleService $scheduleService,Request $request)
    {
        $this->scheduleService=$scheduleService;
        $this->request=$request;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        //

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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
    //历史跟单记录
    public function getContractList( )
    {

        return $this->scheduleService->contract_list();
    }
    //申请验货
    public function postApplyInspection( )
    {

        return $this->scheduleService->apply_inspection();
    }
    //申请验货列表
    public function getApplyInspectionList( )
    {

        return $this->scheduleService->apply_inspection_list();
    }
    //提交质检部
    public function postPostInspectionDepartment( )
    {

        return $this->scheduleService->post_inspection_department();
    }
    //任务五列表
    public function getApplyDepartmentList( )
    {
        return $this->scheduleService->apply_department_list();
    }
    //延迟跟踪
    public function postDelayTrack()
    {
        return $this->scheduleService->delay_track();
    }


    //展示合同对schedule的需求状况
    public function getScheduleIsNeed(ContractScheduleService $contractScheduleService){
        $contract_id=$this->request->get('contract_id');
        return $contractScheduleService->getScheduleIsNeedSelect($contract_id);

        //获得所有schedule
//        $scheduleListRes=$this->scheduleService->list();
//        $scheduleList=$scheduleListRes['data'];
//
//        //获得当前合同有需求的schedule列表
//        $contract_id=$this->request->get('contract_id');
//        $contractScheduleDataRes=$contractScheduleService->getSchedulesByContract($contract_id);
////return $contractScheduleDataRes;
//        $scheduleListNew=$scheduleList;
//        if($contractScheduleDataRes&&$contractScheduleDataRes['data']){
//
//            foreach ($contractScheduleDataRes['data'] as $item) {
//                foreach ($scheduleList as $scheduleKey=> $scheduleItem) {
//                   //$scheduleListNew[$scheduleKey]['is_need']=0;
//                   if($item['schedule_id']==$scheduleItem['id']){
//                       $scheduleListNew[$scheduleKey]['is_need']=1;
//                       continue;
//                   }
//                }
//            }
//
//        }
//        return $scheduleListNew;


    }

    //修改合同对schedule的需求状况
    public function updateScheduleIsNeed(ContractScheduleService $contractScheduleService){
        $contract_id=$this->request->post('contract_id');
        $need_params=$this->request->post('need_params');

//        $contract_id=31;
//        $need_params=array(
//            array(
//                'is_need'=>1,
//                'schedule_id'=>2
//            ),
//            array(
//                'is_need'=>1,
//                'schedule_id'=>3
//            ),
//            array(
//                'is_need'=>0,
//                'schedule_id'=>4
//            )
//        );
        $res=$contractScheduleService->updateScheduleIsNeed($contract_id,$need_params);
        return $res;
    }







}
