<?php

namespace App\Http\Service;

use App\Http\Model\Contract;
use App\Http\Model\ContractSchedule;
use App\Http\Model\ManageList;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Model\User;
use App\Http\Model\UserSchedule;
use App\Http\Model\Schedule;
use App\Http\Model\Standard;
use App\Http\Service\ContractService;
use App\Http\Service\FileService;
use App\Http\Service\PermissionsService;
use App\Http\Model\ApplyInspection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    public $status;

    public $inspection_status=[
        '未提交质检部',//未申请
        '已提交质检部', //已申请
        '已分配验货'//已分配
    ];

    public function __construct(Request $request,Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getUserContract($work_number){
        //获得用户负责的sku
        $manage_list_data=ManageList::where('work_number',$work_number)->with('standard')->get();

        if(count($manage_list_data)==0){
            return [];
        }

        $contract_arr=array();
        foreach ($manage_list_data as $i) {
            if($i['standard']){
                $contract_arr[]=$i['standard']['contract_id'];
            }

        }
        //获得用户负责的contract_id
        //Contract::whereIn('id',)

        return array_unique($contract_arr);
    }




    public function contract_list()
    {

        $current_user=Auth::user();


        $user_name=$current_user->name;
        $user_permission=new PermissionsService($this->request);
        $permission=$user_permission->show_user_role_permission_by_id($current_user->id);

        $show_all=0;
        //$department='';
        foreach($permission['data'] as $datum)
        {
            if($datum->user_limit=='show-all-orders') $show_all=1;
        }
        if($show_all==1){
            $data=Contract::where('id','>',0);
        }else{


            if(isset($current_user['company_no'])&&$current_user['company_no']){
                //得到该用户负责的合同
                $contract_arr=$this->getUserContract($current_user['company_no']);
                if(count($contract_arr)>0){
                    //$department='manager';
                    $data=Contract::whereIn('id',$contract_arr);
                }else{
                    $data=Contract::where('user_list','like',"%$user_name%");
                }
            }


        }


        $data=$data->where(function ($query){
        $query->where('status_code','03')
            ->orWhere('status_code','08');});

        $is_delay_order=$this->request->get('is_delay_order');
        $is_out_shedule=$this->request->get('is_out_shedule');
        $break_update=$this->request->get('break_update');
        if($is_delay_order==1){
            $data=$data->where('delay_track','1');
        }
        if($is_delay_order==2){
            $data=$data->where('delay_track','0');
        }
        if($is_out_shedule==1){
            $data=$data->where('is_out_shedule','1');
        }
        if($is_out_shedule==2){
            $data=$data->where('is_out_shedule','0');
        }

        if($break_update==1){
            $data=$data->where('break_update','1');
        }
        if($break_update==2){
            $data=$data->where('break_update','0');
        }

        //搜索
        $data=$this->contract_search($data);
        //排序
        $data=$this->contract_sort($data);


        $data=$data->paginate(10);

        foreach ($data as &$item)
        {
            $repeat_record=$this->repeat_record($item->id);
            $contractService= new ContractService($this->request);
            $sku_list= $contractService->sku_list($item->id);
            //获得验货数量
            $apply_inspection_num_info=$this->getInspectionNumPerSku($item->apply_inspection());
            //$inspection_service=new InspectionService();
            //$apply_inspection_num_info=$inspection_service->getInspectionNumPerSku($item->apply_inspection());



            if(isset($apply_inspection_num_info)&&$apply_inspection_num_info){
                    foreach ($sku_list as $sku_key=> $sku_list_item) {

                        if(isset($apply_inspection_num_info[$sku_list_item['sku']])){
                            $inspection_item=$apply_inspection_num_info[$sku_list_item['sku']];
                            $sku_list[$sku_key]['inspectioned_num'] =  $inspection_item['apply_num'];
                            $sku_list[$sku_key]['inspection_left_num'] = $sku_list_item['detail_counts']- $inspection_item['apply_num'];
                            $sku_list[$sku_key]['sku'] = $sku_list_item['sku'];
                        }else{
                            isset($sku_list[$sku_key]['inspectioned_num'])&&$sku_list[$sku_key]['inspectioned_num']?$sku_list[$sku_key]['inspectioned_num'] =  $sku_list[$sku_key]['inspectioned_num']:$sku_list[$sku_key]['inspectioned_num'] =  0;
                            isset($sku_list[$sku_key]['inspection_left_num'])&&$sku_list[$sku_key]['inspection_left_num']?$sku_list[$sku_key]['inspection_left_num'] = $sku_list[$sku_key]['inspection_left_num']:$sku_list[$sku_key]['inspection_left_num']=$sku_list[$sku_key]['detail_counts'];
                            $sku_list[$sku_key]['sku'] = $sku_list_item['sku'];
                        }


                    }


            }else{
                    foreach ($sku_list as $sku_key=> $sku_list_item) {
                            $sku_list[$sku_key]['inspectioned_num'] = $sku_list[$sku_key]['detail_counts'] ;
                            $sku_list[$sku_key]['inspection_left_num'] = $sku_list[$sku_key]['detail_counts'] ;

                    }
            }

            $item->sku_list=$sku_list;

            $UserSchedule=UserSchedule::where('contract_id',$item->id)->orderBy('id','desc')->first();//最新更新记录
            //计算勾选的数量
            $except_count=0;
            if($UserSchedule) {
                $count=(array)json_decode($UserSchedule->status);
                $i=0;
                foreach ($count as $c) {
                    if(isset($c->status) && $c->status==1)$i++;

                    //停滞周数，用来标记停滞记录
                    if(isset($value->schedule_id) && $c->schedule_id==42) {

                        $repeat_record=1;
                        $except_count=1;
                    }
                }
                $item->quantity=$i-$except_count;
            }else{
                $item->quantity=0;
            }






            $contractScheduleService=new ContractScheduleService($this->request,$this->response);

            $total_count=$this->count_schedule_contract($item->id);
            $contractScheduleList=$contractScheduleService->getSchedulesByContract($item->id);
            $mustSelectStatus=count($contractScheduleList['data']);
            $item->must_select_status=$mustSelectStatus?1:0;
            $item->total_count=$total_count;
            //$item->rate=$item->quantity/$total_count;
            $item->rate=$item->progress;
            $item->to_week=DifferWeek($item->sign_time,time());
            $item->to_day=DifferDay($item->sign_time,time());
            $item->plan_week=DifferWeek($item->plan_delivery_time,time());
            $item->plan_day=DifferDay($item->plan_delivery_time,time());
            $item->repeat_record=$repeat_record;
			$item->break_update=$this->break_update($item->id);

            unset($item->json_data);
        }

//        $newData=[
//            'department'=>$department,
//            'data'=>$data,
//        ];
        return ['status'=>'1','message'=>'获取成功','data'=>$data];
    }

    //获得各sku的验货数量
    public function getInspectionNumPerSku($apply_inspection_obj){
        $apply_inspection_infos=$apply_inspection_obj->where('is_reset',0)->orderBy('id','desc')->get();


        $arr=array();
        if(count($apply_inspection_infos)){
            foreach ($apply_inspection_infos as $apply_inspection_info) {
                if($apply_inspection_info&&$apply_inspection_info->sku_num){
                    $apply_inspection_num_info=json_decode($apply_inspection_info->sku_num);
                    foreach ($apply_inspection_num_info as $apply_inspection_num_item) {
                        $k=$apply_inspection_num_item->sku;

                        if($arr){
                            if(isset($arr[$k])){
                                $arr[$k]['apply_num']+=$apply_inspection_num_item->quantity;
                            }else{
                                $arr[$k]['apply_num']=$apply_inspection_num_item->quantity;
                            }
                        }else{
                            $arr[$k]['apply_num']=$apply_inspection_num_item->quantity;
                        }


                    }

                }
            }




        }

        return $arr;
    }




	    //判断合同是否有中断更新
    public function break_update($contract_id)
    {
        $history=$this->history($contract_id);
        if(!empty($history['data']))
        {
            $history_data=array_slice($history['data'],0,-1);
            foreach($history_data as $datum)
            {
                if($datum['id']!=''){
                    $mark=true;
                }
                if($datum['id']==''){
                    if(isset($mark))
                    {
                        $mark=false;
                        break;
                    }

                }
            }
        }
        if(isset($mark) && $mark==false){
            $result=true;
        }else{
            $result=false;
        }
        return $result;
    }

    //得到某个合同超出交货时长
    public function getPlanDay($contract_id){
        $contractData=Contract::where('id',$contract_id)->first();
        if(isset($contractData->plan_delivery_time)&&$contractData->plan_delivery_time){
            $plan_day=DifferDay($contractData->plan_delivery_time,time());
            return $plan_day;
        }
        return [];

    }

    //合同排序
    private function contract_sort($data)
    {
        $order_sign=$this->request->input('order_sign');
        $order_progress=$this->request->input('order_progress');
        $order_delivery=$this->request->input('order_delivery');
        //采购时间排序
        if($order_sign!=''){
            if($order_sign=='asc')$data=$data->orderBy('sign_time','desc');
            if($order_sign=='desc')$data=$data->orderBy('sign_time','asc');

        }
        //进度排序
        if($order_progress!=''){
            $data=$data->orderBy(DB::raw("progress+0"),$order_progress);
        }


        //按照超出约定交货时长排序
        if($order_delivery){
            if($order_delivery=='asc')$data=$data->orderBy('plan_delivery_time','desc');
            if($order_delivery=='desc')$data=$data->orderBy('plan_delivery_time','asc');
        }


        return $data;
    }


    private function contract_search($data){


        $search_array=['contract_no','manufacturer','create_user'];
        $keywords=$this->request->input('keywords');
        $type=$this->request->input('type');

        $is_update=$this->request->input('is_update');
        $start_time=$this->request->input('start_time');
        $end_time=$this->request->input('end_time');
        foreach($search_array as $search_type)
        {
            if ($type==$search_type && $keywords!='') {
                $data=$data->where($search_type,'like','%'.$keywords.'%');
            }



        }


        if($type=='sku' && $keywords!='')
        {
            $contract_id=[];
            $standard=Standard::where('sku',$keywords)->groupBy('contract_id')->select('contract_id')->get();
            foreach($standard as $item)
            {
                $contract_id[]=$item->contract_id;
            }
            $data=$data->whereIn('id',$contract_id);
        }


        if(isset($start_time)&&isset($end_time)){


            $start_time=substr($start_time,0,-3);
            $end_time=substr($end_time,0,-3);
            $start_time=date('Y-m-d',(int)$start_time);
            $end_time=date('Y-m-d',(int)($end_time)+24*3600);
            $ids=UserSchedule::where('updated_at','>',$start_time)->where('updated_at','<',$end_time)->select('contract_id')->get();

            if($is_update==2){
                $data=$data->whereNotIn('id',$ids);
            }
            if($is_update==1){

                $data=$data->whereIn('id',$ids);
            }

        }

        return $data;
    }


    //计数——查找有相同记录的数据，目的用来做延迟周数
    public function repeat_record($contract_id)
    {


        $UserSchedule=UserSchedule::where('contract_id',$contract_id)->where('repeat_record','1')->get();
        $i=$UserSchedule->count();
        return $i;
    }
    //状态列表
    public function list($where=array())
    {
        if($where){
            return ['status'=>1,'message'=>'获取成功','data'=>Schedule::where($where)->orderBy('sort','asc')->get()];
        }
        return ['status'=>1,'message'=>'获取成功','data'=>Schedule::orderBy('sort','asc')->get()];
    }

    //获得不是必须的状态列表
    public function listIsMust()
    {
        return ['status'=>1,'message'=>'获取成功','data'=>Schedule::where('is_must',1)->orderBy('sort','asc')->get()];
    }

    //获得不是必须的状态列表
    public function listIsSelect()
    {
        return ['status'=>1,'message'=>'获取成功','data'=>Schedule::where('is_must',0)->orderBy('sort','asc')->get()];
    }
    //展示订单进度状态
    public function view($contract_id)
    {

        return $this->common_view($contract_id);
    }
    //显示订单进度详情
    //$contract_id合同id
    public function common_view($contract_id)
    {
        $id=$this->request->input('id');
        $first_contract_id=$contract_id;
        if(empty($contract_id))
        {
            $UserSchedule=UserSchedule::find($id);

            if(isset($UserSchedule->contract_id)&&$UserSchedule->contract_id){
                $contract_id=$UserSchedule->contract_id;
            }else{
                return ['status' => 1, 'message' => '参数错误','data'=>array('schedule'=>[],'sku_list'=>[])];
            }

        }else{
            $UserSchedule=UserSchedule::where('contract_id',$contract_id)->orderBy('id','desc')->first();
        }

        //获得sku_list
        $contractService= new ContractService($this->request);

        $sku_list= $contractService->sku_list($contract_id);

        //每个状态对应日期合并，按照先后顺序合并，主要看每个状态的最初的操作日期,以前安装倒叙合并，返回每个状态的图片
        $created_at=[];//状态id做key，返回时间数组
        $photo_array=[];//状态id做key，图片数组
        $all=UserSchedule::where('contract_id',$contract_id)->get();


        foreach($all as $item)
        {
            $new_array=[];
            $status_array = (array)json_decode($item->status);
            foreach($status_array as $data)
            {
                if($data->status==1) {
                    $new_array[$data->schedule_id] = $item->created_at->format('Y-m-d');
                }
                if($data->status==1 && !empty($data->photo)) {
                    $photo_array[$data->schedule_id] = $data->photo;
                }
            }

            $created_at=$created_at+$new_array;
        }

        $contractSchedule=new ContractScheduleService($this->request,$this->response);
        $contractScheduleList=$contractSchedule->getSchedulesByContract($contract_id);
        //return $contractScheduleList['data'];
        if(isset($contractScheduleList['data'])&&count($contractScheduleList['data'])==0){
            return ['status' => 1, 'message' => '请选择排除项','data'=>array('schedule'=>[],'sku_list'=>[])];
        }

        $contractScheduleService=new ContractScheduleService($this->request,$this->response);
        $Schedule=$contractScheduleService->getScheduleIsNeed($contract_id);

        $newSchedule=array();
        if($UserSchedule) {
            foreach ($Schedule as $Schedulekey=> $data) {

                    if($data['is_need']==1||$data['is_must']==1){

                        $status_array = (array)json_decode($UserSchedule->status);
                        $data->status=0;
                        foreach($status_array as $item){
                            if (isset($item->schedule_id) && $item->schedule_id==$data->id) {
                                $data->status = $item->status;
                                $data->change_time = $item->change_time;
                                $data->update_time=isset($new_array1[$item->schedule_id])?$created_at[$item->schedule_id]:'';
                                $data->is_need = $item->is_need;
                                $data->show_photo = isset($photo_array[$item->schedule_id])?$photo_array[$item->schedule_id]:[];
                            }
                        }
                        $newSchedule[$Schedulekey]=$data;
                    }




            }
            //return $newSchedule;
            if(isset($UserSchedule->sku_schedule)&&!empty($UserSchedule->sku_schedule)){
                $sku_schedule=json_decode($UserSchedule->sku_schedule,true);
                if($sku_schedule&&isset($sku_schedule[0]['schedule_id'])&&$sku_schedule[0]['schedule_id']==37){
                    foreach ($sku_list as $k => $item) {
                        foreach ($sku_schedule as $item2) {

                            if($item['sku']==$item2['sku']){
                                $sku_list[$k]['complete_counts']=isset($item2['complete_counts'])?$item2['complete_counts']:0;
                                $sku_list[$k]['complete_time']=isset($item2['complete_time'])?$item2['complete_time']:'';
                                $sku_list[$k]['progress_percent']=isset($item2['progress_percent'])?$item2['progress_percent']:'';


                            }
                        }


                    }



                }
            }

            $UserSchedule->sku_list=$sku_list;
            $UserSchedule->schedule = array_values($newSchedule);
            return ['status' => 1, 'message' => '获取成功', 'data' => $UserSchedule];
        }else{
            $boy = new \stdClass();
            $boy->contract_id=$contract_id;
            $result=[];
            foreach($Schedule as  $Schedulekey => $item)
            {
                if($item['is_need']==1||$item['is_must']==1){
                    $item->status=0;
                    $result[]=$item;
                }

            }


            $boy->sku_list=$sku_list;

            $boy->schedule=$result;

            return ['status' => 1, 'message' => '获取成功', 'data' => $boy];
        }
    }





    //编辑提交状态
    public function edit()
    {
        try {
            //print_r($this->request->all());die;
            $user_id = Auth::id();
            $status=  $this->request->input('status');
            $contract_id = $this->request->input('contract_id');
            $sku_schedules=  $this->request->input('sku_schedule');


            //统计打钩状态的个数,这个存在合同表里面，用来排序
            $count=0;

            $file_service=new FileService($this->request);
            $except_count=0;
            foreach($status as $value)
            {

                if($value['status']==1){
                    $count++;
                }
                //停滞周数，用来标记停滞记录
                if(isset($value['schedule_id']) && $value['schedule_id']==42) {

                    $repeat_record=1;
                    $except_count=1;
                }

            }
            $count=$count-$except_count;
            //上传图片
            foreach($status as &$value)
            {
                if(!isset($value['photo']))continue;
                $photo=[];
                foreach ($value['photo'] as $key => $item) {
                    $file_service->filename = 'user_schedules/' . $contract_id . '_' . $value['schedule_id'] . '_' . $key;
                    $photo[]=$file_service->postUpload64($item);
                }
                $value['photo']=$photo;
            }

            //大货部分生成完成后 存入sku列表
            if($sku_schedules){
                $sku_schedule_arr=array();
                foreach($sku_schedules as $k => $sku_schedule){
                    if(isset($sku_schedule['schedule_id']) &&$sku_schedule['schedule_id'] ==37&&isset($sku_schedule['complete_counts'])&&isset($sku_schedule['detail_counts'])&&isset($sku_schedule['sku'])){


                        $sku_schedule_arr[$k]['sku']=$sku_schedule['sku'];
                        $sku_schedule_arr[$k]['detail_counts']=$sku_schedule['detail_counts'];
                        $sku_schedule_arr[$k]['complete_counts']=$sku_schedule['complete_counts'];
                        $sku_schedule_arr[$k]['complete_time']=isset($sku_schedule['complete_time'])&&!empty($sku_schedule['complete_time'])?date('Y-m-d H:i:s',strtotime($sku_schedule['complete_time'])):date('Y-m-d H:i:s',time());
                        $sku_schedule_arr[$k]['schedule_id']=$sku_schedule['schedule_id'];
                        $sku_schedule_arr[$k]['progress_percent']=round(($sku_schedule['complete_counts']/$sku_schedule['detail_counts'])*100,2).'%';

                    }

                }



            }


            //添加合同进度
            $userSchedule = new UserSchedule();
            $userSchedule->user_id = $user_id;
            $userSchedule->status = json_encode($status);
            $userSchedule->contract_id = $contract_id;
            $userSchedule->repeat_record = isset($repeat_record)?$repeat_record:'';
            if(isset($sku_schedule_arr)&&!empty($sku_schedule_arr)){
                $userSchedule->sku_schedule = json_encode($sku_schedule_arr);
            }
            $userSchedule->save();


            $count_schedule_contract=$this->count_schedule_contract($contract_id);
            //更新合同表里面进度
            $break_update=$this->break_update($contract_id);
            $contract=Contract::find($contract_id);
            if($count_schedule_contract>0){
                $contract->progress=round(($count/$count_schedule_contract)*100,2).'%';
            }
            $contract->delay_track=0;
            $break_update?$contract->break_update=1:$contract->break_update=0;
            $contract->save();

            return ['status'=>1,'message'=>'更新成功'];
        }
        catch (\Exception $exception)
        {
            return ['status'=>0,'message'=>'更新失败'];
        }

    }

    //获得有需要的schedule统计数
    private function count_schedule_contract($contract_id){
        $contractScheduleService=new ContractScheduleService($this->request,$this->response);
        $where[]=array('id','!=','42');
        $Schedule=$contractScheduleService->getScheduleIsNeed($contract_id,$where);
        $total_count=0;
        foreach ($Schedule as $ScheduleItem) {
            if(isset($ScheduleItem['is_need'])&&$ScheduleItem['is_need']==1){
                $total_count++;
            }
            if(isset($ScheduleItem['is_must'])&&$ScheduleItem['is_must']==1){
                $total_count++;
            }
        }
        if($total_count==0){
            $total_count=count($Schedule);
        }


        return $total_count;
    }

    //更新合同表的进度
    public function update_schedule_contracts_all(){

        $data=Contract::select('id')->get();

        foreach ($data as $v) {
            $UserSchedule=UserSchedule::where('contract_id',$v['id'])->orderBy('id','desc')->first();//最新更新记录

            //计算勾选的数量
            $except_count=0;
            if($UserSchedule) {
                $count=(array)json_decode($UserSchedule->status);
                $i=0;
                foreach ($count as $c) {
                    if(isset($c->status) && $c->status==1)$i++;
//                    if($c->schedule_id==42) {
//                        $except_count=1;
//                    }
                }
                $complete_counts=$i;
            }else{
                $complete_counts=0;
            }
            $complete_counts=$complete_counts-$except_count;
            $total_count=$this->count_schedule_contract($v['id']);
            $t=$complete_counts/$total_count;
            if($t>1){
                $percent='100%';
            }else{
                $percent=round($t*100,2).'%';
            }
            Contract::where('id',$v['id'])->update(['progress'=>$percent]);

        }

    }




    //历史记录
    public function history($contract_id='')
    {
        if(!$contract_id){
            $contract_id=$this->request->input('contract_id');
        }

        $contract_info=Contract::find($contract_id);
        unset($contract_info->json_data);
        $now_to_sign_week=round((time()-strtotime($contract_info->sign_time))/3600/24/7);
        $week=[];
        for($i=-2;$i<=$now_to_sign_week+1;$i++)
        {
            if($i==0) continue;
            if(date('w',strtotime($contract_info->sign_time))==1)
            {
                $week_Monday=date('Y-m-d',strtotime("$i Monday", strtotime($contract_info->sign_time)));
                $week_Sunday=date('Y-m-d',strtotime("$i Sunday", strtotime($contract_info->sign_time)));
            }else{
                $week_Monday=date('Y-m-d',strtotime("$i Monday", strtotime($contract_info->sign_time)));
                $week_Sunday=date('Y-m-d',strtotime(($i+1)." Sunday", strtotime($contract_info->sign_time)));
            }
            $week[]=array(
                'i'=>$i,
                'id'=>'',
                'user_id'=>'',
                'schedule_id'=>'',
                'status'=>'',
                'created_at'=>'',
                'updated_at'=>'',
                'contract_id'=>$contract_id,
                'repeat_record'=>'',
                'contract'=>$contract_info,
                'quantity'=>'',
                'total_count'=>'',
                'rate'=>'',
                'to_week'=>'',
                'to_day'=>'',
                'plan_week'=>'',
                'plan_day'=>'',
                'week_Monday'=>$week_Monday,
                'week_Sunday'=>$week_Sunday,
                'sign_time'=>$contract_info->sign_time,
            );
        }
        //$UserSchedule=UserSchedule::where('contract_id',$contract_id)->groupBy(DB::raw("date_format(created_at,'%Y-%m-%d')"))->select(DB::raw('*,max(id) as id'))->get();
        //$UserSchedule=UserSchedule::with('user')->where('contract_id',$contract_id)->groupBy(DB::raw("date_format(created_at,'%Y-%m-%d')"))->select(DB::raw('user_id,max(id) as id'))->get();
        $idMax=UserSchedule::where('contract_id',$contract_id)->groupBy(DB::raw("date_format(created_at,'%Y-%m-%d')"))->select(DB::raw('max(id) as id'))->get();
        $UserSchedule=UserSchedule::with('user')->whereIn('id',$idMax)->get();
        //$total_count=Schedule::all()->count();
        $result=[];
        foreach($UserSchedule as $item)
        {
            //当前日期是下单之后的第几周
            $created_at=strtotime($item->created_at);
            $sign_time=strtotime($item->Contract->sign_time);
            $to_week=round(($created_at-$sign_time)/3600/24/7) ;
            unset($item->Contract->json_data);
            $item->contract=$item->Contract;
            $count=json_decode($item->status);
            $i=0;
            foreach ($count as $c) {
                if($c->status==1){$i++;}
            }
            $item->i=$to_week;
            $item->username=$item->User->name;
            $item->quantity=$i;
            $total_count=$this->count_schedule_contract($contract_id);
            $item->total_count=$total_count;
            $item->rate=$item->quantity/$total_count;
            $item->to_week=DifferWeek($item->Contract->sign_time,time());
            $item->to_day=DifferDay($item->Contract->sign_time,time());
            $item->plan_week=DifferWeek($item->Contract->plan_delivery_time,time());
            $item->plan_day=DifferDay($item->Contract->plan_delivery_time,time());
            // $item->week_Monday=>date('Y-m-d',strtotime("$to_week Monday", strtotime($item->Contract->sign_time)));
            // $item->week_Sunday=>date('Y-m-d',strtotime(($to_week+1)." Sunday", strtotime($item->Contract->sign_time)));
            $item->sign_time=$item->Contract->sign_time;
            $result[$to_week]=$item;
        }
        //return $week;
        // $result=($result+$week);
        // ksort($result);
        foreach($week as &$w)
        {
            foreach($result as $r)
            {
                if(strtotime($r->updated_at)>strtotime($w['week_Monday']) && strtotime($r->updated_at)<strtotime($w['week_Sunday']))
                {
                    $week_Monday=$w['week_Monday'];
                    $week_Sunday=$w['week_Sunday'];
                    $w=$r;
                    $w['week_Monday']=$week_Monday;
                    $w['week_Sunday']=$week_Sunday;
                }

            }
            if(strtotime($w['week_Monday'])>time())array_pop($week);
        }

        return ['status' => 0, 'message' => '获取成功','data'=>array_values($week)];
    }



    //查询上周更新进度情况
    public function get_update_schedule()
    {

            //$UserSchedule=UserSchedule::where('contract_id',$contract_id)->groupBy(DB::raw("date_format(created_at,'%Y-%m-%d')"))->select(DB::raw('*,max(id) as id'))->get();
            //$UserSchedule=UserSchedule::with('user')->where('contract_id',$contract_id)->groupBy(DB::raw("date_format(created_at,'%Y-%m-%d')"))->select(DB::raw('user_id,max(id) as id'))->get();
        $contract_res= Contract::where('progress','<',intval('100%'))->select('id','contract_no')->get();
        $res=array();
        if(count($contract_res)>0){

            foreach ($contract_res as $vc) {

                $idMax=UserSchedule::where('contract_id',$vc->id)->groupBy(DB::raw("date_format(created_at,'%Y-%m-%d')"))
                    ->select(DB::raw('max(id) as id'))->get();
                $week_first=$this->get_week_first_day(0);
                $week_first_next=date('Y-m-d',strtotime($week_first)+24*3600*7);
                if(isset($idMax)&&count($idMax)>0){
                    $UserSchedule=UserSchedule::with('user')->whereIn('id',$idMax)
                        ->where('updated_at','>=',$week_first)
                        ->where('updated_at','<',$week_first_next)
                        ->orderBy('created_at','desc')->get();

                    if(isset($UserSchedule)&&count($UserSchedule)>0) {

                        $res[$vc->contract_no]='';

                    }else{

                        $UserSchedule=UserSchedule::with('user')
                            ->whereIn('id',$idMax)->orderBy('created_at','desc')->get();

                        $res[$vc->contract_no]=['week_first'=>$week_first,'week_first_next'=>$week_first_next,'user_id'=>$UserSchedule[0]->user->id,'email'=>$UserSchedule[0]->user->email,'name'=>$UserSchedule[0]->user->name];

                    }
                }

            }


        }
        return $res;



    }

    //得到每周星期一日期
    private function get_week_first_day($i=0){
        //$week_end=date('Y-m-d',time()-3600*24*($w));
        $w=date('w',time());
        if($i==0){
            return $week_first=date('Y-m-d',time()-3600*24*($w)-3600*24*6);
        }
        if($i>0){
            $range_t=3600*24*7*$i;
            $week_first=$this->get_week_first_day($i-1);
            $t=strtotime($week_first)-$range_t;
            return date('Y-m-d',$t);
        }

    }






    //历史记录详情
    public function history_view()
    {
        return $this->common_view('');
    }
	

    //申请验货
    //logo_desc  logo情况     string
    //is_need_drop_test是否需要摔箱  int
    //has_strap有无打包带            int
    //is_need_sample是否需要当场寄样   int
    //estimated_loading_time预计装柜时间
    //news_or_return_product 新品/返单   news/product
    public function apply_inspection()
    {
        $contract_id = $this->request->input('contract_id');
        $content = $this->request->input('content');
        $inspection_date = $this->request->input('inspection_date');
        $file_service=new FileService($this->request);
        $replace_content_photo=[];

        $inspection_date=strtotime($inspection_date);
        if($inspection_date<strtotime(date('Y-m-d',time()))){
            return [
                'status'=>0,
                'message'=>'验货时间不能早于现在'
            ];
        }



        $data = [
            'contract_id' => $contract_id,
            'sku_num' => json_encode($replace_content_photo),
            'inspection_date'=>date('Y-m-d H:i:s',$inspection_date),
            'apply_user' => Auth::id()
        ];







        try {
            $count=ApplyInspection::where('contract_id',$contract_id)->where('is_reset',0)->where(function($query){

                $query->where('status',0)
                    ->orWhere('status',1);
            })->get()->count();

            if($count>0){return ['status' => 0, 'message' => '申请失败,还存在待处理的任务']; }
            $apply = new ApplyInspection();
            //保存基础信息
            foreach ($data as $key => $datum) {
                $apply->$key = $datum;
            }
        //上传图片，带有id命名的图片名字
            if($apply->save()) {
                $new_photo=[];
                foreach ($content as $key => &$item) {
                    foreach ($item['photo'] as $key_p => $photo) {
                        $file_service->filename = 'apply_inspection/' . $apply->id . '_' . $item['sku'] . '_' . $key_p;
                        $new_photo[]=$file_service->postUpload64($photo);
                    }

                    //获得sku数据
                    $contractService= new ContractService($this->request);
                    $sku_info= $contractService->analysis($contract_id);
                    $sku_info=$sku_info['sku_list'];
                    foreach ($sku_info as $sku_info_item) {
                        if($sku_info_item['sku']==$item['sku']){
                            $item['container_num']=$sku_info_item['container_num'];
                            $item['rate_container']=$sku_info_item['rate_container'];
                            $item['sku_chinese_name']=$sku_info_item['name'];
                        }
                    }


                }

            }

            foreach($content as $c)
            {
                unset($c['photo']);
                $replace_content_photo[]=$c;
            }
            //吧图片路径更新到数据库
            foreach($replace_content_photo as &$c)
            {
                $c['photo']=$new_photo;
            }
            $ApplyInspection=ApplyInspection::find($apply->id);
            $ApplyInspection->sku_num=json_encode($replace_content_photo);



            $ApplyInspection->save();
            return ['status' => 1, 'message' => '申请成功'];
        }catch (\Exception $e)
        {
            return ['status' => 0, 'message' => '申请失败'];
        }
    }


//    public function apply_list($where=array())
//    {
//        if(!is_array($this->status)){
//            $status=(array)$this->status;
//        }else{
//            $status=$this->status;
//        }
//
//        if($where){
//            $apply=ApplyInspection::whereIn('status',$status)->where('is_reset',0)->where($where)->get();
//        }else{
//            $apply=ApplyInspection::whereIn('status',$status)->where('is_reset',0)->where('inspection_group_id',0)->paginate(100);
//        }
//        //$apply=$apply->paginate(100);
//
//        $apply=$this->deal_apply_list($apply);
//        return ['status' => 1, 'message' => '获取成功','data'=>$apply];
//    }

    //按地址排序获得验货数据
    public function apply_list_by_address($params=array())
    {
        if(!isset($params['status'])){
            return ['status' => 0, 'message' => '获取失败'];
        }else{
            $status=$params['status'];
        }
        if(!is_array($status)){
            $status=(array)$status;
        }
        $order_by_arr=array('asc','desc');
        if(isset($params['order_by'])&&in_array($params['order_by'],$order_by_arr)){
            $order_by=$params['order_by'];
        }
        $apply=DB::table('apply_inspections')
            ->join('contracts','apply_inspections.contract_id','=','contracts.id')
            ->join('users','apply_inspections.apply_user','=','users.id')
            ->whereIn('apply_inspections.status',$status)
            ->where('apply_inspections.is_reset',0);
        if(isset($params['inspection_group_id'])){
            $apply=$apply->where('inspection_group_id',$params['inspection_group_id']);
        }
        if(isset($params['where'])&&is_array($params['where'])){
            $where=$params['where'];
            $apply=$apply->where($where)
                ->select('users.name as apply_name','apply_inspections.*','contracts.manufacturer as factory_name','contracts.manufacturer_address as factory_address','contracts.contract_no','contracts.create_user as buyer_user','contracts.factory_simple_address');



        }else{

            $apply=$apply
                ->select('users.name as apply_name','apply_inspections.*','contracts.manufacturer as factory_name','contracts.manufacturer_address as factory_address','contracts.contract_no','contracts.create_user as buyer_user','contracts.factory_simple_address');

        }

        if(isset($order_by)){
            $apply=$apply->orderBy(DB::raw("convert(factory_simple_address using gbk)"),$order_by)
                ->paginate(20);
        }else{
            $apply=$apply->orderBy('apply_inspections.id','desc')
                ->paginate(20);
        }


        $apply=$this->deal_apply_list_address($apply);
        return ['status' => 1, 'message' => '获取成功','data'=>$apply];
    }

    public function deal_apply_list_address($apply_inspection_data){
        $complete_status=array('1'=>'产品生产未完成，包装未完成','2'=>'产品生产完成，包装完成30%以下','3'=>'产品生产完成，包装完成30%-80%','4'=>'产品生产完成，包装完成80%以上');
        foreach($apply_inspection_data as $item)
        {
            $item->quantity=count(json_decode($item->sku_num));
            $item->new_quantity=0;


            $contractService= new ContractService($this->request);
            $sku_info= $contractService->analysis($item->contract_id);
            $address=$sku_info['contract_info'];
            $item->ProviceName=$address->ProviceName;
            $item->CityName=$address->CityName;
            $item->total_quantity=count($sku_info['sku_list']);
            $new_sku=[];
            foreach(json_decode($item->sku_num) as $sku)
            {
                $sku->complete=isset($complete_status[$sku->complete])?$complete_status[$sku->complete]:'';
                if($sku->isNew==1){
                    $item->new_quantity+=1;
                }
                $sku->pic=isset($sku_info['data'][$sku->sku])&&isset($sku_info['data'][$sku->sku]['sku_sys']->pic)?current($sku_info['data'][$sku->sku]['sku_sys']->pic):'';
                $new_sku[]=$sku;
            }
            $item->sku_num=$new_sku;
            $item->status_desc=$this->inspection_status[$item->status];

            //获取跟单人名称
            $idMax=UserSchedule::where('contract_id',$item->contract_id)->with(['user'=>function($query){
                $query->select('id','name');
            }])
                ->first();

            $item->schedule_name=$idMax;
            unset($item->contract);

        }

        return $apply_inspection_data;
    }









    public function deal_apply_list($apply_inspection_data){
        $complete_status=array('1'=>'产品生产未完成，包装未完成','2'=>'产品生产完成，包装完成30%以下','3'=>'产品生产完成，包装完成30%-80%','4'=>'产品生产完成，包装完成80%以上');
        foreach($apply_inspection_data as $item)
        {
            $item->apply_name=$item->user->name;
            $item->factory_name=$item->contract->manufacturer;
            $item->factory_address=$item->contract->manufacturer_address;
            $item->contract_no=$item->contract->contract_no;
            $item->quantity=count(json_decode($item->sku_num));
            $item->new_quantity=0;

            //
            $contractService= new ContractService($this->request);
            $sku_info= $contractService->analysis($item->contract_id);
            $address=$sku_info['contract_info'];
            $item->ProviceName=$address->ProviceName;
            $item->CityName=$address->CityName;
            $item->total_quantity=count($sku_info['sku_list']);
            $new_sku=[];
            foreach(json_decode($item->sku_num) as $sku)
            {
                $sku->complete=isset($complete_status[$sku->complete])?$complete_status[$sku->complete]:'';
                if($sku->isNew==1){
                    $item->new_quantity+=1;
                }
                $sku->pic=isset($sku_info['data'][$sku->sku])&&isset($sku_info['data'][$sku->sku]['sku_sys']->pic)?current($sku_info['data'][$sku->sku]['sku_sys']->pic):'';
                $new_sku[]=$sku;
            }
            $item->sku_num=$new_sku;
            $item->status_desc=$this->inspection_status[$item->status];
            unset($item->contract);

        }

        return $apply_inspection_data;
    }

    //提交质检部(提交验货)
    public function post_inspection_department()
    {
        $id=$this->request->input('id');
        if($id) {
            try {
                $applyInspection = ApplyInspection::find($id);
                if(isset($applyInspection->status)&&$applyInspection->status!=0){
                    return ['status' => 0, 'message' => '该步骤已经做过'];
                }
                $applyInspection->status = 1;
                $applyInspection->save();
                return ['status' => 1, 'message' => '操作成功'];
            }catch (\Exception $e){
                return ['status' => 0, 'message' => '操作失败'];
            }
        }
        return ['status' => 0, 'message' => '没有id'];
    }
}
