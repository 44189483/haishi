<?php
namespace Home\Controller;
use Think\Controller;
class WxController extends Controller {

    public function __construct(){

        parent::__construct();

        //实例化微信配置
        $Wx = D('Option');
        $wxcon = $Wx->wxconfig();

        //微信用户用到
        $this->wxchat = new \Org\Util\Wechat($wxcon['appid'], $wxcon['appsecret']);

    }

    public function index(){


    }

    //获取微信用户信息
    public function getuserinfo(){

        $res = $this->wxchat->getAccessToken('code',I('get.code'));
        
        session('openid',$res['openid'],3600*24);//1天
        
        $openid = session('openid');
        
        $member = M('member');

        $row = $member->where("openid='{$openid}'")->find();

        if(!$row){
			//用户不存在
			
            $user = $this->wxchat->getUserInfo($openid); 
            
            if($user){

                $row['openid'] = $openid;
                $row['wxnick'] = $user['nickname'];
                $row['avatar'] = $user['headimgurl'];
				$row['sex'] = $user['sex'];
				$row['city'] = $user['city'];
				$row['province'] = $user['province'];
				$row['country'] = $user['country'];
                $row['status'] = 0;//非会员标记

            }

        }

        //设置session
        session('member',$row);

        if(isset($_SERVER['HTTP_REFERER'])) {
            $url = $_SERVER['HTTP_REFERER'];
        }else{
            $url = "http://hshy.widonet.com/";
        }
 
        header("Location:{$url}");

        exit();

    }

    //微信回调
    public function notify(){
        
        $xml = $GLOBALS['HTTP_RAW_POST_DATA']; 

        $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $xml);
                        
        //将服务器返回的XML数据转化为数组  
        $data = json_decode(json_encode(simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA)),true);  
        //$data = xmlToArray($xml);  
        // 保存微信服务器返回的签名sign  
        $data_sign = $data['sign'];  
        // sign不参与签名算法  
        unset($data['sign']);  
        $sign = $this->makeSign($data);  
          
        // 判断签名是否正确  判断支付状态  
        if ( ($sign === $data_sign) && ($data['return_code'] == 'SUCCESS') && ($data['result_code'] == 'SUCCESS') ) {  
            
            $result = true; 

            // foreach ($data as $key => $val) {
            //     fwrite($myfile, "{$key} => $val <br/>");
            // }
            // 
            $where = "openid='{$data['openid']}' AND out_trade_no='{$data['out_trade_no']}'";
             
            //更新订单 
            $order = M('order');

            $row = $order->where($where)->find();

            if($row){

                $order->result_code = $data['result_code'];
                $order->return_code = $data['return_code'];
                $order->time_end = $data['time_end'];
                $order->transaction_id = $data['transaction_id'];
                $order->total_fee = $data['total_fee'];
                $order->isSignUp = 1;
            
                $order->where($where)->save();

            }

            //更新会员
            if($row['type'] == 1){
                
                $member = M('member');
                $member->status = 1;
                $member->where("openid='{$data['openid']}'")->save();

                session('member.status',1);
            }            

        }else{  
            $result = false;  
        }  
        // 返回状态给微信服务器  
        if ($result) {  
            $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';  
        }else{  
            $str='<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';  
        }  

        fclose($myfile);
  
        return $result;  
        
    }
    
    /** 
    * 生成签名 
    * @return 签名，本函数不覆盖sign成员变量 
    */ 
    protected function makeSign($data){  
        //获取微信支付秘钥  
        Vendor("WxPay.lib.WxPay#Api");
        $key = \WxPayConfig::KEY;  
        // 去空  
        $data=array_filter($data);  
        //签名步骤一：按字典序排序参数  
        ksort($data);  
        $string_a=http_build_query($data);  
        $string_a=urldecode($string_a);  
        //签名步骤二：在string后加入KEY  
        //$config=$this->config;  
        $string_sign_temp=$string_a."&key=".$key;  
        //签名步骤三：MD5加密  
        $sign = md5($string_sign_temp);  
        // 签名步骤四：所有字符转为大写  
        $result=strtoupper($sign);  
        return $result;  
    } 

}