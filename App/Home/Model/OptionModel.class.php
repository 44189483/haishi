<?php
namespace Home\Model;
use Think\Model;
class OptionModel extends Model {

	//获取微信详细配置
	function wxconfig(){

		$option = M('option');

        $wx = $option->where('optionType="wx"')->select();
        foreach ($wx as $key => $val) {
            $wx[$val['optionKey']] = $val['optionValue'];
        }

        return $wx;

	}

}
?>