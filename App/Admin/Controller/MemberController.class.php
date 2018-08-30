<?php
namespace Admin\Controller;
use Think\Controller;
class MemberController extends Controller {

	public function __construct(){

        parent::__construct();

        //未登录
        if(empty($_SESSION['admin'])){
    		redirect(U('Index/login'), 3, '请登陆,页面跳转中...');
    	}

    }

	public function index($main = null){

		$member = M('member'); // 实例化Data数据对象  date 是你的表名

		$where = "status=1";

	    $count = $member->where($where)->count();// 查询满足要求的总记录数 表示查询条件

	    $length = 10;

	    $Page = new \Think\Page($count,$length);// 实例化分页类 传入总记录数

	    $show = $Page->show();// 分页显示输出

	    // 进行分页数据查询
	    $rows = $member->where($where)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
	    
	    $this->assign('rows',$rows);// 赋值数据集

	    $this->assign('page',$show);// 赋值分页输出

	    $this->assign('nav',$this->nav);// 栏目

	    $this->assign('select',$this->select);// 下拉选项

	    $this->display(); // 输出模板

	}

	public function view(){

		$id = I('get.id');

		if(!empty($id)){
			if(!is_numeric($id)){
				$this->error('参数有误！');
			}
			$member = M('member');
			$row = $member->where("articleId={$id}")->find(); 
			$this->assign('row',$row);
		}
		$this->display();
	}

	public function del(){

		$article = M('article');

		$pid = I('post.id');

		$gid = I('get.id');

		if($pid){//多删

			$rows = $article->where("articleId IN(".implode(',', $pid).")")->select(); 

			foreach ($rows as $k => $v) {
				@unlink($v['articleAttach']);
			}

			$bool = $article->where("articleId IN(".implode(',', $pid).")")->delete(); 

		}else if (!empty($gid)) {//单删
			$row = $article->field('articleAttach')->where("articleId={$gid}")->find();
			//删除图片
			@unlink($row['articleAttach']);
			$bool = $article->where("articleId={$gid}")->delete();
		}
		
		if($bool) {
            $this->success('操作成功！');
        }else{
            $this->error('操作错误！');
        }

	}

}