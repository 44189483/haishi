<?php
namespace Home\Controller;
use Think\Controller;
class ArticleController extends Controller {

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

        //获取会员信息
        $member = session('member');
        $this->assign('member',$member);

        $this->main = I('get.main');
        if(!empty($this->main) && !is_numeric($this->main)){
            $this->error('参数有误！');
        }
        $this->assign('main',$this->main);

        switch ($this->main) {
            case 1:
                //公益赛事 山地赛事
                $this->nav = '赛事';
                $this->select = array(
                    '1' => '公益赛事',
                    '2' => '山地赛事'
                );
                $this->where .= " classId IN (1,2)";
                break;
            case 2:
                //徒步露营 藏地旅行 境外旅行
                $this->nav = '户外';
                $this->select = array(
                    '3' => '徒步露营',
                    '4' => '藏地旅行',
                    '5' => '境外旅行'
                );
                $this->where .= " classId IN (3,4,5)";
                break;
            case 3:
                //救援培训 亲子攀岩
                $this->nav = '培训';
                $this->select = array(
                    '6' => '救援培训',
                    '7' => '亲子攀岩'
                );
                $this->where .= " classId IN (6,7)";
                break;
            case 4:
                //美丽公约 公益助学
                $this->nav = '公益';
                $this->select = array(
                    '8' => '美丽公约',
                    '9' => '公益助学'
                );
                $this->where .= " classId IN (8,9)";
                break;
        }

        $this->assign('nav',$this->nav);

        //翻页数
        $this->length = 5;

    }

    //首页
    public function index(){

    	//分类
    	$getcid = I('get.cid');
    	$cid = !empty($getcid) ? $getcid : key($this->select);
    	$where = "classId={$cid}";
		
    	$article = M('article');
        
		//文章信息
		$rows = $article->order('articleOrd DESC,addDate DESC,articleId DESC')->where($where)->limit("0,{$this->length}")->select();
        //查询已报名
        if($this->main == 1 || $this->main == 2){
            foreach ($rows as $key => $val) {
                $mid = session('member.id');
                if(!empty($mid)){
                    $res = M('order')->where("aid={$val['articleId']} AND mid={$mid}")->find(); 
                }
                $rows[$key]['signed'] = $res == true ? 1 : 0;
            }
        }
		$this->assign('rows',$rows);

		$this->assign('cid',$cid);

		$this->assign('select',$this->select);

        $this->assign("title","泉州海丝户外运动俱乐部");

		$this->display('./article');
		
    }

    //瀑布流数据
    public function ajaxdata(){

    	//分类
    	$getcid = I('get.cid');
    	$cid = !empty($getcid) ? $getcid : key($this->select);
    	$where = "classId={$cid}";
		
    	$article = M('article');

	    $count = $article->where($where)->count();// 查询满足要求的总记录数 表示查询条件

	    $Page = new \Think\Page($count,$this->length);// 实例化分页类 传入总记录数

	    $show = $Page->show();// 分页显示输出

	    $offset = ($Page->firstRow + 1) * $Page->listRows;

	    // 进行分页数据查询
	    $rows = $article->where($where)->order('articleOrd DESC,addDate DESC,articleId DESC')->limit($offset.','.$Page->listRows)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条

	    foreach ($rows as $key => $val) {

	    	//转化HTML实体
	    	$content = htmlspecialchars_decode($val['articleContent']);

	    	if($this->main != 1 && $this->main != 3){

		    	//去除HTML标签
		    	$content = strip_tags($content);

		    	//截取
		    	$content = msubstr($content,0,40,'utf-8');

		    }

	    	$rows[$key]['articleContent'] = $content;

            //查询是否已报名
            $mid = session('member.id');
            if(!empty($mid)){
                $res = M('order')->where("aid={$val['articleId']} AND mid={$mid}")->find(); 
            }
            $rows[$key]['signed'] = $res == true ? 1 : 0;

	    }

        if($rows){
            echo $json = json_encode($rows);
        }else{
            echo 0;
        }
    	
    }

    //详情
    public function detail(){

    	$id = I('get.id');
    	$this->assign('id',$id);
		if(empty($id) || !is_numeric($id)){
			$this->error('参数有误！');
		}

		$article = M('article');
		$row = $article->where("articleId={$id}")->find(); 
		$this->assign('row',$row);
		
		if($this->main == 3){

			//相关分类下的视频集
			$rows = $article->where("classId={$row['classId']}")->order('articleOrd DESC,addDate DESC,articleId DESC')->limit('0,3')->select(); 
			$this->assign('rows',$rows);

		}

        $this->assign("title",$row['articleTitle']);

        //分享内容处理
        $desc = trimall(msubstr(strip_tags(htmlspecialchars_decode($row['articleContent'])), 0, 100));

        $this->assign("desc",$desc);

        

		$this->display('./article-detail');

    }

    //活动报名
    public function signup(){

        //活动ID
        $id = I("post.id");
        if(empty($id)){
            echo '活动不存在';
            exit();
        }

        $openid = session('openid');
		
        $signup = M('order');
        $row = $signup->where("aid={$id} AND openid='{$openid}' AND isSignUp=1")->find(); 
        if($row){
            echo '您已经报过名了';
            exit();
        }

    }

}