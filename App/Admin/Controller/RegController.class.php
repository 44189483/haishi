<?php
namespace Admin\Controller;
use Think\Controller;
class RegController extends Controller {

	public function index(){

		$option = M('option');

		$where = 'optionType="register"';

		//网站信息
		$res = $option->where($where)->find();
		$this->assign('res',$res);

		if(I('post.')){

			$option->optionValue = I('post.cost');
	
			$option->where($where)->save();

			redirect(U('Reg/index'), 1, '页面跳转中...');
			
		}

		$this->display();

	}

}