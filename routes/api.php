<?php

use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['prefix' => 'v1'], function(){

    Route::post('register', 'Auth\RegisterController@register')->name('auth.register.register');
    Route::post('login', 'Auth\LoginController@login')->name('auth.login.login');;
    Route::get('hello', function () {
        return 'Hello, Welcome to LaravelAcademy.org';
    });
    Route::get('swagger/json', 'SwaggerController@getJson');
	Route::get('contracts/get-contracts-for-api', 'Admin\ContractController@getContractsForApi');//获取验货标准接口
    Route::get('contracts/update-contracts-status', 'Admin\ContractController@geUpdateContractStatus');//获取验货标准接口


});

Route::group(['prefix' => 'v1','middleware' => 'auth:api'], function(){
    //用户类
    Route::get('logout', 'Auth\LoginController@logout')->name('auth.login.logout');//注销
    Route::post('reset', 'Admin\UserController@reset')->name('admin.user.reset');//重置密码
    Route::post('role/add-role', 'Admin\RoleController@postAddRole');//添加岗位或者部门
    Route::post('role/update-role', 'Admin\RoleController@postUpdateRole');//添加岗位或者部门
    Route::get('role/list', 'Admin\RoleController@getList');//删除岗位或者部门
    Route::get('role/department-list', 'Admin\RoleController@getDepartmentList');//展示部门
    Route::get('role/position-list', 'Admin\RoleController@getPositionList');//展示岗位
    Route::post('user/add-user', 'Admin\UserController@postAddUser');//添加用户
    Route::post('user/update-user', 'Admin\UserController@postUpdateUser');//修改用户
    Route::get('user/user-list', 'Admin\UserController@getUserList');//用户列表
    Route::get('user/get-user', 'Admin\UserController@getGetUser');//根据id获取用户
    Route::post('user/update-status', 'Admin\UserController@postUpdateStatus');//根据id获取用户
    Route::get('role/show-role', 'Admin\RoleController@getShowRole');//根据id部门岗位展示
    //Route::get('role/user-list-roleid', 'Admin\RoleController@getUserListByPosition');//展示岗位下面的用户

    Route::apiResource('role', 'Admin\RoleController');
    //合同类


    Route::apiResource('standards', 'Admin\StandardController');
    Route::apiResource('contracts', 'Admin\ContractController');
    Route::apiResource('users', 'Admin\UserController');
    //权限控制器
    Route::get('permissions/show-permission', 'Admin\PermissionController@getShowPermission');//显示特定分类权限
    Route::post('permissions/update-permission', 'Admin\PermissionController@postUpdatePermission');//修改权限
    Route::post('permissions/user-permission', 'Admin\PermissionController@postUserPermission');//配置用户权限
    Route::get('permissions/user-permission', 'Admin\PermissionController@getUserPermission');//展示用户权限
    Route::get('permissions/all-permission', 'Admin\PermissionController@getAllPermission');//展示所有权限
    Route::get('permissions/parent-permission', 'Admin\PermissionController@getParentPermission');//展示父级权限
    Route::get('permissions/current-permission', 'Admin\PermissionController@getCurrentPermission');//展示当前权限
    Route::post('permissions/role-permission', 'Admin\PermissionController@postRolePermission');//配置角色权限
    Route::get('permissions/role-permission', 'Admin\PermissionController@getRolePermission');//展示角色权限
    Route::get('permissions/user-role-permission', 'Admin\PermissionController@getUserRolePermission');//展示角色权限
    Route::post('permissions/gave-permission', 'Admin\PermissionController@postGavePermission');//展示角色权限

    Route::apiResource('permissions', 'Admin\PermissionController');
    //任务控制器
    Route::get('task/task-sku-view', 'Admin\TaskController@getTaskSkuView');//展示验货表单详细信息
    Route::post('task/task-add', 'Admin\TaskController@postTaskAdd');//分配任务
    Route::post('task/task-inspection-post', 'Admin\TaskController@postInspectionPost');//处理验收的sku结果数据
    Route::delete('task/task-delete', 'Admin\TaskController@deleteTask');//删除任务
    Route::get('task/user-task-factory', 'Admin\TaskController@getUserTaskFactory');//获取任务下的工厂
    Route::get('task/task-factory-contract', 'Admin\TaskController@getTaskFactoryContract');//获取工厂下的po
    Route::get('task/contract-sku-list', 'Admin\TaskController@getContractSkuList');//获取po下的sku
    Route::get('task/task-acc-view', 'Admin\TaskController@getTaskAccView');//展示配件验货表单
    Route::post('task/task-inspection-acc-post', 'Admin\TaskController@postInspectionAccPost');//提交配件验货表单
    Route::get('task/inspection-result-task-list', 'Admin\TaskController@getInspectionResultTaskList');//检验结果任务列表
    Route::get('task/inspection-contract-result-list', 'Admin\TaskController@getInspectionContractResultList');//检验结果合同列表
    Route::get('task/task-user', 'Admin\TaskController@getTaskUser');//获取验货用户
    Route::get('task/inspection-result-contract-list', 'Admin\TaskController@getInspectionResultContractList');//展示验货po结果列表
    Route::get('task/inspection-result-contract-sku-list', 'Admin\TaskController@getInspectionResultContractSkuList');//展示验货sku列表
    Route::get('task/inspection-result-sku-view', 'Admin\TaskController@getInspectionResultSkuView');//展示验货sku结果数据
    Route::get('task/sku-orn-view', 'Admin\TaskController@geSkuOrnView');//展示验货sku原始数据
    Route::get('task/sku-view', 'Admin\TaskController@geSkuView');//展示验货sku对比数据
    Route::get('task/test', 'Admin\TaskController@test');//检验结果任务列表
    Route::get('task/pic-same', 'Admin\TaskController@getPicSame');//读取图片
    Route::post('task/create-task', 'Admin\TaskController@postCreateTask');//创建任务
    Route::apiResource('task', 'Admin\TaskController');
    //进度控制器
    Route::get('schedule/history', 'Admin\ScheduleController@getHistory');//订单跟踪历史记录列表
    Route::get('schedule/history-view', 'Admin\ScheduleController@getHistoryView');//订单跟踪历史记录详情
    Route::get('schedule/contract-list', 'Admin\ScheduleController@getContractList');//订单跟踪列表
    Route::post('schedule/apply-inspection', 'Admin\ScheduleController@postApplyInspection');//申请验货post
    Route::get('schedule/apply-inspection-list', 'Admin\ScheduleController@getApplyInspectionList');//申请验货list
    Route::post('schedule/post-inspection-department', 'Admin\ScheduleController@postPostInspectionDepartment');//提交质检部
    Route::get('schedule/apply-department-list', 'Admin\ScheduleController@getApplyDepartmentList');//申请验货list
    Route::get('schedule/delay-track', 'Admin\ScheduleController@setDelayTrack');//延迟跟踪
    Route::get('schedule/set-track', 'Admin\ScheduleController@setTrack');//恢复跟踪
    Route::get('schedule/set-track-all', 'Admin\ScheduleController@setTrackAll');//批量恢复跟踪
    Route::get('schedule/schedule-isneed', 'Admin\ScheduleController@getScheduleIsNeed');//
    Route::post('schedule/update_schedule-isneed', 'Admin\ScheduleController@updateScheduleIsNeed');//


    Route::post('inspection/create_inspection_batch', 'Admin\InspectionController@create_inspection_batch');//添加验货批次
    Route::get('inspection/get_inspection_groups', 'Admin\InspectionController@get_inspection_groups');//获得验货批次列表
    Route::post('inspection/distribute_inspections', 'Admin\InspectionController@distribute_inspections');//分配验货
    Route::get('inspection/inspections_group_list', 'Admin\InspectionController@inspections_group_list');//分配验货列表
    Route::get('inspection/inspections_group', 'Admin\InspectionController@inspections_group');//审核验货列表
    Route::post('inspection/edit_inspections_group_name', 'Admin\InspectionController@edit_inspections_group_name');//修改组名
    Route::get('inspection/edit_inspections_group', 'Admin\InspectionController@edit_inspections_group');//修改组功能展示界面
    Route::post('inspection/store_inspections_group', 'Admin\InspectionController@store_inspections_group');//修改组数据



    Route::apiResource('schedule', 'Admin\ScheduleController');
    //上传文件
    Route::post('file/upload', 'Admin\FileController@postUpload');
    //swagger


});

