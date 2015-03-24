<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/t   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_t extends plato_version_D3_module {
	/* test */
	static public function unit_test() {
		plato_version_D3_template::calculate (
			/* TEST AL,imm8 */
			/* TEST AX,imm16 */
			/* TEST EAX,imm32 */
				0xA8,
			/* TEST r/m8,imm8 */
			/* TEST r/m16,imm16 */
			/* TEST r/m32,imm32 */
				0xF6,
			/* ignore */
				0xFF,
			/* TEST r/m8,r8 */
			/* TEST r/m16,r16 */
			/* TEST r/m32,r32 */
				0x84,

			array (__CLASS__, 'unit_test_core')
		);

		return 0;
	}

	static public function unit_test_core($number1, $number2) {
		parent::$alu->logic_and($number1, $number2);

		return null;
	}
}
