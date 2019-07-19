<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UpdateDefaultPassword
{
    public function __construct() {
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
//        $res=$this->permissionsService->show_user_permission();
//
//        $user_permissions=$this->getTrees($res);
//        $action_name=$this->request->route()->getActionName();
//        $action_name=ltrim(strrchr($action_name,'\\'),'\\');
//        if(in_array($action_name,$user_permissions)){
//            return $next($request);
//        }else{
//            //echo "路由没有设置权限";exit;
//            //return array('status'=>0,'message'=>'路由没有设置权限');
//            //return ['status' => 0, 'message' => '路由没有设置权限'];
//            return response()->json(['status' => 0, 'message' => '路由没有设置权限']);
//        }
        $user = Auth::user();

        if(isset($user->company_no)&&isset($user->password)){
            if(Hash::check($user->company_no,$user->password)){
                return response()->json([
                    'status'=>0,
                    'message'=>'请修改新的密码'
                ]);
                //return $user;
            }else{
                return $next($request);
            }
        }else{
            return response()->json([
                'status'=>0,
                'message'=>'用户信息出错'
            ]);
        }




    }


}
