<?php

namespace App\Http\Service;

use App\Http\Model\ManageList;
use App\Http\Model\User;
use Illuminate\Http\Request;
use App\Http\Model\Contract;
use App\Http\Model\ContractStandard;
use App\Http\Model\Standard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class ContractService
{
    public function __construct(Request $request)
    {
        $this->request=$request;
    }

    public function list()
    {

        $user=Auth::user();
        $result=[];

        $data=Contract::where('id', '>', 0)->orderBy('id','desc')->paginate(15);

        return ['status'=>'1','message'=>'获取成功','data'=>$data];
    }

    public function get_contract_by_api()
    {
        $i=1;
        do {
            $flag=false;
            //DB::transaction(function () {
                $res = curl('http://114.55.32.144:443/productmgr/QueryOrderInspectionRAPI',
                    array('userid' => 'user@api', 'password' => 'password@api', 'page' => $i, 'pagesize' => 10),
                    true);
                $result = json_decode($res);
                if($result->totalCount!=0){$flag=true;$i++;}
                if (isset($result->IsSuccess) && $result->IsSuccess == 1) {
                    $Data = $result->Data;
                    foreach ($Data as $data) {
                        $contract_no = $data->InspectionRequiremenCode;

                        $factory = [
                            'contract_no' => $data->InspectionRequiremenCode,
                            'manufacturer' => $data->FactoryName,
                            'manufacturer_address' => $data->Address,
                            'factory_contacts' => $data->FactoryContacts,
                            'factory_email' => $data->FactoryEmail,
                            'total_volume' => $data->TotalVolume,
                            'total_net_weight' => $data->TotalNetWeight,
                            'total_count' => $data->TotalCount,
                            'plan_delivery_time' => str_date($data->PlanDeliveryTime),
                            'sign_time' => str_date($data->SignTime),
                            'json_data' => json_encode($data),
                            'create_user' => isset($data->CreateUser) ? $data->CreateUser : '',
                            'status_code' => $data->StatusCode,
                            'status_name' => $data->StatusName,
                            'user_list' => $data->UserList

                        ];
                        $Contract = Contract::where('contract_no', $contract_no)->first();
                        //print_r($Contract);die;
                        //echo $contract_no;die;

                        if ($Contract) {
                            //echo 'sssss';die;
                            //print_r($factory);die;
                            Contract::where('contract_no', $contract_no)->update($factory);
                            ContractStandard::where('contract_id', $Contract->id)->delete();
                            Standard::where('contract_id', $Contract->id)->delete();

                        } else {
                            $Contract = new Contract();
                            foreach ($factory as $key => $value) {
                                $Contract->$key = $value;
                            }
                            $Contract->save();

                        }

                        $contract_id = $Contract->id;
                        //添加对应sku标准
                        foreach ((array)$data->SkuInfos as $items) {

                            $sku = $items->InfoData->ProductCode;

                            $standard = new Standard();

                            $standard->contract_id = $contract_id;

                            $standard->sku = $sku;

                            $standard->description = json_encode($items);

                            $standard->save();

                        }
                    }

                }
            //});
        }while($flag==true);
        return ['status'=>1,'message'=>'更新成功','i'=>$i];
    }
    public function update_contract_status()
    {
        $contract=Contract::where('status_code','03')->orWhere('status_code','08')->select('id','contract_no','status_code','status_name')->get();

        foreach($contract as $data)
        {
            //if($data['status_code']=='03'||$data['status_code']=='08'){
                $res = curl('http://114.55.32.144:443/productmgr/QueryStatusRAPI',
                    array('userid' => 'user@api', 'password' => 'password@api','Code'=>$data->contract_no),
                    true);
					//if($data->id==28)return $res;
                if(!isset($res->Data->StatusCode))
                    $res=json_decode($res);
                if(isset($res->Data->StatusCode) && isset($res->Data->StatusName)) {
                    $update = Contract::find($data->id);
                    $update->status_code = $res->Data->StatusCode;
                    $update->status_name = $res->Data->StatusName;
                    $update->save();
                }
            //}



        }
        return ['status'=>1,'message'=>'更新成功'];
    }
    //解析sku数据
    public function analysis($id)
    {
        $contract_id=$id;
        //$Standard=Standard::where('contract_id',$contract_id)->get();
        $contract=Contract::where('id',$contract_id)->first();
        $json_data=json_decode($contract->json_data);
        //print_r($json_data);die;
        $Standard=isset($json_data->SkuInfos)?$json_data->SkuInfos:[];
        $sku_standard=[];//sku详细数据
        $sku_list=[];//sku列表

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
                $sku_list[]=array('name'=>$sku_sys->ChineseName,'rate_container'=>$sku_sys->RateContainer,'container_num'=>$sku_sys->DetailCount/$sku_sys->RateContainer,'pic'=>isset($sku_sys->pic)?current($sku_sys->pic):'','sku'=>$sku_sys->ProductCode,'Count'=>$sku_sys->Count,'detail_counts'=>$sku_sys->DetailCount);
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
        return  ['data'=>$sku_standard,'sku_list'=>$sku_list,'contract_info'=>$json_data];
    }

    public function sku_list($id)
    {
        $data=$this->analysis($id);
        return $data['sku_list'];
    }
    public function contract_info($id)
    {
        $data=$this->analysis($id);
        return $data['contract_info'];
    }
    //获取业务负责人信息
    public function get_manage_list(){

        try{
        $numberlist=array();
        $userlist=array();
        $res = curl('http://114.55.32.144:443/productmgr/QueryManageListRAPI',
            array('userid' => 'user@api', 'password' => 'password@api','userlist'=>$userlist,'numberlist'=>$numberlist),
            0);
        $res=json_decode($res,true);

        if(isset($res['IsSuccess'])&&$res['IsSuccess']){
            ManageList::truncate();

            foreach ($res['Data'] as $i) {
                //根据工号去用户表获得用户id和用户名
                $user_info=User::where('company_no',$i['WorkNumber'])->select('id')->first();

                if($user_info){
                    foreach ($i['ProductList'] as $ip) {
                        $manage_list_obj=new ManageList();
                        $manage_list_obj->user_id=$user_info->id;
                        $manage_list_obj->work_number=$i['WorkNumber'];
                        $manage_list_obj->name=$i['UserID'];
                        $manage_list_obj->manager_type=$i['ManagerType'];
                        $manage_list_obj->manager_type_name=$i['ManagerTypeName'];
                        $manage_list_obj->sku=$ip['ProductCode'];
                        $manage_list_obj->sku_chinese_name=$ip['Name'];
                        $manage_list_obj->save();
                    }
                }


            }


        }

        }catch(Exception $e){
            return $e->getMessage();
        }

    }


}
