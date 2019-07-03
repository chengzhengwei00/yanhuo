<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Service\ScheduleService;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Http\Model\Contract;


class SetTrackAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set_track_all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'set track all';

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

            //获得60天后的时间
            $time=time()+3600*24*60;
            $date=date('Y-m-d H:i:s',$time);
            //return $date;
            Contract::where('plan_delivery_time','<',$date)->update(array('delay_track'=>0));
            $log='恢复跟踪成功'.date('Y-m-d',$time);
            Log::useDailyFiles(storage_path('logs/set_track/info.log'));
            Log::info($log);


        }catch(Exception $e) {
            Log::useDailyFiles(storage_path('logs/set_track/error.log'));
            Log::error($e->getMessage());


        }

    }
}
