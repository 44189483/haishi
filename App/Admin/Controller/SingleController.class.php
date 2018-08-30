<?php
namespace Admin\Controller;
use Think\Controller;
class SingleController extends Controller {

	public function __construct(){

        parent::__construct();

        //未登录
        if(empty($_SESSION['admin'])){
    		redirect(U('Index/login'), 3, '请登陆,页面跳转中...');
    	}

    }

	public function index(){

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

		$this->display();

	}

	public function save(){

		$article = M('article');

		$tid = I('post.tid');

		$content = I('post.content');

		$article->articleContent = $content;
		
		$bool = $article->where("articleType={$tid}")->save();
		
		if($bool) {
            $this->success('操作成功！',U("Single/index",array('tid'=>$tid)));
        }else{
            $this->error('操作错误！');
        }

	}

}