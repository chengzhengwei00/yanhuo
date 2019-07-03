<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Model\Contract;
use App\Http\Model\Standard;
use Illuminate\Support\Facades\DB;
use App\Http\Service\ContractService;

class ContractController extends Controller
{

    public function __construct(ContractService $contractService,Request $request)
    {
        $this->contractService=$contractService;
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
        return $this->contractService->list();

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return json_encode(array(1,1));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        return json_encode(array(2,2));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        return json_encode(array(2,2));
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    //获取合同
    /**
     * @SWG\Get(path="/api/v1/contracts/get-contracts-for-api",
     *   tags={"获取合同"},
     *   summary="从宁波api获取合同",
     *   description="从宁波api获取合同。",
     *   operationId="getContractsForApi",
     *   produces={"application/json"},
     *   @SWG\Response(response="default", description="操作成功"),
     *   @SWG\Response(
     *     response=200,
     *     description="合同列表"
     *   ),
     * )
     */
    public function getContractsForApi( )
    {
        return $this->contractService->get_contract_by_api();
    }
    public function geUpdateContractStatus()
    {
        return $this->contractService->update_contract_status();
    }

    public function get_manage_list(ContractService $contractService){
        return $contractService->get_manage_list();
    }


}
