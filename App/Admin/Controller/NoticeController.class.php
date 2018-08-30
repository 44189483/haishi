<?php
namespace Admin\Controller;
use Think\Controller;
class NoticeController extends Controller {

	public function __construct(){

        parent::__construct();

        //未登录
        if(empty($_SESSION['admin'])){
    		redirect(U('Index/login'), 3, '请登陆,页面跳转中...');
    	}

    }

	public function index(){


		$where = "articleType=1";

		$article = M('article'); // 实例化Data数据对象  date 是你的表名

	    $count = $article->where($where)->count();// 查询满足要求的总记录数 表示查询条件

	    $length = 10;

	    $Page = new \Think\Page($count,$length);// 实例化分页类 传入总记录数

	    $show = $Page->show();// 分页显示输出

	    // 进行分页数据查询
	    $rows = $article->where($where)->order('articleOrd DESC,addDate DESC,articleId DESC')->limit($Page->firstRow.','.$Page->listRows)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条

	    //echo $article->_sql();
	    
	    $this->assign('rows',$rows);// 赋值数据集

	    $this->assign('page',$show);// 赋值分页输出

	    $this->display(); // 输出模板

	}

	public function view($id=''){

		if(!empty($id)){
			$article = M('article');
			$row = $article->where("articleId={$id}")->find(); 
			$this->assign('row',$row);
		}
		$this->display(); 
	}

	public function save(){

		$act = I('get.act');

		$id = I('post.id');

		$article = M('article');

		$title = I('post.title');
		$content = I('post.content','',false);
		$date = I('post.date');
		$check = I('post.check') == 1 ? 1 : 0;
		$ord = I('post.ord');

		$article->articleTitle = $title;
		$article->articleType = 1;
		$article->articleContent = $content; 
		$article->articleShow = $check;
		$article->articleOrd = $ord;
		$article->addDate = $date; 

		if($act == 'add'){
			$bool = $article->add();
		}elseif($act == 'edit'){
			$bool = $article->where("articleId={$id}")->save();
		}

		if($bool) {
            $this->success('操作成功！',U('Notice/index'));
        }else{
            $this->error('操作错误！');
        }

	}

	public function del(){

		$article = M('article');

		$pid = I('post.id');

		$gid = I('get.id');

		if($pid){//多删

			//删除会员查看记录 
			M('checknotice')->where("nid IN(".implode(',', $pid).")")->delete(); 
			
			$bool = $article->where("articleId IN(".implode(',', $pid).")")->delete(); 

		}else if (!empty($gid)) {//单删

			//删除会员查看记录 
			M('checknotice')->where("nid={$gid}")->delete();
			
			$bool = $article->where("articleId={$gid}")->delete();
			
		}
		
		if($bool) {
            $this->success('操作成功！');
        }else{
            $this->error('操作错误！');
        }

	}

}