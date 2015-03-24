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
		plato_include								�����ļ�

		@ str	$file
		@ array	$args	= array()
		@ int	$type	= PLATO_TYPE_INTEGER		��������
		@ call	$debug	= null						����ִ�к���

		# mixed										ִ�гɹ����� $type ����
		# class	plato_exception						ִ��ʧ�ܷ��� plato_exception �쳣
	*/
	function plato_include($file, $args = array(), $type = PLATO_TYPE_INTEGER, $debug = null) {
		$object = plato_class_loader::create($file);

		/* ��ʼ�� */
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
		plato_require								�����ļ�
													��ִ��ʧ��, ��ֱ������쳣��Ϣ����ֹ��������
													����ʹ�� "@" ����������쳣��Ϣ����ʾ, ��Ӧ�Ĵ��󼶱�Ϊ E_WARNING

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
