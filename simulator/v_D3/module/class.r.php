<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/r   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_r extends plato_version_D3_module {
	/* rcl */
	static public function unit_rcl() {
		$number = plato_version_D3_template::shift (
			/* RCL r/m8,1 */
			/* RCL r/m16,1 */
			/* RCL r/m32,1 */
				0xD0,
			/* RCL r/m8,CL */
			/* RCL r/m16,CL */
			/* RCL r/m32,CL */
				0xD2,
			/* RCL r/m8,imm8 */
			/* RCL r/m16,imm8 */
			/* RCL r/m32,imm8 */
				0xC0,

			'shift_left', false, true
		);

		if($number['position'] == 1) {
			parent::$register->OF = $number[1] ^ $number[2];
		}

		return 0;
	}

	/* rcr */
	static public function unit_rcr() {
		$number = plato_version_D3_template::shift (
			/* RCR r/m8,1 */
			/* RCR r/m16,1 */
			/* RCR r/m32,1 */
				0xD0,
			/* RCR r/m8,CL */
			/* RCR r/m16,CL */
			/* RCR r/m32,CL */
				0xD2,
			/* RCR r/m8,imm8 */
			/* RCR r/m16,imm8 */
			/* RCR r/m32,imm8 */
				0xC0,

			'shift_right', false, true
		);

		if($number['position'] == 1) {
			/* 第二高有效位 */
			$bit = ($number[0] >> (parent::$opsize * 8 - 2)) & 0x01;

			parent::$register->OF = $number[2] ^ $bit;
		}

		return 0;
	}

	/* rdtsc */
	static public function unit_rdtsc() {
		$cpu->register->eax = 0;
		$cpu->register->edx = 0;

		return 0;
	}

	/* ret */
	static public function unit_ret() {
		parent::$register->eip = parent::$cpu->pop(4);

		switch(parent::$opcode) {
			/* RETF */
			case 0xC8:
			/* RETF imm16 */
			case 0xCA:
				parent::$register->cs = parent::$cpu->pop(2);
		}

		/* for __stdcall */
		switch(parent::$opcode) {
			/* RETN imm16 */
			case 0xC2:
			/* RETF imm16 */
			case 0xCA:
				parent::$register->esp += parent::$imm * 4;
		}

		return 0;
	}

	/* rol */
	static public function unit_rol() {
		$number = plato_version_D3_template::shift (
			/* ROL r/m8,1 */
			/* ROL r/m16,1 */
			/* ROL r/m32,1 */
				0xD0,

			/* ROL r/m8,CL */
			/* ROL r/m16,CL */
			/* ROL r/m32,CL */
				0xD2,

			/* ROL r/m8,imm8 */
			/* ROL r/m16,imm8 */
			/* ROL r/m32,imm8 */
				0xC0,

			'shift_left', true, true
		);

		if($number['position'] == 1) {
			parent::$register->OF = $number[1] ^ $number[2];
		}

		return 0;
	}

	/* ror */
	static public function unit_ror() {
		$number = plato_version_D3_template::shift (
			/* ROR r/m8,1 */
			/* ROR r/m16,1 */
			/* ROR r/m32,1 */
				0xD0,

			/* ROR r/m8,CL */
			/* ROR r/m16,CL */
			/* ROR r/m32,CL */
				0xD2,

			/* ROR r/m8,imm8 */
			/* ROR r/m16,imm8 */
			/* ROR r/m32,imm8 */
				0xC0,

			'shift_right', true, true
		);

		if($number['position'] == 1) {
			$bit = ($number[0] >> (parent::$opsize * 8 - 2)) & 0x01;

			parent::$register->OF = $number[2] ^ $bit;
		}

		return 0;
	}
}
