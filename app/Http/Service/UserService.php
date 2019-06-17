<?php

namespace App\Http\Service;

use App\Http\Model\ContractStandard;
use App\Http\Model\User;
use function foo\func;
use Illuminate\Http\Request;
use App\Http\Model\Contract;
use App\Http\Model\Standard;
use App\Http\Model\UserTask;
use App\Http\Model\Task;
use App\Http\Model\Role;
use App\Http\Model\InspectionRecord;
use App\Http\Model\InspectionRecordInfo;
use App\Http\Model\InspectionAccessoryRecord;
use App\Http\Model\InspectionOtherRecord;
use App\Http\Model\ContractGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Service\RoleService;
use App\Http\Model\UserRole;

class UserService
{
    public $filename;

    function __construct(Request $request)
    {
        $this->request=$request;
    }

    public function add_user()
    {
        $role_id=$this->request->input('role_id');
        $data=['name'=>$this->request->input('name'),
            'email'=>$this->request->input('email'),
            'company_no'=>$this->request->input('company_no'),
            'telephone'=>$this->request->input('telephone'),
            'password'=>bcrypt($this->request->input('company_no'))];
        //try {
            if(User::where('company_no',$data['company_no'])->get()->count()>0)return ['status'=>0,'message'=>'该工号已经存在'];
            $user = new User();
            foreach ($data as $key => $datum) {
                $user->$key = $datum;
            }
            $user->save();
            //用户分配角色
            $user_id = $user->id;
            $role_service = new RoleService($this->request);
            $role_service->add_user_role($user_id, $role_id);
            return ['status'=>1,'message'=>'添加成功'];
        //}catch (\Exception $e)
        //{
            return ['status'=>0,'message'=>'添加失败'];
        //}
    }
    public function update_user()
    {
        $user_id=$this->request->input('id');
        $role_id=$this->request->input('role_id');
        $data=['name'=>$this->request->input('name'),
            'email'=>$this->request->input('email'),
            'company_no'=>$this->request->input('company_no'),
            'telephone'=>$this->request->input('telephone'),
            'password'=>bcrypt($this->request->input('company_no'))];
        try {
            $user = User::find($user_id);
            foreach ($data as $key => $datum) {
                $user->$key = $datum;
            }
            $user->save();
            //用户分配角色
            $role_service =  new RoleService($this->request);
            $role_service->add_user_role($user_id, $role_id);
            return ['status'=>1,'message'=>'修改成功'];
        }catch (\Exception $e)
        {
            return ['status'=>0,'message'=>'修改失败'];
        }
    }
    public function get_user()
    {
        $user_id=$this->request->input('id');
        $user=User::find($user_id);
        if(!$user){
            return ['status'=>1,'message'=>'用户不存在'];
        }
//        $user_role=[];
//        foreach($user->role as $role)
//        {
//
//            if($role->parent_id==0){
//
//                $user_role[]=Role::find($role->id);
//                unset($user->role);
//                $user->role=[];
//            }else{
//                $user_role[]=Role::find($role->parent_id);
//            }
//        }
        $user_role=[];
        foreach($user->role as $role)
        {
            if($role->parent_id!=0){
                $user_role[]=Role::find($role->parent_id);
            }
            $user_role[]=$role;

        }
        //return $user_role;
        unset($user->role);
        $user->role=get_tree_child($user_role,0,'parent_id');

        return ['status'=>1,'message'=>'获取成功','data'=>$user];
    }
    public function user_list($user_name='',$role_name='',$role_id='',$department_id='')
    {
           if($department_id||$role_id){

               $arr1=array();
               if($department_id){
                   $roleRes=Role::where('parent_id',$department_id)->get();
                   foreach ($roleRes as $item) {
                       $roleIdArr[]=$item['id'];
                   }

                   $res=UserRole::whereIn('role_id',$roleIdArr)->get();

                   foreach ($res as $item) {
                       $arr1[]=$item['user_id'];
                   }
               }
               $arr2=array();
               if($role_id){
                   $res=UserRole::where('role_id',$role_id)->get();

                   foreach ($res as $item) {
                       $arr2[]=$item['user_id'];
                   }

//                   if(!$arr2){
//                       $arr2=$arr1;
//                   }else{
                       $arr1=array();
//                   }

               }

               $arr=array_merge($arr1,$arr2);


               $data=User::where('id','>',0)->whereIn('id',$arr);




           }else{
                $data=User::where('id','>',0);
            }



            if($user_name){
                $data=$data->where('name','like','%'.$user_name.'%');
            }
            $data=$data->paginate(15);
            foreach($data as $datum)
            {

                $user_role=[];
                foreach($datum->role as $role)
                {

                    if($role->parent_id==0){

                        $user_role[]=Role::find($role->id);
                        unset($datum->role);
                        $datum->role=[];
                    }else{
                        $user_role[]=Role::find($role->parent_id);
                    }
                }
                $datum->parent_role=$user_role;
            }
            return ['status'=>1,'message'=>'获取成功','data'=>$data];




    }
    public function update_status()
    {
        $id=$this->request->input('id');
        $status=$this->request->input('status');
        $user=User::find($id);
        $user->status=$status;
        $user->save();
        return ['status'=>1,'message'=>'操作成功'];
    }

    //
    public function get_user_by_role($name){
        $roleRes=Role::where('name',$name)->first();
        if($roleRes){

        }
        $role_parent_id=$roleRes['id'];
        $data=Role::where('parent_id',$role_parent_id)->get();
        foreach ($data as $k=>$v){
            $arr[]=$v['id'];
        }
        $res=UserRole::whereIn('role_id',$arr)->get();

        foreach ($res as $k=>$v){
            $arr2[]=$v['user_id'];
        }
        $res=User::whereIn('id',$arr2)->select('id','name')->get();
        return $res;
    }

    public function get_user_by_role2($name='') {
        $roleRes = Role::where('name', $name)->first();
        $role_parent_id = $roleRes['id'];
        return Role::where('parent_id',$role_parent_id)->with('users')->get();
//        return User::with(['role'=>function ($query) use($role_parent_id){
//            $query->where('parent_id',$role_parent_id)->first();
//        }])->get();
    }
}
