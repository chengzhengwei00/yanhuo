<?php

namespace App\Http\Service;

use App\Http\Model\ContractSchedule;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Model\Contract;
use App\Http\Model\ContractScheduleLogs;
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
        //return $scheduleListRes;



        $scheduleList=$scheduleListRes['data'];

        //获得当前合同有需求的schedule列表
        $contractScheduleDataRes=$this->getSchedulesByContract($contract_id);
//return $contractScheduleDataRes;
        //$scheduleListNew=$scheduleList;
        foreach ($scheduleList as $scheduleKey => $scheduleItem) {
            if(!$scheduleItem['is_must']){
                $scheduleItem['is_need']=0;
            }

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




    public function getScheduleIsNeedSelect($contract_id){
        $scheduleService=new ScheduleService($this->request,$this->response);
        $scheduleListRes=$scheduleService->listIsSelect();
        //return $scheduleListRes;



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

            //以下是三选一
            $isSelectArr=array('11'=>1,'12'=>1,'13'=>1,'43'=>1);
            foreach ($need_params as $i) {

                if(isset($i['is_need'])&&$i['is_need']==0&&$i['schedule_id']){
                    unset($isSelectArr[$i['schedule_id']]);

                }



            }

            if(count($isSelectArr)!=1){
                return ['status'=>'0','message'=>'唛头要保留一个'];
            }

            $scheduleService=new ScheduleService($this->request,$this->response);
            $scheduleListRes=$scheduleService->listIsSelect();


            $scheduleListIdArr=array();
            foreach ($scheduleListRes['data'] as $scheduleListItem) {
                $scheduleListIdArr[]=$scheduleListItem['id'];
            }

            foreach ($need_params as $item) {
                if(isset($item['schedule_id'])&&$item['schedule_id']){
                    $schedule_id=$item['schedule_id'];


                    //只能传入可选的schedule
                    if(!in_array($schedule_id,$scheduleListIdArr)){
                        return ['status'=>'0','message'=>'参数错误'];
                    }



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

                        $contractScheduleLogs=new ContractScheduleLogs();
                        $contractScheduleLogs->schedule_id=$schedule_id;
                        $contractScheduleLogs->contract_id=$contract_id;
                        $contractScheduleLogs->user_id= Auth::id();

                        $res=$contractScheduleLogs->save();

                    }

                }


            }
//            if($res){
//                return ['status'=>'1','message'=>'更新成功'];
//            }
        }

        $isHasData=ContractSchedule::where(array('contract_id'=>$contract_id))->get();
        if(count($isHasData)){
            Contract::where(array('id'=>$contract_id))->update(['is_out_shedule'=>1]);
        }
        //return ['status'=>'0','message'=>'更新失败'];
        return ['status'=>'1','message'=>'更新成功'];
    }


}
