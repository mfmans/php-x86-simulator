<?php

require_once 'plato/inc.base.php';

echo plato_require('test.dll', array(), PLATO_TYPE_INTEGER, 'my_debug_handle');


/* ���Ժ��� */
function my_debug_handle($loader, $address) {
	/* ��ʼ�� */
	if(($loader === null) && ($address === null)) {
		// do something
		return;
	}

	/* ��װ�ϵ� */
	if(is_object($loader) && ($address === 0)) {
		$loader->cpu->breakpoint_add(0x0040117F);
		return;
	}

	/* �������� */
	if(is_object($loader) && ($address === null)) {
		// do something
		return;
	}

	/* ����ϵ� */
	switch($address) {
		case 0x0040117F:
			printf("EAX=%08X", $loader->cpu->register->eax);
			break;
	}
}
