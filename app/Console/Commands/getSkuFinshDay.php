<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Service\ScheduleService;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Http\Model\Contract;
use App\Http\Model\Standard;
use App\Http\Model\SkuFinishDay;


class getSkuFinshDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get_sku_finsh_day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get sku finsh day';

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

            $res=Standard::select('sku')->distinct()->get();
            foreach ($res as $i) {
                $sea_day=$this->get_days($i['sku']);

                $advance_day=$this->get_days_two($i['sku']);

                SkuFinishDay::updateOrCreate(['sku'=>$i['sku']],['sea_day'=>$sea_day,'advance_day'=>$advance_day]);
            }

            $res=Contract::where('status_code','03')->orWhere('status_code','08')->select('plan_delivery_time','id')->get();
            foreach ($res as $i) {
                $plan_delivery_day=DifferDay($i->plan_delivery_time,time());
                $sku_arr=Standard::where('contract_id',$i['id'])->select('sku','contract_id')->get();

                $a=null;
                foreach ($sku_arr as $i2) {
                    $arr=array();
                    $sku_finish_days=SkuFinishDay::where('sku',$i2['sku'])->first();
                    if($sku_finish_days){
                        $s=$sku_finish_days['advance_day']-$sku_finish_days['sea_day']-$plan_delivery_day;
                        if($a==null){
                            $a=$s;
                        }elseif($a>$s){
                            $a=$s;
                        }
                        $arr[]=$s;
                        Contract::where('id',$i2['contract_id'])->update(['finish_day_away'=>$a]);
                    }



                }

            }
            //return $arrs;



//            $log='恢复跟踪成功'.date('Y-m-d',$time);
//            Log::useDailyFiles(storage_path('logs/set_track/info.log'));
//            Log::info($log);


        }catch(Exception $e) {
//            Log::useDailyFiles(storage_path('logs/set_track/error.log'));
//            Log::error($e->getMessage());


        }

    }


    public function get_days_token(){
        $res = curl('http://openapi.can-erp.com/passport_open/login',
            array('client_id' => 'f9d7ddb158045beed1f8925d9667054b', 'client_secret' => 'b069b47c9564397af0e04f46b954d0cd','grant_type'=>'client_credentials'),
            0);
        $res=json_decode($res,true);
        if(isset($res['sub_code'])&&$res['sub_code']&&$res['data']){
            $data=$res['data'];
            return $data['access_token'];
        }
    }




    //获取指定sku 海上天数
    public function get_days($sku=''){


        //return $idMax=UserSchedule::groupBy(DB::raw("contract_id"))->select('id','user_id')->get();


        try{

            $access_token=$this->get_days_token();
            $header[] = "token: $access_token";
            $params=array('sku' => $sku);
            $res = curl('http://openapi.can-erp.com/sku/sea_day',
                $params,
                0,0,$header);
            $res=json_decode($res,true);
            if(!$res['sub_code']){
                return $res['data'];
            }




        }catch(Exception $e){
            return $e->getMessage();
        }

    }

    public function get_days_two($sku=''){
        try{
            $access_token=$this->get_days_token();
            $header[] = "token: $access_token";

            $dtEnd=date('Y-m-d',time());
            $dtStart=date('Y-m-d',time()-24*3600*60);

            $params=array('sku' => $sku,'dtStart'=>$dtStart,'dtEnd'=>$dtEnd);

            $res = curl('http://openapi.can-erp.com/sku/advance_day',
                $params,
                0,0,$header);
            $res=json_decode($res,true);
            if(!$res['sub_code']){
                return $res['data'];
            }


        }catch(Exception $e){
            return $e->getMessage();
        }
    }
}
