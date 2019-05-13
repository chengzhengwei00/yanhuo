<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Service\UserService;

class UserController extends Controller
{
    public function __construct(Request $request,UserService $userService)
    {
        $this->request=$request;
        $this->userService=$userService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    /**
     * 假设是项目中的一个API
     *
     * @SWG\Post(
     *   path="/api/v1/reset",
     *   tags={"重置密码"},
     *   summary="重置密码",
     *   description="重置密码。",
     *   operationId="postreset",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(response="default", description="操作成功"),
     *   @SWG\Parameter(
     *         name="oldpassword",
     *         in="query",
     *         description="旧密码",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="query",
     *         description="密码",
     *         required=true,
     *         type="string",
     *     ),
     *    @SWG\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="用户的id",
     *         required=true,
     *         type="integer",
     *     ),
     * )
     */
    public function reset(Request $request)
    {

        $old_password = $request->input('oldpassword');
        $password = $request->input('password');
        $data=$request->input();
        $rules = [
            'oldpassword' => 'required|between:4,20',
            'password' => 'required|between:4,20|confirmed',
            'password_confirmation' => 'required|between:4,20',
        ];
        $messages = [
            'required' => '密码不能为空',
            'between' => '密码必须是4~20位之间'
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages);

        $user = Auth::user();
        $validator->after(function ($validator) use ($old_password, $user) {
            if (!\Hash::check($old_password, $user->password)) {
                return response()->json(['status'=>0,
                    'message'=>'原密码错误']);
            }
        });

        $user->password = bcrypt($password);
        $user->save();
        return response()->json(['status'=>'1',
            'message'=>'重置成功']);
        //Auth::logout();  //更改完这次密码后，退出这个用户

        return redirect('/login');
    }
    /**
     * 假设是项目中的一个API
     *
     * @SWG\Post(
     *   path="/api/v1/user/add-user",
     *   tags={"添加用户"},
     *   summary="添加用户",
     *   description="添加用户。",
     *   operationId="post-add-user",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(response="default", description="操作成功"),
     *   @SWG\Parameter(
     *         name="name",
     *         in="query",
     *         description="用户名",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         in="query",
     *         description="email",
     *         required=true,
     *         type="string",
     *     ),
     *    @SWG\Parameter(
     *         name="company_no",
     *         in="query",
     *         description="工号",
     *         required=true,
     *         type="string",
     *     ),
     *    @SWG\Parameter(
     *         name="telephone",
     *         in="query",
     *         description="电话",
     *         required=true,
     *         type="string",
     *     ),
     *    @SWG\Parameter(
     *         name="password",
     *         in="query",
     *         description="密码",
     *         required=true,
     *         type="string",
     *     ),
     *  @SWG\Parameter(
     *         name="role_id",
     *         in="query",
     *         description="岗位id",
     *         required=true,
     *         type="integer",
     *     ),
     * )
     */
    public function postAddUser()
    {
        return $this->userService->add_user();
    }
    //修改用户
    public function postUpdateUser()
    {
        return $this->userService->update_user();
    }
    //用户列表
    public function getUserList()
    {
        return $this->userService->user_list();
    }
    //根据id获取用户
    public function getGetUser()
    {
        return $this->userService->get_user();
    }
    //根据id修改状态
    public function postUpdateStatus()
    {
        return $this->userService->update_status();
    }
}
