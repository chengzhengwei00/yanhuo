<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Service\PermissionsService;

class PermissionController extends Controller
{
    public function __construct(PermissionsService $permissionsService,Request $request)
    {
        $this->permissionsService=$permissionsService;
        $this->request=$request;

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //展示顶级分类权限
    public function index( )
    {
        //
        $id=0;
        return $this->permissionsService->show_permission($id);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return $this->permissionsService->add_user_permission();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //添加权限
    public function store( )
    {
        //

        return $this->permissionsService->add_permission();

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id )
    {
        //

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        //
        return $this->permissionsService->update_permission();
    }
    //修改权限
    public function postUpdatePermission()
    {
        //
        return $this->permissionsService->update_permission();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //删除权限
    public function destroy($id)
    {
        //
        return $this->permissionsService->delete_permission($id);
    }
    //显示特定分类权限
    public function getShowPermission()
    {
        $id=$this->request->input('pid');
        $id=($id)?$id:0;
        return $this->permissionsService->show_permission($id);
    }
    //配置用户权限
    public function postUserPermission()
    {
        return $this->permissionsService->edit_user_permission();
    }
    //
    /**
     * 展示用户权限
     *
     * @SWG\Get(
     *   path="/api/v1/permissions/user-permission",
     *   tags={"展示用户权限"},
     *   summary="展示用户权限",
     *   description="展示用户权限。",
     *   produces={"application/json"},
     *   security={
     *          {
     *              "Bearer":{}
     *          }
     *   },
     *   @SWG\Response(response="default", description="操作成功"),
     *   @SWG\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="用户id",
     *         required=true,
     *         type="integer",
     *    ),
     * )
     */
    public function getUserPermission()
    {;
        return $this->permissionsService->show_user_permission();
    }

    //展示用户权限重构
    public function getUserPermissionReconstruct()
    {
        $r=$this->request->route()->getActionName();
        var_dump($r);die;
        $res=$this->permissionsService->show_user_permission();
        return $this->getTrees($res);

    }
    public function getTrees($res){
        static $arr=array();
        foreach ($res as $v) {
            if(isset($v['child'])){
                $arr=$this->getTrees($v['child']);

            }else{
                $user_limit=$v['user_limit'];
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


    //展示所有权限
    public function getAllPermission()
    {;
        return $this->permissionsService->show_all_permission();
    }
    //展示父级权限
    public function getParentPermission()
    {;
        return $this->permissionsService->show_parent_permission();
    }
    //展示当前权限
    public function getCurrentPermission()
    {;
        return $this->permissionsService->show_current_permission();
    }
    //配置角色权限
    public function postRolePermission()
    {
        return $this->permissionsService->edit_role_permission();
    }
    //展示角色权限
    public function getRolePermission()
    {
        return $this->permissionsService->show_role_permission();
    }
    //展示角色权限
    public function getUserRolePermission()
    {
        return $this->permissionsService->show_user_role_permission();
    }
    //根据类型分配权限type=user or type=role
    public function postGavePermission()
    {
        return $this->permissionsService->gave_permission();
    }


}
