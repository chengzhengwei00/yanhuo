<?php

namespace App\Http\Service;

use App\Http\Model\Contract;
use App\Http\Model\ContractSchedule;
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

class ScheduleService
{
    protected $status;

    public function __construct(Request $request,Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }




    public function contract_list()
    {

        $current_user=Auth::user();
        $user_name=$current_user->name;
        $user_permission=new PermissionsService($this->request);
        $permission=$user_permission->show_user_role_permission_by_id($current_user->id);
		//return $permission;
        $show_all=0;
        foreach($permission['data'] as $datum)
        {
			//return $datum->user_limit; 
            if($datum->user_limit=='show-all-orders') $show_all=1;
        }
        if($show_all==1){
            $data=Contract::where('id','>',0);
        }else{
            $data=Contract::where('user_list','like',"%$user_name%");

        }
        $data=$data->where(function ($query){
        $query->where('status_code','03')
            ->orWhere('status_code','08');});

        $is_delay_order=$this->request->get('is_delay_order');
        $is_out_shedule=$this->request->get('is_out_shedule');
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

        //搜索
        $data=$this->contract_search($data);
        //排序
        $data=$this->contract_sort($data);


        $data=$data->paginate(10);
        //$total_count=Schedule::all()->count();
        foreach ($data as &$item)
        {
            $repeat_record=$this->repeat_record($item->id);
            $contractService= new ContractService($this->request);
            $sku_list= $contractService->sku_list($item->id);
            $item->sku_list=$sku_list;
            $UserSchedule=UserSchedule::where('contract_id',$item->id)->orderBy('id','desc')->first();//最新更新记录
            //计算勾选的数量
            if($UserSchedule) {
                $count=(array)json_decode($UserSchedule->status);
                $i=0;
                foreach ($count as $c) {
                    if(isset($c->status) && $c->status==1)$i++;
                }
                $item->quantity=$i;
            }else{
                $item->quantity=0;
            }

            $contractScheduleService=new ContractScheduleService($this->request,$this->response);
            $Schedule=$contractScheduleService->getScheduleIsNeed($item->id);
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

            $contractScheduleList=$contractScheduleService->getSchedulesByContract($item->id);
            $mustSelectStatus=count($contractScheduleList['data']);
            $item->must_select_status=$mustSelectStatus?1:0;
            $item->total_count=$total_count;
            $item->rate=$item->quantity/$total_count;
            $item->to_week=DifferWeek($item->sign_time,time());
            $item->to_day=DifferDay($item->sign_time,time());
            $item->plan_week=DifferWeek($item->plan_delivery_time,time());
            $item->plan_day=DifferDay($item->plan_delivery_time,time());
            $item->repeat_record=$repeat_record;
			$item->break_update=$this->break_update($item->id);

            unset($item->json_data);
        }
        return ['status'=>'1','message'=>'获取成功','data'=>$data];
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
            $data=$data->orderBy('progress',$order_progress);
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

        $i=0;//计数——查找有相同记录的数据
        /*
        $UserSchedule=UserSchedule::where('contract_id',$contract_id)->get();
        $array=[];
        foreach($UserSchedule as $item)
        {
            $array[]=$item->status;
        }

        foreach(array_count_values($array) as $value)
        {
            if($value>1)$i=$value+$i-1;
        }
        */
        $UserSchedule=UserSchedule::where('contract_id',$contract_id)->where('repeat_record','1')->get();
        $i=$UserSchedule->count();
        return $i;
    }
    //状态列表
    public function list()
    {
        return ['status'=>1,'message'=>'获取成功','data'=>Schedule::all()];
    }

    //获得不是必须的状态列表
    public function listIsMust()
    {
        return ['status'=>1,'message'=>'获取成功','data'=>Schedule::where('is_must',1)->get()];
    }

    //获得不是必须的状态列表
    public function listIsSelect()
    {
        return ['status'=>1,'message'=>'获取成功','data'=>Schedule::where('is_must',0)->get()];
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
        if(empty($contract_id))
        {
            $UserSchedule=UserSchedule::find($id);
            $contract_id=$UserSchedule->contract_id;
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

        //$Schedule = Schedule::all();

//        $scheduleService=new ScheduleService($this->request,$this->response);
//        $scheduleListRes=$scheduleService->list();
        $contractSchedule=new ContractScheduleService($this->request,$this->response);
        $contractScheduleList=$contractSchedule->getSchedulesByContract($contract_id);
        //return $contractScheduleList['data'];
        if(isset($contractScheduleList['data'])&&count($contractScheduleList['data'])==0){
            return ['status' => 1, 'message' => '请选择排除项','data'=>array('schedule'=>[],'sku_list'=>[])];
        }

        $contractScheduleService=new ContractScheduleService($this->request,$this->response);
        $Schedule=$contractScheduleService->getScheduleIsNeed($contract_id);


//        $scheduleService=new ScheduleService($this->request,$this->response);
//        $scheduleListRes=$scheduleService->listIsMust();
        //return $Schedule;



        //$scheduleList=$scheduleListRes['data'];
        //$Schedule=array_merge($scheduleList,$Schedule);

        //return $Schedule;
        $newSchedule=array();
        if($UserSchedule) {
            foreach ($Schedule as $Schedulekey=> $data) {

                    $status_array = (array)json_decode($UserSchedule->status);
                    $data->status=0;
                    //if($data['is_need']==1||$data['is_must']==1){
                        foreach($status_array as $item){
                            if (isset($item->schedule_id) && $item->schedule_id==$data->id) {
                                $data->status = $item->status;
                                $data->change_time = $item->change_time;
                                $data->update_time=isset($new_array1[$item->schedule_id])?$created_at[$item->schedule_id]:'';
                                if($data['is_need']==1||$data['is_must']==1) {

                                    $data->is_need = $item->is_need;
                                }
                                $data->show_photo = isset($photo_array[$item->schedule_id])?$photo_array[$item->schedule_id]:[];
                            }
                        }

                    //}
                $newSchedule[$Schedulekey]=$data;

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

        foreach($status as $value)
        {

            if($value['status']==1&& $value['schedule_id']!=42){
                $count++;
            }
            //停滞周数，用来标记停滞记录
            if(isset($value['schedule_id']) && $value['schedule_id']==42) {

                $repeat_record=1;
            }

        }


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

        //更新合同表里面进度
            $contract=Contract::find($contract_id);
            $contract->progress=$count;
            $contract->delay_track=null;
            $contract->save();
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
            return ['status'=>1,'message'=>'更新成功'];
        }catch (\Exception $exception)
        {
            return ['status'=>0,'message'=>'更新失败'];
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
        for($i=1;$i<=$now_to_sign_week+1;$i++)
        {
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
            $contractScheduleService=new ContractScheduleService($this->request,$this->response);
            $Schedule=$contractScheduleService->getScheduleIsNeed($contract_id);
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



   //历史记录
//    public function history_comm($contract_id)
//    {
//        $contract_info=Contract::find($contract_id);
//        if(!$contract_info){
//            return ['status' => 0, 'message' => '没有数据','data'=>[]];
//        }
//        unset($contract_info->json_data);
//        $now_to_sign_week=round((time()-strtotime($contract_info->sign_time))/3600/24/7);
//        //return $now_to_sign_week;
//        $week=[];
//        for($i=1;$i<=$now_to_sign_week;$i++)
//        {
//            $week[]=array(
//                'i'=>$i,
//                'id'=>'',
//                'user_id'=>'',
//                'schedule_id'=>'',
//                'status'=>'',
//                'created_at'=>'',
//                'updated_at'=>'',
//                'contract_id'=>$contract_id,
//                'repeat_record'=>'',
//                'contract'=>$contract_info,
//                'quantity'=>'',
//                'total_count'=>'',
//                'rate'=>'',
//                'to_week'=>'',
//                'to_day'=>'',
//                'plan_week'=>'',
//                'plan_day'=>'',
//                'week_Monday'=>date('Y-m-d',strtotime("$i Monday", strtotime($contract_info->sign_time))),
//                'week_Sunday'=>date('Y-m-d',strtotime(($i+1)." Sunday", strtotime($contract_info->sign_time))),
//                'sign_time'=>$contract_info->sign_time,
//            );
//
//        }
//        $UserSchedule=UserSchedule::where('contract_id',$contract_id)->groupBy(DB::raw("date_format(created_at,'%Y-%m-%d')"))->select(DB::raw('*,max(id) as id'))->get();
//        $total_count=Schedule::all()->count();
//        $result=[];
//        foreach($UserSchedule as $item)
//        {
//            //当前日期是下单之后的第几周
//            $created_at=strtotime($item->created_at);
//            $sign_time=strtotime($item->Contract->sign_time);
//            $to_week=round(($created_at-$sign_time)/3600/24/7) ;
//
//            unset($item->Contract->json_data);
//            $item->contract=$item->Contract;
//
//            $count=json_decode($item->status);
//            $i=0;
//            foreach ($count as $c) {
//                if($c->status==1){$i++;}
//            }
//            $item->i=$to_week;
//            $item->quantity=$i;
//            $item->total_count=$total_count;
//            $item->rate=$item->quantity/$total_count;
//            $item->to_week=DifferWeek($item->Contract->sign_time,time());
//            $item->to_day=DifferDay($item->Contract->sign_time,time());
//            $item->plan_week=DifferWeek($item->Contract->plan_delivery_time,time());
//            $item->plan_day=DifferDay($item->Contract->plan_delivery_time,time());
//            $item->week_Monday=date('Y-m-d',strtotime(($to_week+1)." Monday", strtotime($item->Contract->sign_time)));
//            $item->week_Sunday=date('Y-m-d',strtotime(($to_week+2)." Sunday", strtotime($item->Contract->sign_time)));
//            $item->sign_time=$item->Contract->sign_time;
//            $result[$to_week]=$item;
//
//        }
//        $result=($result+$week);
//        ksort($result);
//
//
//         return ['status' => 1, 'message' => '获取成功','data'=>array_values($result)];
//    }
    //历史记录
//    public function history()
//    {
//        $contract_id=$this->request->input('contract_id');
//        return $this->history_comm($contract_id);
//    }
    //历史记录详情
    public function history_view()
    {
        return $this->common_view('');
    }
	

    //申请验货
    public function apply_inspection()
    {
        $contract_id = $this->request->input('contract_id');
        $content = $this->request->input('content');
        $inspection_date = $this->request->input('inspection_date');
        $file_service=new FileService($this->request);
        $replace_content_photo=[];
        foreach($content as $c)
        {
            unset($c['photo']);
            $replace_content_photo[]=$c;
        }
        $data = [
            'contract_id' => $contract_id,
            'sku_num' => json_encode($replace_content_photo),
            'inspection_date'=>$inspection_date,
            'apply_user' => Auth::id()
        ];
        try {
            $count=ApplyInspection::where('contract_id',$contract_id) ->where(function($query){

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
                foreach ($content as $key => $item) {
                    foreach ($item['photo'] as $key_p => $photo) {
                        $file_service->filename = 'apply_inspection/' . $apply->id . '_' . $item['sku'] . '_' . $key_p;
                        $new_photo[]=$file_service->postUpload64($photo);
                    }
                }
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
    public function apply_list()
    {
        $complete_status=array('1'=>'产品生产未完成，包装未完成','2'=>'产品生产完成，包装完成30%以下','3'=>'产品生产完成，包装完成30%-80%','4'=>'产品生产完成，包装完成80%以上');
        $apply=ApplyInspection::where('status',$this->status)->paginate(100);
        foreach($apply as $item)
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
                $sku->pic=isset($sku_info['data'][$sku->sku])?current($sku_info['data'][$sku->sku]['sku_sys']->pic):'';
                $new_sku[]=$sku;
            }
            $item->sku_num=$new_sku;
            unset($item->contract);

        }
        return ['status' => 1, 'message' => '获取成功','data'=>$apply];
    }
    //申请验货列表
    public function apply_inspection_list()
    {
        $this->status=0;
        return $this->apply_list();
    }
    //提交质检部
    public function post_inspection_department()
    {
        $id=$this->request->input('id');
        if($id) {
            try {
                $applyInspection = ApplyInspection::find($id);
                $applyInspection->status = 1;
                $applyInspection->save();
                return ['status' => 1, 'message' => '操作成功'];
            }catch (\Exception $e){
                return ['status' => 1, 'message' => '操作失败'];
            }
        }
        return ['status' => 0, 'message' => '没有id'];
    }
    //验货列表
    public function apply_department_list()
    {

        $this->status=1;
        return $this->apply_list();
    }
    //延迟跟踪
//    public function delay_track()
//    {
//        $contract_id=$this->request->input('contract_id');
//        $contract=Contract::find($contract_id);
//        $contract->delay_track=1;
//        $contract->save();
//        return ['status' => 1, 'message' => '操作成功'];
//    }
}
