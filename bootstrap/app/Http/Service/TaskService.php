<?php

namespace App\Http\Service;

use App\Http\Model\ContractStandard;
use App\Http\Model\User;
use Illuminate\Http\Request;
use App\Http\Model\Contract;
use App\Http\Model\Standard;
use App\Http\Model\UserTask;
use App\Http\Model\Task;
use App\Http\Model\InspectionRecord;
use App\Http\Model\InspectionRecordInfo;
use App\Http\Model\InspectionAccessoryRecord;
use App\Http\Model\InspectionOtherRecord;
use App\Http\Model\ContractGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskService
{
    function __construct(Request $request)
    {
        $this->request=$request;
    }
//添加任务
    public function add_task()
    {
        //$request={"user_id":24,"data":[{"factory_name":"\u5b81\u6ce2\u529b\u98de\u5065\u5eb7\u79d1\u6280\u6709\u9650\u516c\u53f8","contract_no":{"YG-201812170837":{"TY91Y0235":200,"TY91Y02351":200},"YG-201901280070":{"SY18P0184":200,"SY18P01841":200}},"inspection_date":"2018-12-12"},{"factory_name":"\u5b81\u6ce2\u529b\u98de\u5065\u5eb7\u79d1\u6280\u6709\u9650\u516c\u53f8","contract_no":{"YG-201812170837":{"TY91Y0235":200,"TY91Y02351":200},"YG-201901280070":{"SY18P0184":200,"SY18P01841":200}},"inspection_date":"2018-12-12"}]}
        $request=$this->request->all();
        try {
            //任务表
            DB::beginTransaction();//开启事务
            $Task = new Task();
            $Task->task_no = 'task' . date('YmdHis');
            $Task->user_id = $request['user_id'];
            $Task->save();
            //任务关联表
            foreach ($request['data'] as $key => $contract_info) {
                foreach ($contract_info['contract_no']  as $contract_sku) {
                    $contract_no=$contract_sku['no'];
                    $contract = Contract::where('contract_no', $contract_no)->first();//检验合同是否存在
                    if (!$contract) return ['status' => '0', 'message' => '合同编号:' . $contract_no . '不存在'];
                    $userTask = new UserTask();
                    $userTask->user_id = $request['user_id'];
                    $userTask->contract_id = $contract->id;
                    $userTask->count = json_encode($contract_sku['sku']);
                    $userTask->inspection_date = $contract_sku['inspection_date'];
                    $userTask->factory_name = $contract_info['factory_name'];
                    $userTask->factory_group = $key+1;
                    $userTask->task_id = $Task->id;
                    $userTask->save();
                }

            }
            DB::commit();//成功，提交事务
            return ['status'=>'1','message'=>'分配成功'];
        }catch (\Exception $e)
        {
            DB::rollBack();//失败，回滚事务
            return ['status'=>'0','message'=>'分配失败'];
        }

    }
    //获取所有用户
    public function getTaskUser()
    {
        return User::all();
    }

    //删除任务
    public function delete_task()
    {
        //?id=8
        $id=$this->request->input('id');

        $userTask=  UserTask::where('task_id',$id)->delete();
        $Task=  Task::find($id)->delete();
        if($userTask && $Task){
            return ['status'=>'1','message'=>'删除成功'];
        }else{
            return ['status'=>'0','message'=>'删除失败'];
        }
    }
    //获取指定用户的任务
    public function user_task()
    {
        $user=Auth::user();
        //$userModel=User::find($user->id);
        $TaskModel=Task::where('user_id',$user->id)->paginate(15);
        $result=[];
        foreach($TaskModel as $task)
        {
            $factory=[];
            foreach($task->userTask as $userTask)
            {

                $factory[$userTask->factory_group -1]= $userTask;
            }
            $task->factory=$factory;
            $result[]= $task;
        }

        return $result;

    }

    //获取全部用户的任务
    public function all_task()
    {
        $TaskModel=Task::orderBy('id','asc')->paginate(15);
        $result=[];
        foreach($TaskModel as $task)
        {
            $factory=[];

            foreach($task->userTask as $userTask)
            {

                $factory[$userTask->factory_group -1]= $userTask;
            }
            $task->user_name=$task->user->name;
            $task->factory=$factory;
            $result[]= $task;
        }
        
        return $result;

    }
    //登录用户任务列表
    public function list_task()
    {
        $user=Auth::user();
        if($user->level==1){
            return ['status'=>1,'message'=>'获取成功','data'=>$this->all_task()];
        }else{
            return ['status'=>1,'message'=>'获取成功','data'=>$this->user_task()];
        }

    }
    //获取任务下工厂信息
    public function user_task_factory()
    {

        $task_id=$this->request->input('task_id');

        $userTaskModel=UserTask::where('task_id',$task_id)->groupBy('factory_group')->select('factory_name','task_id','user_id')->paginate(15);

        return $userTaskModel;

    }
    //获取工厂下的po
    public function task_factory_contract()
    {

        $task_id=$this->request->input('task_id');
        $factory_group=$this->request->input('factory_group');
        $userTaskModel=UserTask::where('task_id',$task_id)->where('factory_group',$factory_group)->get();
        $data=[];
        foreach($userTaskModel as $item)
        {
            $item->task_no=$item->Task->task_no;
            $item->contract_no=$item->Contract->contract_no;
            $data[]=$item;
        }

        return $data;

    }
    //获取po下的sku列表
    public function task_sku_list()
    {
        $task_id=$this->request->input('task_id');
        $contract_id=$this->request->input('contract_id');
        $user_task=UserTask::where('task_id',$task_id)->where('contract_id',$contract_id)->first();
        $count=json_decode($user_task->count);//需要验证的sku
        //print_r($count);die;
        $data= $this->view_task($contract_id);
        //一个合同下面可能只需要验证部分sku,筛选出需要验证的sku
        $result=[];
        //print_r($data['sku_list']);die;
        $ini=[];
        foreach($count as $item){
            $ini[$item->name]=$item->num;
        }
        foreach($data['sku_list'] as $key=>$value)
        {
            if(isset($ini[$key])){
                $value['count']=$ini[$key];
                $result[$key]=$value;
            }
        }

        return ['status'=>1,'message'=>'获取成功','data'=>$result,'task_id'=>$task_id,'contract_id'=>$contract_id];
    }
    //展示sku的标准详细信息
    public function task_sku_view()
    {
        $sku=$this->request->input('sku');
        $task_id=$this->request->input('task_id');
        $contract_id=$this->request->input('contract_id');
        $data= $this->view_task($contract_id);
        return ['status'=>1,'message'=>'获取成功','data'=>$data['data'][$sku],'task_id'=>$task_id,'contract_id'=>$contract_id];
    }
    //解析po下的sku数据
    //return 以sku下标的数组
    public function view_task($id)
    {
        $contract_id=$id;
        //$Standard=Standard::where('contract_id',$contract_id)->get();
        $contract=Contract::where('id',$contract_id)->first();
        $json_data=json_decode($contract->json_data);
        //print_r($json_data);die;
        $Standard=$json_data->SkuInfos;
        $sku_standard=[];//sku详细数据
        $sku_list=[];//sku列表
        //print_r($Standard);die;
        foreach((array)$Standard as $item)
        {

            //print_r($item->description);die;
            $description=$item;
            //print_r($description->Data);die;
            if($description->InfoData){
                $sku_sys=$description->InfoData;
                foreach($sku_sys->pictures as $pic)
                {
                    $sku_sys->pic[]=$pic->PictureAddress;
                }

                unset($sku_sys->pictures);
                $description->InfoData->contract_id=$contract_id;//添加合同id
                $sku_standard[$sku_sys->ProductCode]['sku_sys']=$description->InfoData;
                $sku_list[$sku_sys->ProductCode]['name']=$sku_sys->ChineseName;
                $sku_list[$sku_sys->ProductCode]['pic']=current($sku_sys->pic);

            }
            if(!empty($description->accessory)) {
                foreach($description->accessory as $accessory)
                {
                    $sku_standard[$accessory->ProductCode]['sku_acc'] = $description->accessory;
                }

            }
            if($description->Data) {
                $sku_other=$description->Data;
                $sku_other=$sku_other[0];
                foreach($sku_other as $other)
                {
                    if($other->IsApplicable=='02')continue;
                    $boy = new \stdClass();
                    $boy->InspectionRequiremen=$other->InspectionRequiremen;
                    $boy->IsNeedPic=$other->IsNeedPic;
                    foreach($other->files as $pic){
                        $boy->pic[]=$pic->InstructionAddress;
                    }

                    $sku_standard[$other->InspectionRequiremenCode]['sku_other'][] = $boy;
                }

            }

        }
        return  ['data'=>$sku_standard,'sku_list'=>$sku_list];
    }



    //提sku交质检数据
    public function inspection_result()
    {
        $user_id=Auth::id();
        $result=$this->request->all();
        //print_r($result);die;
        $sku=$result['sku'];
        $task_id=$result['task_id'];
        $contract_id=$result['contract_id'];
        DB::beginTransaction();//开启事务
        try {
            $inspection_record = new InspectionRecord();
            $inspection_record->task_id = $task_id;
            $inspection_record->save();
            foreach ($result['data'] as $key => $data) {
                if ($key == 'sku_sys') {
                    $SinglePackingSize=array($data['SinglePackingSizeLength'],$data['SinglePackingSizeWidth'],$data['SinglePackingSizeHight']);
                    sort($SinglePackingSize);
                    $PackingSize=array($data['PackingSizeLength'],$data['PackingSizeWidth'],$data['PackingSizeHight']);
                    sort($PackingSize);
                    $inspection_record_info = new InspectionRecordInfo();
                    $inspection_record_info->task_id = $task_id;
                    $inspection_record_info->sku = $sku;
                    $inspection_record_info->contract_id = $contract_id;
                    $inspection_record_info->rate_container = $data['RateContainer'];
                    $inspection_record_info->bar_code = $data['BarCode'];;
                    $inspection_record_info->outside_bar_code = $data['OutsideBarCode'];;
                    $inspection_record_info->net_weight = $data['NetWeight'];;
                    $inspection_record_info->rough_weight = $data['RoughWeight'];;
                    if($data['PackingSizeLength']=='' && $data['PackingSizeWidth']=='' && $data['PackingSizeHight']=='')
                    {

                        $inspection_record_info->packing_size = json_encode($SinglePackingSize);

                    }else{
                        $inspection_record_info->packing_size = json_encode($PackingSize);
                    }
                    $inspection_record_info->single_packing_size = json_encode($SinglePackingSize);



                    //$inspection_record_info->pic = $data->rate_container;
                    //$inspection_record_info->upload_pic = $data->rate_container;
                    $inspection_record_info->user_id=$user_id;
                    $inspection_record_info->save();


                }
                if ($key == 'sku_other') {
                    foreach ($data as $key_other => $sku_other) {
                        $inspection_record_info = new InspectionOtherRecord();
                        $inspection_record_info->task_id = $task_id;
                        $inspection_record_info->sku = $sku;
                        $inspection_record_info->contract_id = $contract_id;
                        $inspection_record_info->description = isset($sku_other['description'])?$sku_other['description']:'';
                        $inspection_record_info->is_standard = $sku_other['is_standard'];
                        $inspection_record_info->remark = $sku_other['remark'];;
                        //$inspection_record_info->pic = $sku_other->pic;;
                        //$inspection_record_info->upload_pic = $sku_other->upload_pic;;
                        $inspection_record_info->user_id=$user_id;
                        $inspection_record_info->save();
                    }
                }
                DB::commit();//成功，提交事务

            }
        return ['status' => 1, 'message' => '操作成功'];
        }catch (\Exception $e) {
            DB::rollBack();//失败，回滚事务
            return ['status'=>0,'message'=>'操作失败'];
        }


    }
    //展示配件表单
    public function task_acc_view()
    {
        $task_id=$this->request->input('task_id');
        $contract_id=$this->request->input('contract_id');

        $user_task=UserTask::where('task_id',$task_id)->where('contract_id',$contract_id)->get();

        $result=[];
        foreach ($user_task as $item)
        {
            $data= $this->view_task($item->contract_id);
            foreach($data['data'] as $key=>$value)
            {
                //print_r($value['sku_acc']);die;
                if(isset($value['sku_acc']))
                {
                    $result[]=array('task_id'=>$task_id,'contract_id'=>$item->contract_id,'data'=>$value['sku_acc']);
                }


            }
        }
         return $result;
    }
    //提交配件质检数据
    public function inspection_acc_result()
    {
        $user_id=Auth::id();
        try{
            $result=$this->request->all();
            //print_r($result);die;
            DB::beginTransaction();//开启事务
            foreach($result as $data) {
                foreach($data['data'] as $acc) {
                    //print_r($acc);die;
                    $inspection_record_info = new InspectionAccessoryRecord();
                    $inspection_record_info->task_id = $data['task_id'];
                    $inspection_record_info->accessory_sku = $acc['AccessoryCode'];
                    $inspection_record_info->sku = $acc['ProductCode'];
                    $inspection_record_info->contract_id = $data['contract_id'];
                    $inspection_record_info->bar_code = $acc['BarCode'];;
                    $inspection_record_info->packing = $acc['PackingType'];
                    $inspection_record_info->user_id = $user_id;
                    //$inspection_record_info->pic = $sku_acc->pic;;
                    // $inspection_record_info->upload_pic = $sku_acc->upload_pic;;
                    $inspection_record_info->save();
                }

            }
            DB::commit();//成功，提交事务
            return ['status' => 1, 'message' => '操作成功'];
        }catch (\Exception $e) {
            DB::rollBack();//失败，回滚事务
            return ['status'=>0,'message'=>'操作失败'];
        }
    }
    //检验结果任务列表
    public function inspection_result_task_list()
    {
        $result=[];
        $list=InspectionRecordInfo::orderBy('id','desc')->groupBy('task_id')->paginate(15);
        foreach($list as $item)
        {
            $item->user_name=$item->user->name;
            $item->task_no=$item->Task->task_no;
            $result[]=$item;
        }
        return ['status'=>1,'message'=>'展示成功','data'=>$result];
    }
    //检验结果合同列表
    public function inspection_result_contract_list()
    {
        $task_id=$this->request->input('task_id');
        $result=[];
        $list=InspectionRecordInfo::where('task_id',$task_id)->get();
        foreach($list as $item)
        {
            $item->task_no=$item->Task->task_no;
            $item->contract_no=$item->Contract->contract_no;
            $result[]=$item;
        }
        return $result;
    }

    //获取合同下的sku
    public function inspection_result_contract_sku_list()
    {
        $task_id=$this->request->input('task_id');
        $contract_id=$this->request->input('contract_id');
        $result=[];
        $list=InspectionRecordInfo::where('task_id',$task_id)->where('contract_id',$contract_id)->get();
        $info=$this->view_task($contract_id);
        foreach($list as $item)
        {
            $item->name=$info['data'][$item->sku]['sku_sys']->ChineseName;
            $item->pic=current($info['data'][$item->sku]['sku_sys']->pic);
            $result[]=$item;
        }
        //print_r($result);die;
        return ['status'=>1,'message'=>'获取成功','data'=>$result,'task_id'=>$task_id,'contract_id'=>$contract_id];
    }
    //检验结果po下的sku数据
    public function inspection_result_sku_view()
    {
        //http://test.laravel55.cc/api/v1/task/inspection-result-sku-view?contract_id=29&sku=TY91Y0235
        $result=[];
        $task_id=$this->request->input('task_id');
       $contract_id=$this->request->input('contract_id');
       $sku=$this->request->input('sku');
        $org=$this->sku_org_view();

       $InspectionRecordInfo=InspectionRecordInfo::where('contract_id',$contract_id)->where('task_id',$task_id)->where('sku',$sku)->get();
       $InspectionAccessoryRecord=InspectionAccessoryRecord::where('contract_id',$contract_id)->where('task_id',$task_id)->where('sku',$sku)->get();
       $InspectionOtherRecord=InspectionOtherRecord::where('contract_id',$contract_id)->where('task_id',$task_id)->where('sku',$sku)->get();
       $pic_sys_type=['RoughWeight','lengthWidthHeight','NetWeight','OutsideBarCode','PackinglengthWidthHeight','barCode'];
       $pic_acc_type=['BarCode','PackingType'];
       $sys_array= new \stdClass();
       foreach($InspectionRecordInfo as $item)
       {

           $packing=json_decode($item->packing_size);
               $item->PackingSizeLength=array('org'=>$org['sku_sys']->PackingSizeLength,'new'=>$packing[0],'pic'=>$this->pic_same($item->task_id . '/' . $item->contract_id . '/' . $item->sku . '/PackingSizeLength'));
               $item->PackingSizeWidth=array('org'=>$org['sku_sys']->PackingSizeWidth,'new'=>$packing[1],'pic'=>$this->pic_same($item->task_id . '/' . $item->contract_id . '/' . $item->sku . '/PackingSizeWidth'));
               $item->PackingSizeHight=array('org'=>$org['sku_sys']->PackingSizeHight,'new'=>$packing[2],'pic'=>$this->pic_same($item->task_id . '/' . $item->contract_id . '/' . $item->sku . '/PackingSizeHight'));


           $packing=json_decode($item->single_packing_size);
               $item->SinglePackingSizeLength=array('org'=>$org['sku_sys']->SinglePackingSizeLength,'new'=>$packing[0],'pic'=>$this->pic_same($item->task_id . '/' . $item->contract_id . '/' . $item->sku . '/SinglePackingSizeLength'));
               $item->SinglePackingSizeWidth=array('org'=>$org['sku_sys']->SinglePackingSizeWidth,'new'=>$packing[1],'pic'=>$this->pic_same($item->task_id . '/' . $item->contract_id . '/' . $item->sku . '/SinglePackingSizeWidth'));
               $item->SinglePackingSizeHight=array('org'=>$org['sku_sys']->SinglePackingSizeHight,'new'=>$packing[2],'pic'=>$this->pic_same($item->task_id . '/' . $item->contract_id . '/' . $item->sku . '/SinglePackingSizeHight'));

           $item->pic=isset($org['sku_sys']->pictures)?$org['sku_sys']->pictures:[];
           $item->ProductCode=$item->sku;
           $item->RateContainer=$item->rate_container;
           $item->BarCode=array('org'=>$org['sku_sys']->BarCode,'new'=>$item->bar_code,'pic'=>$this->pic_same($item->task_id . '/' . $item->contract_id . '/' . $item->sku . '/BarCode'));
           $item->OutsideBarCode=array('org'=>$org['sku_sys']->BarCode,'new'=>$item->outside_bar_code,'pic'=>$this->pic_same($item->task_id . '/' . $item->contract_id . '/' . $item->sku . '/OutsideBarCode'));
           $item->RoughWeight=array('org'=>$org['sku_sys']->BarCode,'new'=>$item->rough_weight,'pic'=>$this->pic_same($item->task_id . '/' . $item->contract_id . '/' . $item->sku . '/RoughWeight'));
           $item->NetWeight=array('org'=>$org['sku_sys']->BarCode,'new'=>$item->net_weight,'pic'=>$this->pic_same($item->task_id . '/' . $item->contract_id . '/' . $item->sku . '/NetWeight'));
           $item->RateContainer=$org['sku_sys']->RateContainer;
           $sys_array=$item;

       }
        //sku acc数据
        $acc_array=[];
        foreach($InspectionAccessoryRecord as $key=>$acc_value)
        {

            foreach ($pic_acc_type as $type){

                $acc_value->pic= array('org'=>(isset($org['sku_acc'][$key]->files))?$org['sku_acc'][$key]->files:[],'new'=>$this->pic_same($acc_value->task_id . '/' . $acc_value->contract_id . '/' . $acc_value->sku . '/' . $acc_value->accessory_sku . '/' . $type));
            }
            $acc_value->BarCode=array('org'=>$org['sku_acc'][$key]->BarCode,'new'=>$acc_value->bar_code);
            $acc_value->PackingType=array('org'=>$org['sku_acc'][$key]->PackingType,'new'=>$acc_value->packing);
            $acc_array[]=$acc_value;

        }
        //sku other数据
        $other_array=[];
        foreach($InspectionOtherRecord as $key=>$other_value)
        {

            $other_value->pic= array('org'=>(isset($org['sku_other'][$key]->files))?$org['sku_other'][$key]->files:[],'new'=>$this->pic_same($other_value->task_id . '/' . $other_value->contract_id . '/' . $other_value->sku . '/other'.($key+1)));
            $other_value->InspectionRequiremen=array('org'=>$org['sku_other'][$key]->InspectionRequiremen,'new'=>$other_value->remark);
            $other_array[]=$other_value;

        }
        $result['sku_sys']=$sys_array;
        $result['sku_acc']=$acc_array;
        $result['sku_other']=$other_array;


        return $result;

    }
    //合同下的sku，原始数据
    public function sku_org_view()
    {
        $contract_id=$this->request->input('contract_id');
        $sku=$this->request->input('sku');
        $org_info=$this->view_task($contract_id);
        return $org_info['data'][$sku];
    }
    //sku验货结果数据对比
    public function sku_view()
    {
        return ['status'=>1,'message'=>'获取成功',
            'data'=>$this->inspection_result_sku_view()];
    }
    //获取对应图片
    public function pic_same($dir)
    {

        $files = [];
        $real_dir=storage_path('app/public/').$dir;
        if (is_dir($real_dir)) {

            if (false != ($handle = opendir($real_dir))) {

                while (false !== ($file = readdir($handle))) {
                    if ($file != '.' && $file != '..') {
                        $files[] = '/storage/'.$dir.'/'.$file;
                    }

                }
            }
        }
        return $files;
    }
    //生成任务
    public function create_task()
    {
        $request=$this->request->all();
        try {
            //任务主表
            DB::beginTransaction();//开启事务
            $Task = new Task();
            $Task->task_no = 'task' . date('YmdHis');
            $Task->save();
            //任务关联表
            foreach ($request['data'] as $apply_inspection_id) {
                    $userTask = new ContractGroup();
                    $userTask->apply_inspection_id = $apply_inspection_id;
                    $userTask->task_id = $Task->id;
                    $userTask->save();
            }
            DB::commit();//成功，提交事务
            return ['status'=>'1','message'=>'分组成功'];
        }catch (\Exception $e)
        {
            DB::rollBack();//失败，回滚事务
            return ['status'=>'0','message'=>'分组失败'];
        }

    }



}
