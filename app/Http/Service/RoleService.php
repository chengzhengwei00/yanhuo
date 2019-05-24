<?php

namespace App\Http\Service;

use Illuminate\Http\Request;
use App\Http\Model\Permission;
use App\Http\Model\User;
use App\Http\Model\Role;
use App\Http\Model\UserRole;
use App\Http\Model\User_has_permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleService
{
    public function __construct(Request $request)
    {
        $this->request=$request;
    }

    //添加岗位或者部门
    public function add_role()
    {
        $role=new Role();
        $data=['name'=>$this->request->input('name'),
            'description'=>$this->request->input('description'),
            'parent_id'=>$this->request->input('parent_id'),
            'type'=>$this->request->input('type')];
        foreach($data as $key=>$datum)
        {
            $role->$key=$datum;
        }
        if($role->save())
        {
            return ['status'=>1,'message'=>'添加成功'];
        }else{
            return ['status'=>0,'message'=>'添加失败'];
        }
    }
    //修改部门或者岗位
    public function update_role()
    {
        $id=$this->request->input('id');
        $role=Role::find($id);
        $data=['name'=>$this->request->input('name'),
            'description'=>$this->request->input('description'),
            'parent_id'=>$this->request->input('parent_id'),
            'type'=>$this->request->input('type')];
        foreach($data as $key=>$datum)
        {
            $role->$key=$datum;
        }
        if($role->save())
        {
            return ['status'=>1,'message'=>'修改成功'];
        }else{
            return ['status'=>0,'message'=>'修改失败'];
        }
    }
    //删除岗位或者部门
    public function delete_role($id)
    {
        DB::beginTransaction();//开启事务
        try {
            $role = Role::find($id);
            if (isset($role->parent_id) && $role->parent_id != 0) {
                Role::where('parent_id', $id)->delete();
            }
            Role::find($id)->delete();
            DB::commit();//成功，提交事务
            return ['status'=>1,'message'=>'删除成功'];
        }catch (\Exception $e) {
            DB::rollBack();//失败，回滚事务
            return ['status'=>0,'message'=>'删除失败'];
        }

    }
    //部门展示
    public function department_list()
    {
        $department=Role::where('parent_id',0)->get();
        return ['status'=>1,'message'=>'展示成功','data'=>$department];
    }
    //岗位展示
    public function position_list()
    {
        $id=$this->request->input('id');
        $department=Role::where('parent_id',$id)->get();
        $parent=Role::find($id);


        return ['status'=>1,'message'=>'展示成功','data'=>$department,'parent'=>$parent];
    }
    //岗位部门展示
    public function list()
    {
        return ['status'=>1,'message'=>'展示成功','data'=>Role::all()];
    }
    //分配角色
    public function add_user_role($user_id,$role_id)
    {
        if(UserRole::where('user_id',$user_id)->get()->count()>0)
        {
            UserRole::where('user_id',$user_id)->update(['role_id'=>$role_id]);

        }else {

            $user_role = new UserRole();
            $user_role->user_id = $user_id;
            $user_role->role_id = $role_id;
            $user_role->save();
        }
        return ['status'=>1,'message'=>'操作成功'];

    }

    //根据id展示部门或者岗位
    public function show_role()
    {
        $id=$this->request->input('id');
        $role=Role::find($id);
        if($role->parent_id==0){
            $parent_name=$role->name;
        }else{
            $parent=Role::find($role->parent_id);
            $parent_name=$parent->name;
        }
        return ['status'=>1,'message'=>'展示成果','data'=>$role,'parent'=>$parent_name];
    }


    //
    public function getUserListByPosition($role_id){
        if(!$role_id){
            return ['status'=>0,'message'=>'参数错误'];
        }
        $res=UserRole::where('role_id',$role_id)->get();
        $arr=array();
        foreach ($res as $item) {
            $arr[]=$item['user_id'];
        }
        if($arr){
            $data=User::whereIn('id',$arr)->get();
            return ['status'=>1,'message'=>'获取成功','data'=>$data];
        }else{
            return ['status'=>0,'message'=>'数据为空'];
        }


    }


}
