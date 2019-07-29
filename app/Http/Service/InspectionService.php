<?php

namespace App\Http\Service;

use App\Http\Model\InspectionGroup;
use App\Http\Model\ApplyInspection;
use App\Http\Model\InspectionGroupsUser;
use App\Http\Model\InspectionRecordInfo;
use App\Http\Model\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InspectionService
{
    public function __construct(InspectionGroup $inspection_group,
                                ApplyInspection $apply_inspection,
                                InspectionGroupsUser $inspection_groups_user,
                                InspectionRecordInfo $inspection_record_info,
                                Request $request,Response $response) {
       $this->inspection_group=$inspection_group;
       $this->request=$request;
       $this->response=$response;
       $this->apply_inspection=$apply_inspection;
       $this->inspectionGroupsUser=$inspection_groups_user;
        $this->inspection_record_info=$inspection_record_info;
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
        $params_group_search=array();
        if($type=='inspection_group_no' && $keywords!=''){
            $params_group_search[]=array('inspection_group_no','like','%'.$keywords.'%');
        }
        $user_name_params=array();
        if($type=='user_name' && $keywords!=''){

            $user_name_params[]=array('name','like','%'.$keywords.'%');
        }




        if(isset($where['user_id'])&&$where['user_id']){
            $user_id=$where['user_id'];
            $inspection_group_datas=$this->inspection_group->where($params_group_search)->whereHas('apply_inspections',function ($query) use($status,$params_search){
                $query->where('status', $status)->where('is_reset',0)->when($params_search, function ($query, $params_search) {


                    $query->whereHas('contract',function ($query) use ($params_search){
                        $query->where($params_search);
                    });
                });
            })->whereHas('inspection_group_user',function ($query) use($user_id){
                $query->where('user_id', $user_id);
            });
        }else{
            $inspection_group_datas=$this->inspection_group->where($params_group_search)->whereHas('apply_inspections',function ($query) use($status,$params_search){
                $query->where('status', $status)->where('is_reset',0)->when($params_search, function ($query, $params_search) {


                    $query->whereHas('contract',function ($query) use ($params_search){
                        $query->where($params_search);
                    });
                });
            })->when($user_name_params,function($query,$user_name_params){
                $query->whereHas('inspection_group_user',function ($query) use($user_name_params){
                    $query->whereHas('user',function ($query) use($user_name_params){
                        $query->where($user_name_params);
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

        foreach ($inspection_group_datas as $item) {
            $user_id=array();
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


    public function generate_inspection_group_no($res=array()){
        if(!$res){
            $res=$this->inspection_group->select('inspection_group_no',DB::raw("date_format(created_at,'%Y%m') as create_at"))->orderBy('id','desc')->first();
        }

        if(isset($res['inspection_group_no'])&&$res['create_at']==date('Ym')){
            $str=substr($res['inspection_group_no'],-4);
            $str=intval($str)+1;
        }else{
            $str='0001';
        }
        if(strlen($str)<4){
            $str=str_pad($str,4,0,STR_PAD_LEFT);
        }

        return  'YH-'.date('ym',time()).$str;
    }


    //得到用户是否有权利验货
    public function  get_inspection_user_promission($apply_id='',$inspection_group_id='',$user_id){
        if($apply_id){
            $inspection_group_id=$this->apply_inspection->where('id',$apply_id)->select('inspection_group_id')->first();

        }
        $user_id_arr=$this->inspectionGroupsUser->where('inspection_group_id',$inspection_group_id)->get();

        foreach ($user_id_arr as $i) {
            if($i==$user_id){
                return true;
            }
        }

        return false;

    }


    public function add_inspection_task_data(){
        $requestData=$this->request->all();
        $params_all=array(
            'bar_code',
            'chinese_name',
            'count',
            'detail_count',
            'is_new_product',
            'net_weight',
            'outside_bar_code',
            'packing_size',
            'packing_weight',
            'sku',
            'product_size',
            'rate_container',
            'rough_weight',
            'single_packing',
            'single_packing_size',
            'text_ture',
            //'pictures'
        );
        $params_data=array();
        foreach ($params_all as $item) {
            if(isset($requestData[$item])&&$requestData[$item]){
                $params_data[$item]=$requestData[$item];
            }
        }
        $apply_inspection_id=$requestData['apply_id'];

        $res=$this->inspection_record_info
            ->updateOrCreate(['apply_inspection_id'=>$apply_inspection_id],$params_data);
        if($res===false){
            return ['status' => 0, 'message' => '提交失败'];
        }else{
            return ['status' => 1, 'message' => '提交成功'];
        }
    }



    public function upload_inspection_task_img($img=array()){
        $img_arr=$this->request->input('inspection_task_img');
        if(!$img_arr){
            $img_arr=$img;
        }
        if(!is_array($img_arr)){
            $img_arr=(array)$img_arr;
        }


        //这是通过base64传输的
        if($img_arr) {

            foreach ($img_arr as $i) {
                if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $i, $result)) {
                    $type=$result[2];
                    if (in_array($type, array('pjpeg', 'jpeg', 'jpg', 'gif', 'bmp', 'png'))) {
                        $img = base64_decode(str_replace($result[1], '', $i));
                        $filename = uniqid() . '.'.$type;
                        $bool = Storage::disk('public')->put($filename, $img);
                        //判断是否上传成功
                        if ($bool) {
                            return ['status' => 1, 'message' => '上传成功'];
                        } else {
                            return ['status' => 0, 'message' => '上传失败'];
                        }
                    }
                }
            }

        }
    }






    //
    public function contract_inspection_list(){
        return $apply=ApplyInspection::with('contract_inspection_groups')->where('is_reset',0)->where('status',1)->paginate(100);
    }










}
