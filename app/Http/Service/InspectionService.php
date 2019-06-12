<?php

namespace App\Http\Service;

use App\Http\Model\InspectionGroup;
use App\Http\Model\ApplyInspection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InspectionService
{
    public function __construct(InspectionGroup $inspection_group,Request $request,Response $response) {
       $this->inspection_group=$inspection_group;
       $this->request=$request;
       $this->response=$response;
    }

    //获得各组以及其包含的数据
    public function inspection_groups_list()
    {
        $data=$this->inspection_group->with('apply_inspections')->paginate(100);

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
        return $apply=ApplyInspection::with('contract_inspection_groups')->where('status',1)->paginate(100);
    }
}
