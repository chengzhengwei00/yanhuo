<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Service\PermissionsService;
use App\Http\Model\ManageList;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    public function username()
    {
        return 'company_no';
    }
    protected function credentials(Request $request)
    {
        $credentials  = $request->only($this->username(), 'password');
        $credentials['status'] = 1;
        return $credentials;
    }
    public function login(Request $request,PermissionsService $permissionsService)
    {
        $this->validateLogin($request);
//        if($request->input('company_no')=='' || $request->input('password')=='')
//        {
//            return response()->json(['status'=>'0','message'=>'用户名或密码不能为空']);
//        }
        if ($this->attemptLogin($request)) {
            $user = $this->guard()->user();
            $user->generateToken();
            $user_permission=$permissionsService->show_user_role_permission_by_id($user->id);
            $department='';
             if(isset($user->company_no)&&$user->company_no){
                 $manage_list_data=ManageList::where('work_number',$user->company_no)->get();
                 if(count($manage_list_data)>0){
                     $department='manager';
                 }
             }
            $user->department=$department;





            return response()->json([
                'status'=>1,
                'message'=>'登录成功',
                'data' => array_merge($user->toArray()),
                'permission'=>$user_permission
            ]);
        }else{
            return response()->json([
                'status'=>0,
                'message'=>'登录失败'
            ]);
        }

        return $this->sendFailedLoginResponse($request);
    }
    public function logout(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user) {
            $user->api_token = null;
            $user->save();
        }

        return response()->json([
            'status'=>1,
            'message'=>'注销成功',
            'data' => ''], 200);
    }
}
