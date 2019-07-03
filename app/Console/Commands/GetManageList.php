<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Http\Model\User;
use App\Http\Model\ManageList;


class GetManageList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_manage_list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get manage list';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


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

                Log::useDailyFiles(storage_path('logs/get_manage_list/info.log'));
                Log::info('成功');


            }

        }catch(Exception $e){
            Log::useDailyFiles(storage_path('logs/get_manage_list/error.log'));
            Log::error($e->getMessage());
        }

    }
}
