<?php
namespace Admin\Controller;
use Think\Controller;
class OptionController extends Controller {

	public function index(){

		$option = M('option');

		//网站信息
		$res = $option->where('optionType="site"')->select();
		foreach($res as $k => $v){
			$site[$v['optionKey']] = $v['optionValue'];
		}
		$this->assign('site',$site);

		if(I('post.')){
	
			$option->where("optionType='site'")->delete();

			foreach(I('post.') as $k => $v){
				$option->optionType  = 'site';
				$option->optionKey   = $k;
				$option->optionValue = $v;
				$option->add();
			}

			//文件存在判断
			if(!empty($_FILES["attchment"]["name"]) && is_uploaded_file($_FILES["attchment"]["tmp_name"])){

				$upload = new \Think\Upload();// 实例化上传类
			    $upload->maxSize   = 3145728 ;// 设置附件上传大小
			    $upload->exts      = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
			    $upload->autoSub   = false;//去除子目录
			    $upload->rootPath  = 'upload/wxcode/'; // 设置附件上传根目录
    			//$upload->savePath  = ''; // 设置附件上传（子）目录
			    // 上传文件 
			    $info = $upload->upload();

			    if($info) {

			    	//删除旧图
					@unlink($site['wxcode']);

					$option->optionType  = 'site';
					$option->optionKey   = 'wxcode';
					$option->optionValue = $upload->rootPath.$info['attchment']['savename'];
					$option->add();

			    }

			}

			redirect(U('Option/index'), 1, '页面跳转中...');
			
		}

		$this->display();

	}

	public function delfile(){

		$option = M('option');
		$site = $option->field('optionValue')->where("optionType='site' AND optionKey='wxcode'")->find();

		@unlink($site['optionValue']);

		$option->optionValue = '';
		$bool = $option->where("optionType='site' AND optionKey='wxcode'")->delete();

		if(!$bool) {
	        $this->error('删除失败！');
	    }else{
	        $this->success('删除成功！');
	    }

	}


}