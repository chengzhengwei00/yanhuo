<?php

namespace App\Http\Service;

use Illuminate\Http\Request;
use App\Http\Model\Standard;

class StandardService
{
    
    public function list(Request $request)
    {
        $contract_id=$request->input('contract_id');
        return Standard::where('contract_id', $contract_id)->get()->toJson();
    }

}
