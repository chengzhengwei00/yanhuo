<?php

namespace App\Http\Service;

use App\Http\Model\ContractSchedule;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Model\Contract;
use App\Http\Model\ContractStandard;
use App\Http\Model\Standard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractScheduleService
{
    public function __construct(Request $request,Response $response) {
        //$this->scheduleService=$scheduleService;
        $this->request=$request;
        $this->response=$response;
    }

    public function getSchedulesByContract($contract_id)
    {

        if($contract_id){


            $data=ContractSchedule::where('contract_id', '=', $contract_id)->get();

            return ['status'=>'1','message'=>'获取成功','data'=>$data];

        }



    }


    public function getScheduleIsNeed($contract_id){
        $scheduleService=new ScheduleService($this->request,$this->response);
        $scheduleListRes=$scheduleService->list();
        $scheduleList=$scheduleListRes['data'];

        //获得当前合同有需求的schedule列表
        $contractScheduleDataRes=$this->getSchedulesByContract($contract_id);
//return $contractScheduleDataRes;
        //$scheduleListNew=$scheduleList;
        foreach ($scheduleList as $scheduleKey => $scheduleItem) {
                $scheduleItem['is_need']=0;
        }
        if($contractScheduleDataRes&&$contractScheduleDataRes['data']){

            foreach ($contractScheduleDataRes['data'] as $item) {
                foreach ($scheduleList as $scheduleKey=> $scheduleItem) {
                    //$scheduleListNew[$scheduleKey]['is_need']=0;
                    if($item['schedule_id']==$scheduleItem['id']){
                        //$scheduleListNew[$scheduleKey]['is_need']=1;
                        //$arr=array('is_need',1);
                        //array_push($scheduleListNew,$arr);
                        $scheduleItem['is_need']=1;
                        continue;
                    }
                }
            }

        }

        return $scheduleList;
//        $a=array_values(json_decode($scheduleList,true));
//        return $a;
    }


    public function updateScheduleIsNeed($contract_id,$need_params){

        if($need_params&&$contract_id){
            $res=false;
            foreach ($need_params as $item) {
                if(isset($item['schedule_id'])&&$item['schedule_id']){
                    $schedule_id=$item['schedule_id'];
                    if(isset($item['is_need'])){

                        if($item['is_need']==1){
                            $contractScheduleRes=ContractSchedule::where(array('schedule_id'=>$schedule_id,'contract_id'=>$contract_id))->first();
                            if(!$contractScheduleRes){
                                $contractSchedule=new ContractSchedule();
                                $contractSchedule->schedule_id=$schedule_id;
                                $contractSchedule->contract_id=$contract_id;
                                $res=$contractSchedule->save();
                            }
                        }
                        if($item['is_need']==0){
                            $contractScheduleRes=ContractSchedule::where(array('schedule_id'=>$schedule_id,'contract_id'=>$contract_id))->first();
                            if($contractScheduleRes){
                                $res=ContractSchedule::where(array('schedule_id'=>$schedule_id,'contract_id'=>$contract_id))->delete();
                            }
                        }

                    }

                }


            }
//            if($res){
//                return ['status'=>'1','message'=>'更新成功'];
//            }
        }
        //return ['status'=>'0','message'=>'更新失败'];
        return ['status'=>'1','message'=>'更新成功'];
    }


}
