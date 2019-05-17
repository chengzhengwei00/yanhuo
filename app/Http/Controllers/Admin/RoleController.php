<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Service\RoleService;

class RoleController extends Controller
{
    /**
     *
     * @SWG\SecurityScheme(
     *     securityDefinition="Bearer",
     *     type="apiKey",
     *     in="header",
     *     name="api_token"
     * )
     *
     */
    public function __construct(RoleService $roleService,Request $request)
    {
        $this->roleService=$roleService;
        $this->request=$request;
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
        return $this->roleService->delete_role($id);
    }
    /**
     * 假设是项目中的一个API
     *
     * @SWG\Post(
     *   path="/api/v1/role/add-role",
     *   tags={"添加岗位部门"},
     *   summary="添加岗位部门",
     *   description="添加岗位部门。",
     *   operationId="postAddRole",
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
     *         description="岗位部门名字",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="description",
     *         in="query",
     *         description="岗位部门描述",
     *         required=true,
     *         type="string",
     *     ),
     *    @SWG\Parameter(
     *         name="parent_id",
     *         in="query",
     *         description="部门的parent_id为0，岗位的parent_id就是部门的id",
     *         required=true,
     *         type="integer",
     *     ),
     *      @SWG\Parameter(
     *         name="type",
     *         in="query",
     *         description="用来区分部门和岗位，部门是0，岗位是1",
     *         required=true,
     *         type="integer",
     *     ),
     * )
     */
    public function postAddRole()
    {
        return $this->roleService->add_role();
    }
    public function postUpdateRole()
    {
        return $this->roleService->update_role();
    }
    public function getDeleteRole()
    {
        return $this->roleService->delete_role();
    }
    public function getList()
    {
        return $this->roleService->list();
    }
    public function getDepartmentList()
    {
        return $this->roleService->department_list();
    }
    public function getPositionList()
    {
        return $this->roleService->position_list();
    }
    public function getShowRole()
    {
        return $this->roleService->show_role();
    }


}
