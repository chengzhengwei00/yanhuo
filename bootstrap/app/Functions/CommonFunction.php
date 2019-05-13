<?php
//无限级分类格式化
function get_tree_child($data, $fid=0,$pid='pid')
{
    $res=array();
    $fids = array($fid);
    $level=0;
    do{
        $flag = false;
        $cids = array();
        $result=array();
        foreach($fids as $fid) {

            foreach($data as $v)
            {
                if(isset($v[$pid]) && $v[$pid]==$fid)
                {
                    $v['level']=$level;
                    $result[]=$v;
                    $cids[] = $v['id'];
                    $flag = true;

                }
            }

        }
        $level++;
        if(!empty($result))$res[]=$result;
        $fids = $cids;
    }while($flag === true);
    return $res;
    $count=count($res);
    if($count==1) return current($res);
    $final=array();
    $ini=$res[$count-1];
    //$ini=$res[$count-2];//去除最后一个
    for($i=$count-2;$i>=0;$i--) {
        $reset = array();
        foreach ($res[$i] as $value1) {

            foreach ($ini as $value2) {
                if ($value1['id'] == $value2[$pid]) {
                    if(is_array($value1)) {
                        $value1['child'][] = $value2;
                    }else {
                        $value1->child = $value2;
                    }

                }
            }
            $reset[] = $value1;
            $final = $reset;
        }
        $ini=$reset;
    }

        return $final;

}
//curl请求
function curl($url, $params = false, $ispost = 0, $https = 0)
{
    $httpInfo = array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($https) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    }
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }

    $response = curl_exec($ch);

    if ($response === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
    curl_close($ch);
    return $response;
}
function DifferWeek($from,$to)
{
    if(!is_numeric($from)){
        $from=strtotime($from);
    }
    if(!is_numeric($to)){
        $to=strtotime($to);
    }
    return ($to-$from)<0 ? 0:ceil(($to-$from)/(7*3600*24));
}
function DifferDay($from,$to)
{
    if(!is_numeric($from)){
        $from=strtotime($from);
    }
    if(!is_numeric($to)){
        $to=strtotime($to);
    }
    return ($to-$from)<0 ?0:ceil(($to-$from)/(3600*24));
}
function str_date($date)
{
    return date('Y-m-d H:i:s',preg_replace('/\D/','',$date)/1000);
}
?>