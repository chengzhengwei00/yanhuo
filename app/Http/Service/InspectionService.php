<?php

namespace App\Http\Service;

use App\Http\Model\InspectionGroup;
use App\Http\Model\ApplyInspection;
use App\Http\Model\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InspectionService
{
    public function __construct(InspectionGroup $inspection_group,ApplyInspection $apply_inspection,Request $request,Response $response) {
       $this->inspection_group=$inspection_group;
       $this->request=$request;
       $this->response=$response;
       $this->apply_inspection=$apply_inspection;
    }

    //获得各组以及其包含的数据
    public function inspection_groups_list()
    {
        $data=$this->inspection_group->whereHas('apply_inspections',function ($query){
            $query->where('status', 1)->where('is_reset',0);
        })->with('apply_inspections')->orderBy('name','desc')->get();

        if(count($data)){
            foreach ($data as $item) {
                if($item->apply_inspections){
                    $scheduleService=new ScheduleService($this->request,$this->response);
                    $item->apply_inspections=$scheduleService->deal_apply_list($item->apply_inspections);
                }
           }
        }

        return $data;
    }

    //
    public function contract_inspection_list(){
        return $apply=ApplyInspection::with('contract_inspection_groups')->where('is_reset',0)->where('status',1)->paginate(100);
    }

    //获得已经分配验货的数据
    public function select_distributed_list(){
        $data=$this->apply_inspection->where('is_reset',0)->where('status',2)->has('inspection_group')->with(['inspection_group'=>function($query){
            $query->with(['user'=>function($query){
                $query->select('id','name');
            }]);
        }])->orderBy('id','desc')->get();
         //return $data;


        $data=$this->apply_inspection->where('is_reset',0)->where('status',2)->has('inspection_group')->with('inspection_group')->orderBy('id','desc')->get();
        //return $data;
        if(count($data)){

            foreach ($data as &$item) {
               $user_id=$item->inspection_group->user_id;
                $user_id=unserialize($user_id);
                $res=User::whereIn('id',$user_id)->select('name')->get();
                $item->inspection_group->user=$res;
            }
            $scheduleService=new ScheduleService($this->request,$this->response);
            $data=$scheduleService->deal_apply_list($data);
        }
        return $data;
    }








}
