<?php
return array(
	'DEFAULT_CHARSET' => 'utf-8', // 默认输出编码
	'OUTPUT_CHARSET' =>'UTF8', // 默认输出编码
	'DB_TYPE' => 'mysql',// 数据库类型 
	'DB_HOST' => '127.0.0.1',// 服务器地址 
	'DB_NAME' => 'db_haishi',// 数据库名 
	'DB_USER' => 'root',// 用户名 
	'DB_PWD' => 'root',// 密码 
	'DB_PORT' => 3306,// 端口 
	'DB_PREFIX' => 'hs_',// 数据库表前缀 
	'DB_CHARSET' => 'utf8',// 数据库字符集 
	'DB_PARAMS' => array(PDO::ATTR_CASE => PDO::CASE_NATURAL),//字段强制转换小写
	'TMPL_L_DELIM' => '{{',
	'TMPL_R_DELIM' => '}}',
	'TEMPLATE_CHARSET' =>'UTF8', // 模板模板编码
	//'DEFAULT_FILTER' =>  'strip_tags,stripslashes',
	'SQL_DEBUG_LOG' => true,
	'SESSION_OPTIONS' => array(
		'expire'=>86400//过期时间为24小时
	),
);