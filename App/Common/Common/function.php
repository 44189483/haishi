<?php
/**
* 方法
* @authors Sunguoliang
* @date    2016-11-09 09:10:00
 * @version 1.0
*/

/*
* 创建文件夹
* 方法:create_folders
* 参数:
*   $dir - 字符串 必填   
* 返回值:
*   布尔值
*/
function create_folders($dir){
    return is_dir($dir) or (create_folders(dirname($dir)) and mkdir($dir, 0777));
}

/*
* 检查数组是否为空
* 方法:checkArray
* 参数:
*   $array - 数组 必填  
* 返回值:
*   布尔值
*/
function checkArray($array){

    foreach ($array as $value){
        if(is_array($value)){
            if(count($value)){
                if(!checkArray($value)){
                    return false;
                }
            }
        }else{
            $value=trim($value);
            if(!empty($value)){
                return false;
            }
        }
        $i++;
    }
    return true;

}

/*
* smart模版专用 清除HTML标签及其中的图片
* 方法 clearHtml
* 参数:无
* 返回值:
*   无 - 字符串
* 用法:
* $smarty->registerPlugin('function','自定义名称','clearHtml');
*/
function clearHtml($params){

    extract($params);//smart模版专用

    $str = preg_replace('/[&nbsp;]/', '', $str);

    $str = preg_replace('/<img[^>]+>/i','',$str);

    return $str;

} 

/*
* 邮件发送(需要配合邮件发送类 class.phpmailer.php)
* 方法 sendEmail
* 参数:
*   $spend_from      - 发件邮箱
*   $spend_name      - 发件人
*   $receive_email   - 收件邮箱
*   $receive_name    - 收件人
*   $receive_subject - 标题
*   $receive_content - 详情
* 返回值:
*   布尔值(成功/失败)
*/
function sendEmail($spend_from,$spend_name,$receive_email,$receive_name,$spend_subject,$spend_content){

    $mail             = new PHPMailer();

    $mail->IsHTML(true);

    $mail->CharSet    = "utf-8";

    $mail->From       = $spend_from;

    $mail->FromName   = $spend_name;

    $mail->Subject    = $spend_subject;

    $mail->MsgHTML($spend_content);

    $mail->AddAddress($receive_email, $receive_name);

    return $mail->Send();

}

/*
* 字符串截取
* 方法 msubstr
* 参数:
*   $str      - 原字符
*   $start    - 开始值
*   $length   - 长度
*   $charset  - 字符集
*   $suffix 
*   返回值:      - 字符
*/
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    $fix='';
    if(strlen($slice) < strlen($str)){
        $fix='...';
    }
    return $suffix ? $slice.$fix : $slice;
}

/* 
*发送GET请求方法 
*@param string $url URL 
*@param bool $ssl  是否为https协议 
*@return string   响应主体内容  
*/  
function request($url,$data=null){ 

    $curl = curl_init(); 

    curl_setopt($curl, CURLOPT_URL, $url);  

    //设定为不验证证书和host  
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);  

    if(!empty($data)){  
        curl_setopt($curl, CURLOPT_POST, true);  
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);  
    }  

    // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出  
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);  

    $output = curl_exec($curl);  
    if (false === $output) {  
        echo "<br/>",curl_error($curl),"<br/>";  
        return false;  
    } 

    curl_close($curl);

    return $output; 

} 

function trimall($str){  
    $reg = array(" ","　","\t","\n","\r");  
    return str_replace($reg, '', $str);    
}

/** 
 * 创建(导出)Excel数据表格 
 * @param  array   $list 要导出的数组格式的数据 
 * @param  string  $filename 导出的Excel表格数据表的文件名 
 * @param  array   $header Excel表格的表头 
 * @param  array   $index $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值) 
 * 比如: $header = array('编号','姓名','性别','年龄'); 
 *       $index = array('id','username','sex','age'); 
 *       $list = array(array('id'=>1,'username'=>'YQJ','sex'=>'男','age'=>24)); 
 * @return [array] [数组] 
 */  
function exportExcel($list,$filename,$header=array(),$index = array()){    
    header("Content-type:application/vnd.ms-excel");    
    header("Content-Disposition:filename=".$filename.".xls");    
    $teble_header = implode("\t",$header);  
    $strexport = $teble_header."\r";  
    foreach ($list as $row){    
        foreach($index as $val){  
            $strexport.=$row[$val]."\t";     
        }  
        $strexport.="\r";   
  
    }    
    $strexport = iconv('UTF-8',"GB2312//IGNORE",$strexport);    
    exit($strexport);       
}   

?>