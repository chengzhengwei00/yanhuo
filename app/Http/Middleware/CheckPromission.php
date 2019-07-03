<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Service\PermissionsService;
use Illuminate\Http\Request;

class CheckPromission
{
    public function __construct(PermissionsService $permissionsService,Request $request) {
        $this->permissionsService=$permissionsService;
        $this->request=$request;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $res=$this->permissionsService->show_user_permission();

        $user_permissions=$this->getTrees($res);
        $action_name=$this->request->route()->getActionName();
        $action_name=ltrim(strrchr($action_name,'\\'),'\\');
        if(in_array($action_name,$user_permissions)){
            return $next($request);
        }else{
            //echo "路由没有设置权限";exit;
            //return array('status'=>0,'message'=>'路由没有设置权限');
            //return ['status' => 0, 'message' => '路由没有设置权限'];
            return response()->json(['status' => 0, 'message' => '路由没有设置权限']);
        }


    }

    public function getTrees($res){

        static $arr=array();
        foreach ($res as $v) {
            if(isset($v['child'])){
                $arr=$this->getTrees($v['child']);

            }else{
                $user_limit=$v['method_name'];
                if(strpos($user_limit,'&&')){
                    $user_limit_arr=explode('&&',$user_limit);
                    $arr=array_merge($user_limit_arr,$arr);
                }else{
                    $arr[]=$user_limit;
                }

            }


        }

        return $arr;
    }
}
