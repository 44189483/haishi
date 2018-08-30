<?php
namespace Admin\Controller;
use Think\Controller;
class AjaxController extends Controller {

	public function input(){

		if (IS_POST){

			$table = I('post.table');//表

			$filedId = I('post.filedId');//字段ID

			$id = I('post.id');//ID值

			$filed = I('post.filed');//要更新的字段

			$value = I('post.value');//值

			if(!empty($id)){

				$m = M($table);

				$m->$filed = $value;

				$bool = $m->where("{$filedId}={$id}")->save();

				if($bool){
					$data = '1';
				}else{
					$data = '0';
				}

				$this->ajaxReturn($data);

			}		

		}else{
			$this->error('非法请求');
		}

	}

	public function checkbox(){

		if (IS_POST){

			$table = I('post.table');//表

			$filedId = I('post.filedId');//字段ID

			$id = I('post.id');//ID值

			$filed = I('post.filed');//要更新的字段

			if(!empty($id)){

				echo 'a';

				$m = M($table);

				$row = $m->where("{$filedId}={$id}")->find();

				$val = $row[$filed] == 0 ? 1 : 0;

				$m->$filed = $val;

				$m->where("{$filedId}={$id}")->save();

				//M($table)->_sql();

			}		

		}else{
			$this->error('非法请求');
		}

	}

}