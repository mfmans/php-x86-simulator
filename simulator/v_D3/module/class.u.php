<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/u   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_u extends plato_version_D3_module {
	/* ud2 */
	static public function unit_ud2() {
		parent::exception (PLATO_EX_UNIT_UD3);
	}
}
