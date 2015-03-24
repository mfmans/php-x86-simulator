<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ inc/base   #D3
*/

/* create env without php extension */
if(!class_exists('plato_class_loader')) {
	define('PLATO',			true);
	define('PLATO_ROOT',	realpath(dirname(__FILE__)).'/');

	/* all constant */
	require_once PLATO_ROOT.'inc.const.php';

	/* exception */
	require_once PLATO_ROOT.'class.exception.php';

	/* interface */
	require_once PLATO_ROOT.'class.interface.php';
	/* loader */
	require_once PLATO_ROOT.'class.loader.php';
}

/* plato_include */
if(!function_exists('plato_include')) {
	/*
		plato_include								引入文件

		@ str	$file
		@ array	$args	= array()
		@ int	$type	= PLATO_TYPE_INTEGER		返回类型
		@ call	$debug	= null						调试执行函数

		# mixed										执行成功根据 $type 返回
		# class	plato_exception						执行失败返回 plato_exception 异常
	*/
	function plato_include($file, $args = array(), $type = PLATO_TYPE_INTEGER, $debug = null) {
		$object = plato_class_loader::create($file);

		/* 初始化 */
		if(is_callable($debug)) {
			call_user_func($debug, null, null);
		}

		try {
			$object->load($debug);
			$object->call(0, $args);

			$return = $object->destroy($type);
		} catch (Exception $ex) {
			if($ex instanceof plato_exception == false) {
				$return = new plato_exception ($ex);
			} else {
				$return = $ex;
			}
		}

		if(is_callable($debug)) {
			call_user_func($debug, $object, null);
		}

		return $return;
	}
}

/* plato_require */
if(!function_exists('plato_require')) {
	/*
		plato_require								引入文件
													若执行失败, 将直接输出异常信息并终止程序运行
													可以使用 "@" 运算符屏蔽异常信息的显示, 对应的错误级别为 E_WARNING

		@ str	$file
		@ array	$args	= array()
		@ int	$type	= PLATO_TYPE_INTEGER
		@ call	$debug	= null

		# mixed
	*/
	function plato_require($file, $args = array(), $type = PLATO_TYPE_INTEGER, $debug = null) {
		$object = plato_include($file, $args, $type, $debug);

		/* exception */
		if($object instanceof plato_exception) {
			if((error_reporting() & E_WARNING) == E_WARNING) {
				$object->report();
			}

			/* error reporting disabled */
			exit;
		}

		return $object;
	}
}
