<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Model\Contract;
use App\Http\Model\Standard;
use Illuminate\Support\Facades\DB;
use App\Http\Service\ScheduleService;

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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

}
