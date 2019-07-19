<?php

namespace App\Http\Service;

use App\Http\Model\InspectionGroup;
use App\Http\Model\ApplyInspection;
use App\Http\Model\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InspectionService
{
    public function __construct(InspectionGroup $inspection_group,ApplyInspection $apply_inspection,Request $request,Response $response) {
       $this->inspection_group=$inspection_group;
       $this->request=$request;
       $this->response=$response;
       $this->apply_inspection=$apply_inspection;
    }

    //获得各组以及其包含的数据
    public function inspection_groups_list($where=array())
    {


        if(!isset($where['status'])){
            $status=1;
        }else{
            $status=$where['status'];
        }


        $keywords=$this->request->input('keywords');
        $type=$this->request->input('type');
        $params_search=array();
        if ($type=='contract_no' && $keywords!='') {

            $params_search[]=array('contract_no','like','%'.$keywords.'%');
        }
        if ($type=='factory_simple_address' && $keywords!='') {

            $params_search[]=array('factory_simple_address','like','%'.$keywords.'%');
        }
        if ($type=='manufacturer' && $keywords!='') {

            $params_search[]=array('manufacturer','like','%'.$keywords.'%');
        }




        if(isset($where['user_id'])&&$where['user_id']){
            $user_id=$where['user_id'];
            $inspection_group_datas=$this->inspection_group->whereHas('apply_inspections',function ($query) use($status,$params_search){
                $query->where('status', $status)->where('is_reset',0)->when($params_search, function ($query, $params_search) {


                    $query->whereHas('contract',function ($query) use ($params_search){
                        $query->where($params_search);
                    });
                });
            })->whereHas('inspection_group_user',function ($query) use($user_id){
                $query->where('user_id', $user_id);
            });
        }else{
            $inspection_group_datas=$this->inspection_group->whereHas('apply_inspections',function ($query) use($status,$params_search){
                $query->where('status', $status)->where('is_reset',0)->when($params_search, function ($query, $params_search) {


                    $query->whereHas('contract',function ($query) use ($params_search){
                        $query->where($params_search);
                    });
                });
            });
        }



        if(isset($where['order_by'])&&in_array($where['order_by'],array('asc','desc'))){
            $order_by=$where['order_by'];
            $inspection_group_datas=$inspection_group_datas->orderBy(DB::raw("convert(name using gbk)"),$order_by)->get();
        }else{
            $inspection_group_datas=$inspection_group_datas->get();
        }


        $params=array('status'=>$status);
        $user_id=array();
        foreach ($inspection_group_datas as $item) {
            if($item->inspection_group_user){
                $inspection_group_users=$item->inspection_group_user;

                foreach ($inspection_group_users as $inspection_group_user) {
                    $user_id[]=$inspection_group_user->user_id;
                }
                if(isset($user_id)){
                    $item->user_id=$user_id;
                    $res=User::whereIn('id',$user_id)->select('name')->get();
                    $user_arr=array();
                    foreach ($res as $ir) {
                        $user_arr[]=$ir['name'];
                    }
                    $item->user=$user_arr;
                }else{
                    $item->user_id='';
                }



            };



            if($item['id']){
                $params['inspection_group_id']=$item['id'];
            }


            $scheduleService=new ScheduleService($this->request,$this->response);


            if(isset($where['user_id'])&&$where['user_id']){
                $apply_inspections=$scheduleService->deal_apply_list_address_ss($params);
                $item['apply_inspections']=$apply_inspections;
            }else{
                $apply_inspections=$scheduleService->apply_list_by_address($params);
                $item['apply_inspections']=$apply_inspections['data'];
            }



        }
         return $inspection_group_datas;



    }









    //
    public function contract_inspection_list(){
        return $apply=ApplyInspection::with('contract_inspection_groups')->where('is_reset',0)->where('status',1)->paginate(100);
    }










}
