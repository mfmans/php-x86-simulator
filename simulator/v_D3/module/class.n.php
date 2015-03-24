<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/n   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_n extends plato_version_D3_module {
	/* neg */
	static public function unit_neg() {
		$number = parent::$cpu->read_address();
		$number = parent::$alu->logic_neg($number);

		parent::$cpu->write_address($number);

		return 0;
	}

	/* not */
	static public function unit_not() {
		$number = parent::$cpu->read_address();
		$number = parent::$alu->logic_not($number);

		parent::$cpu->write_address($number);

		return 0;
	}
}
