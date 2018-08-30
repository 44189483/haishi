<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends Controller {

    public function __construct(){

        parent::__construct();

        //实例化微信配置
        $Wx = D('Option');
        $wxcon = $Wx->wxconfig();
		
        //会员自动登录
        if(empty(session('openid'))){

            $request_url = "http://hshy.widonet.com/index.php/Home/Wx/getuserinfo";

            $this->wxchat = new \Org\Util\Wechat($wxcon['appid'], $wxcon['appsecret']);

            $url = $this->wxchat->getRequestCodeURL($request_url);

            header("Location:{$url}");

            exit();

        }

        $jssdk = new \Org\Util\JSSDK($wxcon['appid'],$wxcon['appsecret']);

        $signPackage = $jssdk->GetSignPackage();

        $this->assign("signPackage",$signPackage);

        //微信用户用到
        $this->wxchat = new \Org\Util\Wechat($wxcon['appid'], $wxcon['appsecret']);

        $this->assign("title","泉州海丝户外运动俱乐部");

        //当前控制器名称
        $this->assign('cname',CONTROLLER_NAME); 

        //获取会员信息
        $member = session('member');
        $this->assign('member',$member);

    }

    //会员主页面
    public function index(){

        $this->display('./user');
        
    }

    public function single(){

        $tid = I('get.tid');

        if(empty($tid) || !is_numeric($tid)){
            $this->error('参数有误！');
        }

        //分类:4.关于海丝5.会员制度6.会费详情
        switch ($tid) {
            case 4:
                $nav = '关于海丝';
                break;
            case 5:
                $nav = '会员制度';
                break;
            case 6:
                $nav = '会费详情';
                break;
            default:
                $this->error('参数有误！');
                break;
        }
        $this->assign('nav',$nav);

        $article = M('article');

        $row = $article->field("articleType,articleContent")->where("articleType={$tid}")->find(); 

        $this->assign('row',$row);

        $this->display('./user-single');

    }

    //通知页面
    public function notice(){

        $article = M('article');

        $rows = $article->field("articleTitle,articleContent,addDate")->where("articleType=1")->order('articleOrd DESC,addDate DESC,articleId DESC')->select();

        $this->assign('rows',$rows);

        $this->display('./notice');

    }

    //用户信息 相关及活动报名 采用一个公共页
    public function info(){
		
        ini_set('date.timezone','Asia/Shanghai');

        $openid = session('openid');

        $out_trade_no = date('YmdHis').rand(0,100);

        $member = M('member');

        $row = $member->where("openid='{$openid}'")->find();
	        
        $aid = I('get.aid');
        if(!empty($aid)){

            //报名活动
            $article = M('article');
            $res = $article->where("articleId={$aid}")->field("articleTitle,price,vipPrice")->find();
			$this->assign('res',$res);

            //判断会员级别
            if(!$row){

                //会员不存在为原价
                if($res['price'] > 0){
                    $amount = $res['price'];
                }

            }else{

                //非正式会员
                if($row['status'] == 0){
                    if($res['price'] > 0){
                        $amount = $res['price'];
                    }
                }

                //正式会员
                if($row['status'] == 1){
                    if($res['vipPrice'] > 0){
                        $amount = $res['vipPrice'];
                    }
                }
            }
			
            //支付款大于0，调查微信支付
			if($amount > 0){

				$this->wxpay($amount,$res['articleTitle'],$out_trade_no);
			
			}

            //上一路径
            if(isset($_SERVER['HTTP_REFERER'])) {
                $url = $_SERVER['HTTP_REFERER'];
            }else{
                $url = "http://hshy.widonet.com/";
            }
            $this->assign('url',$url);
			
			$this->assign('amount',$amount);

        }else if(empty($aid) && $row['status'] == 0){

            //非会员 注册用
            $res = M('option')->where('optionType="register"')->find();

            //会员价
            $amount = $res['optionValue'];
			
			$this->assign('amount',$amount);

            $this->wxpay($res['optionValue'],'会员加入',$out_trade_no);

        }
		
        if($row['status'] == 1){
            $this->assign('row',$row);
        }
        
        $this->assign('aid',$aid);

        $this->assign('out_trade_no',$out_trade_no);

        $this->display('./user-info');

    }

    //更新/注册会员信息及订单信息
    public function saveinfo(){

        $openid = session('openid');

        $member = M('member');
        $row = $member->where("openid='{$openid}'")->find();

        $order = M('order');
        
        //活动ID
        $aid = I('post.aid');
        if(!empty($aid)){//报名活动

            $order->mid = $row['id'] == null ? 0 : $row['id'];
            $order->type = 2;
            $order->openid = $openid;
            $order->aid = I("post.aid");
            $order->realname = I("post.realname");
            $order->mobile = I("post.mobile");
            $order->card = I("post.card");
            $order->out_trade_no = I('post.out_trade_no');
            $order->createTime = date('Y-m-d H:i:s');
            $order->add();
                
        }else{//会员更新注册

            $member->realname = I("post.realname");
            $member->mobile = I("post.mobile");
            $member->card = I("post.card");

            
            $dd = $order->where("openid='{$openid}' AND type=1")->find();

            //会员存在并末支付
            if($row && $row['status'] == 0){

                if(!$dd){
                    //防止重复提交生成订单
                    $order->type = 1;
                    $order->openid = $openid;
                    $order->realname = I("post.realname");
                    $order->mobile = I("post.mobile");
                    $order->card = I("post.card");
                    $order->out_trade_no = I('post.out_trade_no');
                    $order->createTime = date('Y-m-d H:i:s');
                    $order->add();
                }

                $member->where("openid='{$openid}'")->save();

            }else if(!$row){

                //会员不存在
                $member->openid = $openid;
                $member->wxnick = session('member.wxnick');
                $member->avatar = session('member.avatar');
                $member->sex    = session('member.sex');
                $member->createTime = date('Y-m-d H:i:s');
            
                $mid = $member->add();

                if(!$dd){
                    //防止重复提交生成订单
                    $order->type = 1;
                    $order->mid = $mid;
                    $order->openid = $openid;
                    $order->realname = I("post.realname");
                    $order->mobile = I("post.mobile");
                    $order->card = I("post.card");
                    $order->out_trade_no = I('post.out_trade_no');
                    $order->createTime = date('Y-m-d H:i:s');
                    $order->add();
                }

            }else{
                $member->where("openid='{$openid}'")->save();
            }
            
        }

    }

    /** 
    * 微信支付 
    * @param decimal $amount - 价格
    * @param string  $name - 内容
    * @param string  $out_trade_no - 订单号
    */
    protected function wxpay($amount,$name,$out_trade_no){

        $openid = session('openid');

        Vendor("WxPay.lib.WxPay#Api");
        Vendor("WxPay.payment.WxPay#JsApiPay");
        Vendor("WxPay.payment.log");

        // //初始化日志
        // $logHandler= new \CLogFileHandler("/Libs/Library/Vendor/Wxpay/logs/".date('Y-m-d').'.log');
        // $log = \Log::Init($logHandler, 15);

        // //打印输出数组信息
        // function printf_info($data)
        // {
        //     foreach($data as $key=>$value){
        //         echo "$key : $value <br/>";
        //     }
        // }

        //①、获取用户openid
        $tools = new \JsApiPay();
        //$openId = $tools->GetOpenid();
        //
		$amount *= 100;
        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($name);
        //$input->SetAttach("test");
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($amount);//$amount * 100
        //$input->SetTime_start(date("YmdHis"));
        //$input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($name);
        $input->SetNotify_url("http://hshy.widonet.com/index.php/Home/Wx/notify");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openid);
        $order = \WxPayApi::unifiedOrder($input);
        //echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
        //printf_info($order);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        $this->assign('jsApiParameters',$jsApiParameters);
        
        //获取共享收货地址js函数参数
        $editAddress = $tools->GetEditAddressParameters();
        $this->assign('editAddress',$editAddress);

    }

}