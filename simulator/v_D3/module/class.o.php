<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/o   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_o extends plato_version_D3_module {
	/* or */
	static public function unit_or() {
		plato_version_D3_template::calculate (
			/* OR AL,imm8 */
			/* OR AX,imm16 */
			/* OR EAX,imm32 */
				0x0C,
			/* OR r/m8,imm8 */
			/* OR r/m16,imm16 */
			/* OR r/m32,imm32 */
				0x80,
			/* OR r/m16,imm8 */
			/* OR r/m32,imm8 */
				0x83,
			/* OR r/m8,r8 */
			/* OR r/m16,r16 */
			/* OR r/m32,r32 */
			/* OR r8,r/m8 */
			/* OR r16,r/m16 */
			/* OR r32,r/m32 */
				0x08,

			array (__CLASS__, 'unit_or_core')
		);

		return 0;
	}

	static public function unit_or_core($number1, $number2) {
		return parent::$alu->logic_or($number1, $number2);
	}
}
