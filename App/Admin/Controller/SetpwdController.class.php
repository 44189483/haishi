<?php
namespace Admin\Controller;
use Think\Controller;
class SetpwdController extends Controller {

	public function __construct(){

        parent::__construct();

        //未登录
        if(empty(I('session.admin'))){
    		redirect(U('Index/login'), 3, '请登陆,页面跳转中...');
    	}

    }

	public function index(){

		$admin = I('session.admin');

		$this->assign('admin',$admin);

		$this->display();

	}

	public function changepwd(){

		$option = M('option');

		$admin = I('session.admin');

		$pwd = I('post.pwd','','md5');

		$option->optionValue = $pwd;

		$bool = $option->where("optionType='AdminContrl' AND optionKey='{$admin}'")->save();

		if($bool) {
            $this->success('操作成功！',U('Setpwd/index'));
        }else{
            $this->error('操作错误！');
        }

	}

}