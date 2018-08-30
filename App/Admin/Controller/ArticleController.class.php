<?php
namespace Admin\Controller;
use Think\Controller;
class ArticleController extends Controller {

	public function __construct(){

        parent::__construct();

        //未登录
        if(empty($_SESSION['admin'])){
    		redirect(U('Index/login'), 3, '请登陆,页面跳转中...');
    	}

        $this->main = I('get.main');
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
    		default:
    			$this->error('参数有误！');
    			break;
    	}

    }

	public function index($main = null){

		//所属分类
    	$cid = I('get.cid');
		$this->assign('cid',$cid);
		if(!empty($cid)){
			$where = "classId={$cid}";
		}else{
			$where = $this->where;
		}

		//标题
		$title = I('get.title','','trim');
		$this->assign('title',$title);
		if(!empty($title)){
			$where .= " AND articleTitle LIKE'%{$title}%'";
		}

		$article = M('article'); // 实例化Data数据对象  date 是你的表名

	    $count = $article->where($where)->count();// 查询满足要求的总记录数 表示查询条件

	    $length = 10;

	    $Page = new \Think\Page($count,$length);// 实例化分页类 传入总记录数

	    $show = $Page->show();// 分页显示输出

	    // 进行分页数据查询
	    $rows = $article->where($where)->order('articleOrd DESC,addDate DESC,articleId DESC')->limit($Page->firstRow.','.$Page->listRows)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
	    foreach ($rows as $key => $val) {
	    	$rows[$key]['className'] = M('class')->where("classId={$val['classId']}")->getField('className');
	    }

	    //echo $article->_sql();
	    
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
			$article = M('article');
			$row = $article->where("articleId={$id}")->find(); 
			$this->assign('row',$row);
		}
		$this->display();
	}

	public function save(){

		$article = M('article');

		$id = I('post.id');

		$cid = I('post.cid');

		$ord = I('post.ord');

		$check = I('post.check') == '' ? 0 : 1;

		$title = I('post.title');

		$content = I('post.content');

		$vipPrice = I('post.vipPrice');

		$price = I('post.price');
		
		$date = I('post.date');

		//文件存在判断
		if(!empty($_FILES["attchment"]["name"]) && is_uploaded_file($_FILES["attchment"]["tmp_name"])){

			$upload = new \Think\Upload();// 实例化上传类
		    $upload->maxSize   = 5000000 ;// 设置附件上传大小
		    $upload->exts      = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		    $upload->rootPath  = 'upload/images/'; // 设置附件上传根目录
		    $upload->autoSub   = false;//去除子目录
		    // 上传文件 
		    $info = $upload->upload();

		    if($info && !empty($id)){//删除旧图
				$row = $article->where("articleId={$id}")->find(); 
				@unlink($row['articleAttach']);
		    }

		    $article->articleAttach = $upload->rootPath.$info['attchment']['savename'];

		}
		
		$article->classId = $cid;
		$article->articleType = 2;
		$article->articleOrd = $ord;
		$article->articleShow = $check;
		$article->articleTitle = $title;
		$article->articleContent = $content;
		$article->vipPrice = $vipPrice;
		$article->price = $price;
		$article->addDate = $date;
		
		if(empty($id)){
			$row = $article->where("classId={$cid} AND articleTitle='{$title}'")->find(); 
			if($row){
				$this->error('该信息已存在！');
			}
			$bool = $article->add();
		}else{
			$bool = $article->where("articleId={$id}")->save();
		}
		
		if($bool) {
            $this->success('操作成功！',U("Article/index",array('main'=>$this->main)));
        }else{
            $this->error('操作错误！');
        }

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