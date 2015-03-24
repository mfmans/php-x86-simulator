<?php

require_once 'plato/inc.base.php';

echo plato_require('test.dll', array(), PLATO_TYPE_INTEGER, 'my_debug_handle');


/* 调试函数 */
function my_debug_handle($loader, $address) {
	/* 初始化 */
	if(($loader === null) && ($address === null)) {
		// do something
		return;
	}

	/* 安装断点 */
	if(is_object($loader) && ($address === 0)) {
		$loader->cpu->breakpoint_add(0x0040117F);
		return;
	}

	/* 结束运行 */
	if(is_object($loader) && ($address === null)) {
		// do something
		return;
	}

	/* 处理断点 */
	switch($address) {
		case 0x0040117F:
			printf("EAX=%08X", $loader->cpu->register->eax);
			break;
	}
}
