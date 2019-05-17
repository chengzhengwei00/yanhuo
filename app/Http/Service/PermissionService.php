<?php

namespace App\Http\Service;

use App\Http\Model\UserRole;
use Illuminate\Http\Request;
use App\Http\Model\Permission;
use App\Http\Model\User;
use App\Http\Model\User_has_permission;
use App\Http\Model\Role;
use App\Http\Model\RolePermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PermissionsService
{
    public function __construct(Request $request)
    {
        $this->request=$request;
    }

    //显示特定分类子权限
    public function show_permission($id=0)
    {
        $array=[];
        $permission=Permission::where('parent_id',$id)->get();
        foreach($permission as $data)
        {
            $son=Permission::where('parent_id',$data->id)->get();
            if($son->count()>0) {
                $data->child=$son;
                $array[] = $data;
            }else{
                $array[] = $data;
            }

        }
        return $array;

    }
    //显示全部权限
    public function show_all_permission()
    {

        $permission=Permission::all();
        $array=[];
        foreach($permission as $data)
        {
            $array[]=array('id'=>$data->id,
                'api_route'=>$data->api_route,
                'display_name'=>$data->display_name,
                'user_limit'=>$data->user_limit,
                'parent_id'=>$data->parent_id);
        }
        return get_tree_child($array,0,'parent_id');

    }
    //分配用户权限
    public function edit_user_permission( )
    {
        DB::beginTransaction();//开启事务
        try {
            $user_id = $this->request->input('user_id');
            $permission_id_array = $this->request->input('permission_id');
            $this->delete_user_permission($user_id);
            if(empty($permission_id_array)){
                return ['status'=>1,'message'=>'分配成功'];
            }
            //print_r($this->request->input('permission_id'));die;
            foreach ((array)$permission_id_array as $permission_id) {
                $user_has_permission = new User_has_permission();
                $user_has_permission->user_id = $user_id;
                $user_has_permission->permission_id = $permission_id;
                $user_has_permission->save();
            }
            DB::commit();//成功，提交事务
        return ['status'=>1,'message'=>'分配成功'];
        }catch (\Exception $e) {
            DB::rollBack();//失败，回滚事务
            return ['status' => 0, 'message' => '分配失败'];
        }

    }
    //删除用户权限
    public function delete_user_permission($user_id)
    {
		$user_has_permission=User_has_permission::where('user_id',$user_id)->delete();
		if($user_has_permission){
            return ['status'=>1,'message'=>'删除成功'];
        }else{
            return ['status'=>0,'message'=>'删除失败'];
        }
    }
    //删除角色权限
    public function delete_role_permission($role_id)
    {
        $role_permission=RolePermission::where('role_id',$role_id)->delete();
        if($role_permission){
            return ['status'=>1,'message'=>'删除成功'];
        }else{
            return ['status'=>0,'message'=>'删除失败'];
        }
    }
    //展示用户自定义权限
    public function user_permission($id)
    {
        $user_permission=[];
        $user_has_permission=User_has_permission::where('user_id',$id)->get();
        foreach($user_has_permission as $permission)
        {
            $user_permission[]= $permission->permission_id;
        }

        $permission=Permission::all();
        $array=[];
        foreach($permission as $data)
        {


                if(in_array($data->id,$user_permission)){
                    $array[]=array('id'=>$data->id,
                        'api_route'=>$data->api_route,
                        'display_name'=>$data->display_name,
                        'user_limit'=>$data->user_limit,
                        'parent_id'=>$data->parent_id,
                        'display'=>1);
                }else{
                    $array[]=array('id'=>$data->id,
                        'api_route'=>$data->api_route,
                        'display_name'=>$data->display_name,
                        'user_limit'=>$data->user_limit,
                        'parent_id'=>$data->parent_id,
                        'display'=>0);

                }

        }
        return get_tree_child($array,0,'parent_id');


    }
    //展示用户自定义权限
    public function show_user_permission( )
    {
        $user_id=$this->request->input('user_id');

        if(User_has_permission::where('user_id',$user_id)->get()->count()>0)
        {
            return $this->user_permission($user_id);
        }
        if($user_role=UserRole::where('user_id',$user_id)->first())
        {
            $role_id=$user_role->role_id;
            return $this->role_permission($role_id);
        }
    }
    //添加权限
    public function add_permission( )
    {

        $api_route=$this->request->input('api_route');
        $display_name=$this->request->input('display_name');
        $user_limit=$this->request->input('user_limit');
        $parent_id=$this->request->input('parent_id');
        if(Permission::where('user_limit',$user_limit)->where('api_route',$api_route)->count()>0){
            return ['status'=>0,'message'=>'该路由组合已经存在'];
        }
        $permission= new Permission;
        $permission->api_route = $api_route;
        $permission->display_name = $display_name;
        $permission->user_limit = $user_limit;
        $permission->parent_id = $parent_id;
        $permission->save();
        return ['status'=>1,'message'=>'添加成功'];
    }
    //修改权限
    public function update_permission( )
    {
        try {
            $id=$this->request->input('id');
            $api_route=$this->request->input('api_route');
            $display_name=$this->request->input('display_name');
            $user_limit=$this->request->input('user_limit');
            $parent_id=$this->request->input('parent_id');
            $permission=  Permission::find($id);
            $permission->api_route = $api_route;
            $permission->display_name = $display_name;
            $permission->user_limit = $user_limit;
            $permission->parent_id = $parent_id;
            $permission->save();
        }catch (\Exception $e) {
            return ['status'=>0,'message'=>'修改失败'];
        }
        return ['status'=>1,'message'=>'修改成功'];
    }
    //删除权限
    public function delete_permission($id)
    {
        DB::beginTransaction();//开启事务
        try {
            $permission = Permission::where('id', $id)->delete();
            $User_has_permission = User_has_permission::where('permission_id', $id)->delete();
            DB::commit();//成功，提交事务
            return ['status' => 1, 'message' => '删除成功'];
        }catch (\Exception $e) {
            DB::rollBack();//失败，回滚事务
            return ['status' => 0, 'message' => '删除失败'];

        }


    }
    //显示父级权限
    public function show_parent_permission()
    {
        $parent_id=$this->request->input('parent_id');
        $permission=Permission::where('id',$parent_id)->first();
        return ['status' => 1, 'message' => '展示成功','data'=>$permission];
    }
    //根据id显示本身权限
    public function show_current_permission()
    {
        $id=$this->request->input('id');
        $permission=Permission::where('id',$id)->first();
        return ['status' => 1, 'message' => '展示成功','data'=>$permission];
    }
    //给角色分配权限
    public function edit_role_permission()
    {
        DB::beginTransaction();//开启事务
        try {
            $role_id = $this->request->input('role_id');
            $this->delete_role_permission($role_id);
            $permission_id_array = $this->request->input('permission_id');
            //print_r($this->request->input('permission_id'));die;
            foreach ((array)$permission_id_array as $permission_id) {
                $role_permission = new RolePermission();
                $role_permission->role_id = $role_id;
                $role_permission->permission_id = $permission_id;
                $role_permission->save();
            }
            DB::commit();//成功，提交事务
            return ['status'=>1,'message'=>'分配成功'];
        }catch (\Exception $e) {
            DB::rollBack();//失败，回滚事务
            return ['status' => 0, 'message' => '分配失败'];
        }

    }
    public function gave_permission()
    {
        $type=$this->request->input('type');
        if($type=='user') {
            return $this->edit_user_permission();
        }
        if($type=='role') {
            return $this->edit_role_permission();
        }
        return ['status'=>0,'message'=>'参数非法'];
    }
    //展示角色权限
    public function role_permission($id)
    {
        $role_permission_array=[];
        $role=Role::find($id);
        if($role->parent_id!=0){
            $role_permission=RolePermission::where('role_id',$role->parent_id)->get();
            foreach($role_permission as $permission)
            {
                $role_permission_array[]= $permission->permission_id;
            }
        }
        $role_permission=RolePermission::where('role_id',$id)->get();
        foreach($role_permission as $permission)
        {
            $role_permission_array[]= $permission->permission_id;
        }

        $permission=Permission::all();
        $array=[];
        foreach($permission as $data)
        {


            if(in_array($data->id,$role_permission_array)){
                $array[]=array('id'=>$data->id,
                    'api_route'=>$data->api_route,
                    'display_name'=>$data->display_name,
                    'user_limit'=>$data->user_limit,
                    'parent_id'=>$data->parent_id,
                    'display'=>1);
            }else{
                $array[]=array('id'=>$data->id,
                    'api_route'=>$data->api_route,
                    'display_name'=>$data->display_name,
                    'user_limit'=>$data->user_limit,
                    'parent_id'=>$data->parent_id,
                    'display'=>0);

            }

        }
        return get_tree_child($array,0,'parent_id');


    }

    //展示角色权限
    public function show_role_permission()
    {
        $id=$this->request->input('id');
        return $this->role_permission($id);
    }
    //显示用户或者角色权限
    public function show_user_role_permission()
    {
        $user_id=$this->request->input('user_id');
        $role_id=$this->request->input('role_id');
        $type=$this->request->input('type');
        if($type=='user')
        {
            if(User_has_permission::where('user_id',$user_id)->get()->count()>0)
            {
                return $this->user_permission($user_id);
            }
            if($user_role=UserRole::where('user_id',$user_id)->first())
            {
                $role_id=$user_role->role_id;
                return $this->role_permission($role_id);
            }
        }
        if($type=='role')
        {
            return $this->role_permission($role_id);
        }
        return ['status'=>0,'message'=>'数据不存在'];


    }
    //根据用户id查询权限
    public function show_user_role_permission_by_id($user_id)
    {
        if(User_has_permission::where('user_id',$user_id)->get()->count()>0)
        {
            $array=[];
            $user_has_permission=User_has_permission::where('user_id',$user_id)->get();
            foreach($user_has_permission as $permission)
            {
                $array[]= $permission->permission;
            }
            return ['status'=>'1','message'=>'展示成功','data'=>$array];
        }
        if(UserRole::where('user_id',$user_id)->get()->count()>0)
        {

            $user=User::find($user_id);
            foreach($user->role as $roles)
            {
                $array= $roles->permissions;
            }
            return ['status'=>'1','message'=>'展示成功','data'=>$array];
        }


    }

}
