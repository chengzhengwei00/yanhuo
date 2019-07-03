<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Service\ScheduleService;
use App\Http\Model\ScheduleUpdateEmail;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Log;
use Exception;


class ScheduleEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'schedule update email';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService=$scheduleService;
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

                $last_week_data=$history=$this->scheduleService->get_update_schedule();
                $new_data=array();
                foreach ($last_week_data as $kl=> $vl) {
                    if($vl){
                        $new_data[$vl['email']][$kl]=$vl;
                    }
                }
                if($new_data){
                    foreach ($new_data as $kn=> $vn) {
                        if($vn){
                            $messageLines=array();
                            $vn_new=$vn;
                            foreach ($vn  as $kv => $vv) {

                                //查询是否已经发送过邮件
                                $is_exist=ScheduleUpdateEmail::where('contract_no',$kv)
                                    ->where('week_first',$vv['week_first'])
                                    ->where('week_end',$vv['week_first_next'])
                                    ->where('user_id',$vv['user_id'])
                                    ->first();
                                if($is_exist){
                                    unset($vn_new[$kv]);
                                    continue;
                                }

                                $messageLines[]='合同号：'.$kv.'  上周('.$vv['week_first'].'-'.$vv['week_first_next'].')未进度更新，请注意及时处理';
                                $name=$vv['name'];

                            }
                            if($vn_new){
                                $formData=array(
                                    'name'=>$name,
                                    'messageLines'=>$messageLines
                                );

                                $res=Mail::to('braveren@sandinrayli.com')->cc('braveren@sandinrayli.com')
                                    ->bcc('braveren@sandinrayli.com')->send(new TestEmail($formData));

                                if(!$res){
                                    Log::useDailyFiles(storage_path('logs/schedule_email/info.log'));
                                    Log::info('cuowu');
                                }




                                foreach ($vn_new  as $kvn => $vvn) {


                                    ScheduleUpdateEmail::insert([
                                            'contract_no'=>$kvn,
                                            'week_first' =>$vvn['week_first'],
                                            'week_end' =>$vvn['week_first_next'],
                                            'user_id' =>$vvn['user_id'],
                                        ]
                                    );


                                }
                            }


                        }

                    }

                }


        }catch(Exception $e) {
            Log::useDailyFiles(storage_path('logs/schedule_email/error.log'));
            Log::error($e->getMessage());


        }

    }
}
