<?php
namespace Admin\Controller;
use Think\Controller;
class SignupController extends Controller {

	public function __construct(){

        parent::__construct();

        //未登录
        if(empty($_SESSION['admin'])){
    		redirect(U('Index/login'), 3, '请登陆,页面跳转中...');
    	}

    }

	public function index($aid){

		if(empty($aid) || !is_numeric($aid)){
			$this->error('参数有误！');
		}

		//所属活动
		$article = M('article');
		$row = $article->field('articleId,articleTitle,classId')->where("articleId={$aid}")->find(); 
		$this->assign('row',$row);

		if ($row['classId'] == 1 || $row['classId'] == 2) {
    		$main = 1;
    	}else if ($row['classId'] == 3 || $row['classId'] == 4 || $row['classId'] == 5) {
    		$main = 2;
    	}else if ($row['classId'] == 6 || $row['classId'] == 7) {
    		$main = 3;
    	}else if ($row['classId'] == 8 || $row['classId'] == 9) {
    		$main = 4;
    	}

		$signup = M('order'); // 实例化Data数据对象

	    $count = $signup->where("aid={$aid} AND type=2 AND isSignUp=1")->count();// 查询满足要求的总记录数 表示查询条件

	    $length = 10;

	    $Page = new \Think\Page($count,$length);// 实例化分页类 传入总记录数

	    $show = $Page->show();// 分页显示输出

	    $Model = M();

		$sql = "
			SELECT
				s.id,
				s.mid,
				m.avatar,
				m.wxnick,
				s.realname,
				s.mobile,
				s.card,
				FORMAT((s.total_fee / 100),2) AS total_fee,
				s.createTime
			FROM
				hs_order s
			INNER JOIN
				hs_article a
			ON
				s.aid=a.articleId
			LEFT JOIN
				hs_member m
			ON
				s.mid=m.id
			WHERE
					s.aid={$aid}
				AND
					s.type=2
				AND
					s.isSignUp=1
				ORDER BY s.createTime DESC,s.id DESC
				LIMIT {$Page->firstRow},{$Page->listRows}
	    "; 

	    $rows = $Model->query($sql);

	    $this->assign('aid',$aid);

	    $this->assign('count',$count);

	    $this->assign('rows',$rows);// 赋值数据集

	    $this->assign('page',$show);// 赋值分页输出

	    $this->assign('main',$main);

	    $this->display(); // 输出模板

	}

	public function del(){

		$signup = M('order');

		$pid = I('post.id');

		$gid = I('get.id');

		if($pid){//多删
			$bool = $signup->where("id IN(".implode(',', $pid).")")->delete(); 

		}else if (!empty($gid)) {//单删
			$bool = $signup->where("id={$gid}")->delete();
		}
		
		if($bool) {
            $this->success('操作成功！');
        }else{
            $this->error('操作错误！');
        }

	}

	//导出EXCEL
	public function excel(){

		$aid = I('post.aid');

		if(empty($aid) || !is_numeric($aid)){
			$this->error('参数有误！');
		}

		$article = M('article');
		$row = $article->field('articleTitle')->where("articleId={$aid} AND isSignUp=1")->find(); 

		$filename = $row['articleTitle'].'-活动报名记录';  
		$header = array('姓名','电话','身份证','实收金额','时间');  
		$index = array('realname','mobile','card','total_fee','createTime'); 
		$Model = M(); 
		$sql = "SELECT realname, mobile, card, FORMAT((total_fee / 100),2) AS total_fee, createTime FROM hs_order WHERE aid={$aid}";
		$rows = $Model->query($sql);
		exportExcel($rows,$filename,$header,$index);

	}

}