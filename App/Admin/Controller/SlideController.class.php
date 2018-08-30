<?php
namespace Admin\Controller;
use Think\Controller;
class SlideController extends Controller {

	public function __construct(){

        parent::__construct();

        //未登录
        if(empty(I('session.admin'))){
    		redirect(U('Index/login'), 3, '请登陆,页面跳转中...');
    	}

    }

	public function index($sid=0){

		$slide = M('img');

		if(!empty($sid)){
			$row = $slide->find($sid); 
			$this->assign('row',$row);
		}

		$this->assign('sid',$sid);

		$rows = $slide->order('ord DESC,id DESC')->select();

		$this->assign('rows',$rows);

		$this->display();

	}

	public function save($sid=''){

		$slide = M('img');

		$row = $slide->field('url')->find($sid);

		//文件存在判断
		if(!empty($_FILES["attchment"]["name"]) && is_uploaded_file($_FILES["attchment"]["tmp_name"])){

			$upload = new \Think\Upload();// 实例化上传类
		    $upload->maxSize   = 5000000 ;// 设置附件上传大小
		    $upload->exts      = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		    $upload->autoSub   = false;//去除子目录
		    $upload->rootPath  = 'upload/slide/'; // 设置附件上传根目录
		    // 上传文件 
		    $info = $upload->upload();

		    if($info) {
		    	@unlink($row['url']);//删除旧图
				$filepath = $upload->rootPath.$info['attchment']['savename'];				
		    }

		}else{
			$filepath = I('post.img');
		}

		$slide->title  = I('post.title');
		$slide->link  = I('post.link');
		$slide->ord  = I('post.ord');
		$slide->url = $filepath;

		if(!empty($sid)){
			$slide->where("id={$sid}")->save();
			$slide->getLastSql();
		}else{
			$slide->add();
		}
		redirect(U('Slide/index'), 1, '页面跳转中...');

	}

	public function del($sid){

		$slide = M('img');
		$row = $slide->field('url')->find($sid);

		@unlink($row['url']);

		$bool = $slide->delete($sid);

		if(!$bool) {
	        $this->error('删除失败！');
	    }else{
	        $this->success('删除成功！',U('Slide/index'));
	    }

	}

}