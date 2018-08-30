<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {

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

		$this->appid = $wxcon['appid'];
		$this->secret = $wxcon['appsecret'];
		$this->accesstoken = $wxcon['accesstoken'];
		$this->lasttime = $wxcon['lasttime'];

        $jssdk = new \Org\Util\JSSDK($this->appid,$this->secret);

		$signPackage = $jssdk->GetSignPackage();

		$this->assign("signPackage",$signPackage);

		$this->assign("title","泉州海丝户外运动俱乐部");

		//当前控制器名称
        $this->assign('cname',CONTROLLER_NAME); 

        //获取会员信息
        $member = session('member');
        $this->assign('member',$member); 

    }

    public function index(){ 

    	$this->wx(); 	

    	//当前控制器名称
        $this->assign('cname',CONTROLLER_NAME); 
        
        //轮播图
		$img = M('img');
		$banner = $img->order('ord DESC,id DESC')->limit(0,5)->select();
		$this->assign('banner',$banner);

		$article = M('article');

		//赛事信息
		$saishi = $article->order('articleOrd DESC,addDate DESC,articleId DESC')->where('classId IN(1,2)')->limit('0,3')->select();
		$this->assign('saishi',$saishi);

		//公益培训
		$peixun = $article->where('classId IN(6,7)')->order('articleOrd DESC,articleId DESC')->find();

		$this->assign('peixun',$peixun);

		$this->display('./index');
		
    }

    public function wx(){
    	
        if($this->lasttime + 6000 > time()){

        	$wxchat = new \Org\Util\Wechat($this->appid, $this->secret,$this->accesstoken);

        }else{

        	$option = M('option');

        	$wxchat = new \Org\Util\Wechat($this->appid, $this->secret);

        	$rows = $wxchat->getAccessToken();

	        $array = array(
	            'accesstoken' => $rows['access_token'],
	            'lasttime' => time()
	        );

	        // 更新 AccessToken
	        foreach($array as $k => $v){

	            $option->optionValue = $v;   
	            
	            $option->where("optionType='wx' AND optionKey='{$k}'")->save();

	        }
	    
	    }

	    //创建微信菜单
		$buttons = '
			{
			    "button": [
			        {
			            "name": "赛事活动", 
			            "sub_button": [
			                {
			                    "type":"view",
				                "name":"公益赛事",
					            "url":"http://hshy.widonet.com/index.php/Home/Article/index/main/1/cid/1.html"
			                }, 
			                {
			                    "type":"view",
				                "name":"山地赛事",
					            "url":"http://hshy.widonet.com/index.php/Home/Article/index/main/1/cid/2.html"
			                },
			                {
			                    "type":"view",
				                "name":"徒步露营",
					            "url":"http://hshy.widonet.com/index.php/Home/Article/index/main/2/cid/3.html"
			                },
			                {
			                    "type":"view",
				                "name":"藏地旅行",
					            "url":"http://hshy.widonet.com/index.php/Home/Article/index/main/2/cid/4.html"
			                },
			                {
			                    "type":"view",
				                "name":"境外旅行",
					            "url":"http://hshy.widonet.com/index.php/Home/Article/index/main/2/cid/5.html"
			                }
			            ]
			        },
			        {
			            "name": "公益培训", 
			            "sub_button": [
			                {
			                    "type":"view",
				                "name":"美丽公约",
					            "url":"http://hshy.widonet.com/index.php/Home/Article/index/main/4/cid/8.html"
			                }, 
			                {
			                    "type":"view",
				                "name":"公益助学",
					            "url":"http://hshy.widonet.com/index.php/Home/Article/index/main/4/cid/9.html"
			                },
			                {
			                    "type":"view",
				                "name":"救援培训",
					            "url":"http://hshy.widonet.com/index.php/Home/Article/index/main/3/cid/6.html"
			                },
			                {
			                    "type":"view",
				                "name":"亲子攀岩",
					            "url":"http://hshy.widonet.com/index.php/Home/Article/index/main/3/cid/7.html"
			                }
			            ]
			        },
			        {
			            "name": "会员服务", 
			            "sub_button": [
			                {
			                    "type":"view",
				                "name":"会员加入",
					            "url":"http://hshy.widonet.com/index.php/Home/User/info.html"
			                }, 
			                {
			                    "type":"view",
				                "name":"会员制度",
					            "url":"http://hshy.widonet.com/index.php/Home/User/single/tid/5.html"
			                },
			                {
			                    "type":"view",
				                "name":"关于海丝",
					            "url":"http://hshy.widonet.com/index.php/Home/User/single/tid/4.html"
			                },
			                {
			                    "type":"view",
				                "name":"户外保险",
					            "url":"http://suluotanxian.cps.m.51baoy.com/?from=singlemessage"
			                }
			            ]
			        }
			    ]
			}
		'; 

	    $wxchat->menuCreate($buttons);   
    	
    }

}