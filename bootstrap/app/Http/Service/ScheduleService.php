<?php

namespace App\Http\Service;

use App\Http\Model\Contract;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Model\User;
use App\Http\Model\UserSchedule;
use App\Http\Model\Schedule;
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
        $show_all=0;
        foreach($permission['data'] as $datum)
        {
            if($datum->user_limit='show-all-orders') $show_all=1;
        }
        if($show_all==1){
            $data=Contract::where('id','>',0);
        }else{
            $data=Contract::where('user_list','like',"%$user_name%");

        }
        $data=$data->where(function ($query){
        $query->where('status_code','03')
            ->orWhere('status_code','08');});
        $type=$this->request->input('type');
        $keywords=$this->request->input('keywords');
        $search_array=['contract_no','manufacturer','create_user'];
        $order_sign=$this->request->input('order_sign');
        $order_progress=$this->request->input('order_progress');
        //搜索
        foreach($search_array as $search_type)
        {
            if ($type==$search_type && $keywords!='') {
                $data=$data->where($search_type,'like','%'.$keywords.'%');
            }
        }
        //采购时间排序
        if($order_sign){
            if($order_sign=='asc')$data=$data->orderBy('sign_time','desc');
            if($order_sign=='desc')$data=$data->orderBy('sign_time','asc');

        }
        //进度排序
        if($order_progress){
            $data=$data->orderBy('progress',$order_progress);
        }
        $data=$data->paginate(10);
        $total_count=Schedule::all()->count();
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
            $item->total_count=$total_count;
            $item->rate=$item->quantity/$total_count;
            $item->to_week=DifferWeek($item->sign_time,time());
            $item->to_day=DifferDay($item->sign_time,time());
            $item->plan_week=DifferWeek($item->plan_delivery_time,time());
            $item->plan_day=DifferDay($item->plan_delivery_time,time());
            $item->repeat_record=$repeat_record;

            unset($item->json_data);
        }

        return ['status'=>'1','message'=>'获取成功','data'=>$data];
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
        $Schedule = Schedule::all();
        if($UserSchedule) {
            foreach ($Schedule as $data) {
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
            }
            $UserSchedule->schedule = $Schedule;
            return ['status' => 1, 'message' => '获取成功', 'data' => $UserSchedule];
        }else{
            $boy = new \stdClass();
            $boy->contract_id=$contract_id;
            $result=[];
            foreach($Schedule as $item)
            {
                $item->status=0;
                $result[]=$item;
            }
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
        //统计打钩状态的个数,这个存在合同表里面，用来排序
        $count=0;
        $file_service=new FileService($this->request);
        foreach($status as $value)
        {
            if($value['status']==1){
                $count++;
            }
            //停滞周数，用来标记停滞记录
            if(isset($value['schedule_id']) && $value['schedule_id']==36) {

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
            $userSchedule->save();
            return ['status'=>1,'message'=>'更新成功'];
        }catch (\Exception $exception)
        {
            return ['status'=>0,'message'=>'更新失败'];
        }

    }

    //历史记录
    public function history()
    {
        $contract_id=$this->request->input('contract_id');
        $contract_info=Contract::find($contract_id);
        unset($contract_info->json_data);
        $now_to_sign_week=round((time()-strtotime($contract_info->sign_time))/3600/24/7);
        //return $now_to_sign_week;
        $week=[];
        for($i=1;$i<=$now_to_sign_week;$i++)
        {
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
                'week_Monday'=>date('Y-m-d',strtotime("$i Monday", strtotime($contract_info->sign_time))),
                'week_Sunday'=>date('Y-m-d',strtotime(($i+1)." Sunday", strtotime($contract_info->sign_time))),
                'sign_time'=>$contract_info->sign_time,
            );

        }
        $UserSchedule=UserSchedule::where('contract_id',$contract_id)->groupBy(DB::raw("date_format(created_at,'%Y-%m-%d')"))->select(DB::raw('*,max(id) as id'))->get();
        $total_count=Schedule::all()->count();
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
            $item->quantity=$i;
            $item->total_count=$total_count;
            $item->rate=$item->quantity/$total_count;
            $item->to_week=DifferWeek($item->Contract->sign_time,time());
            $item->to_day=DifferDay($item->Contract->sign_time,time());
            $item->plan_week=DifferWeek($item->Contract->plan_delivery_time,time());
            $item->plan_day=DifferDay($item->Contract->plan_delivery_time,time());
            $item->week_Monday=date('Y-m-d',strtotime(($to_week+1)." Monday", strtotime($item->Contract->sign_time)));
            $item->week_Sunday=date('Y-m-d',strtotime(($to_week+2)." Sunday", strtotime($item->Contract->sign_time)));
            $item->sign_time=$item->Contract->sign_time;
            $result[$to_week]=$item;

        }
        $result=($result+$week);
        ksort($result);


         return ['status' => 0, 'message' => '获取成功','data'=>array_values($result)];
    }
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
    public function delay_track()
    {
        $contract_id=$this->request->input('contract_id');
        $contract=Contract::find($contract_id);
        $contract->delay_track=1;
        $contract->save();
        return ['status' => 1, 'message' => '操作成功'];
    }
}
