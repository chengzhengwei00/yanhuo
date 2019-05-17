<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Model\Contract;
use App\Http\Model\Standard;
use Illuminate\Support\Facades\DB;
use App\Http\Service\ContractService;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{

    public function __construct(Request $request)
    {
        $this->request=$request;
    }
    public function postUploadweb()
    {

        $task_id=$this->request->input('task_id');
        $contract_id=$this->request->input('contract_id');
        $sku=$this->request->input('sku');
        $num=$this->request->input('num');
        $type1=$this->request->input('type');
         $a=$this->request->all();
         $base64=$this->request->input('photo');
        $file = $this->request->file('photo');
        if ($fp = fopen($file, "rb", 0)) {
            $gambar = fread($fp, filesize($file));
            fclose($fp);
            $base64 = chunk_split(base64_encode($gambar));
            file_put_contents('a.jpg',base64_decode($base64));
            return $base64;
        }

        if($base64) {
            //return '1111';
            if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
                $type=$result[2];
                if (in_array($type, array('pjpeg', 'jpeg', 'jpg', 'gif', 'bmp', 'png'))) {
                    $img = base64_decode(str_replace($result[1], '', $base64));
                    $filename = $task_id.'_'.$contract_id.'_'.$sku.'_'.$type1.'_'.$num . '.'.$type;
                    $bool = Storage::disk('public')->put($filename, $img);
                    //判断是否上传成功
                    if ($bool) {
                        return ['status' => 1, 'message' => '上传成功'];
                    } else {
                        return ['status' => 0, 'message' => '上传失败'];
                    }
                }
            }
        }
    }

    public function postUpload()
    {
        $task_id=$this->request->input('task_id');
        $contract_id=$this->request->input('contract_id');
        $sku=$this->request->input('sku');
        $parent_sku=$this->request->input('parentSku');
        $type1=$this->request->input('type');
        //return $this->request->all();
        if ($this->request->hasFile('photo') && $this->request->file('photo')->isValid()) {
            $file = $this->request->file('photo');
            //原文件名
            $originalName = $file->getClientOriginalName();
            //扩展名
            $ext = $file->getClientOriginalExtension();
            //MimeType
            $type = $file->getClientMimeType();
            //临时绝对路径
            $realPath = $file->getRealPath();
            //echo Storage::disk('root');die;
            if($parent_sku=='') {
                $filename = $task_id . '/' . $contract_id . '/' . $sku . '/' . $type1 . '/' . date('Y-m-d_H-i-s').'_'.rand(9, 1000) . '.' . $ext;
            }else{
                $filename = $task_id . '/' . $contract_id . '/' . $parent_sku . '/'.$sku.'/'.$type1 . '/' . date('Y-m-d_H-i-s').'_'.rand(9, 1000) . '.' . $ext;
            }
            if (!file_exists($file_dir=dirname(storage_path('app/public/').$filename))){
                mkdir ($file_dir,0777,true);
            }
            $bool = Storage::disk('public')->put($filename,file_get_contents($realPath));
            //判断是否上传成功
            if($bool){
                return ['status'=>1,'message'=>'上传成功'];
            }else{
                return ['status'=>0,'message'=>'上传失败'];
            }
        }
    }
    public function postUpload64()
    {
        if ($this->request->hasFile('photo') && $this->request->file('photo')->isValid()) {
            //$base64 = preg_replace("/\s/",'+',$request->input('img'));
            $file = $this->request->file('photo');
            if ($fp = fopen($file, "rb", 0)) {
                $gambar = fread($fp, filesize($file));
                fclose($fp);
                $base64 = chunk_split(base64_encode($gambar));
                echo $base64;
                die;
                $img = base64_decode($base64);
                $filename = uniqid() . '.png';
                $bool = Storage::disk('public')->put($filename, $img);
                //判断是否上传成功
                if ($bool) {
                    return ['status' => 1, 'message' => '上传成功'];
                } else {
                    return ['status' => 0, 'message' => '上传失败'];
                }
            }
        }

    }
}
