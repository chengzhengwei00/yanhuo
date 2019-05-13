<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Service\TaskService;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(TaskService $TaskService,Request $request)
    {
        $this->TaskService = $TaskService;
        $this->request = $request;
    }

    public function index()
    {
        //
        return $this->TaskService->list_task();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store( )
    {
        //
        return $this->TaskService->inspection_result();
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
        return $this->TaskService->task_sku_list($id);
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
    public function update(Request $request, $id)
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

    //添加任务
    public function postTaskAdd()
    {
        return $this->TaskService->add_task();
    }
    //删除任务
    public function deleteTask()
    {
        return $this->TaskService->delete_task();
    }
    public function postInspectionPost()
    {
        return $this->TaskService->inspection_result();
    }
    //获取任务下的工厂
    public function getUserTaskFactory()
    {
        return $this->TaskService->user_task_factory();
    }
    //获取工厂下的po
    public function getTaskFactoryContract()
    {
        return $this->TaskService->task_factory_contract();
    }
    //获取po下的sku
    public function getContractSkuList()
    {
        //
        return $this->TaskService->task_sku_list();
    }
    //展示验货表单sku详细信息
    public function getTaskSkuView()
    {
        return $this->TaskService->task_sku_view();
    }
    //展示配件验货表单sku详细信息
    public function getTaskAccView()
    {
        return $this->TaskService->task_acc_view();
    }
    //提交配件验货表单sku详细信息
    public function postInspectionAccPost()
    {
        return $this->TaskService->inspection_acc_result();
    }
    //检验结果任务列表
    public function getInspectionResultTaskList()
    {
        return $this->TaskService->inspection_result_task_list();
    }
    //检验结果po列表
    public function getInspectionContractResultList()
    {
        return $this->TaskService->inspection_result_contract_list();
    }
    //测试解析数据
    public function test()
    {
        return 'ssss';
    }
    //验货合同结果列表
    public function getInspectionResultContractList()
    {
        return $this->TaskService->inspection_result_contract_list();
    }
    //验货合同结果sku列表
    public function getInspectionResultContractSkuList()
    {
        return $this->TaskService->inspection_result_contract_sku_list();
    }
    //验货合同sku数据结果
    public function getInspectionResultSkuView()
    {
        return $this->TaskService->inspection_result_sku_view();
    }
    //验货合同sku原始数据
    public function geSkuOrnView()
    {
        return $this->TaskService->sku_org_view();
    }
    //验货合同sku原始数据和质检数据
    public function geSkuView()
    {
        return $this->TaskService->sku_view();
    }
    //获取全部用户
    public function getTaskUser()
    {
        return $this->TaskService->getTaskUser();
    }
    //获取图片
    public function getPicSame()
    {
        return $this->TaskService->pic_same();
    }
    //创建任务
    public function postCreateTask()
    {
        return $this->TaskService->create_task();
    }
}
