<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/i   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_i extends plato_version_D3_module {
	/* idiv */
	static public function unit_idiv() {
		plato_version_D3_template::div('idiv');

		return 0;
	}

	/* imul */
	static public function unit_imul() {
		switch(parent::$opcode) {
			/* IMUL r/m8 */
			case 0xF6:
				$number1	= parent::$register->al;
				$number2	= parent::$cpu->read_address();

				$result		= parent::$alu->arith_imul($number1, $number2, 1);

				parent::$register->al = $result[0];
				parent::$register->ah = $result[1];

				break;

			/* IMUL r/m16 */
			/* IMUL r/m32 */
			case 0xF7:
				$number1	= parent::$register->read(0);
				$number2	= parent::$cpu->read_address();

				$result		= parent::$alu->arith_imul($number1, $number2);

				parent::$register->write(0, $result[0]);
				parent::$register->write(2, $result[1]);

				break;

			/* IMUL r16,r/m16 */
			/* IMUL r32,r/m32 */
			case 0x0FAF:
				$number1	= parent::$register->read(parent::$mod['code']);
				$number2	= parent::$cpu->read_address();

				$result		= parent::$alu->arith_imul($number1, $number2);

				parent::$register->write(parent::$mod['code'], $result[0]);

				break;

			/* IMUL r16,r/m16,imm8 */
			/* IMUL r32,r/m32,imm8 */
			/* IMUL r16,imm8 */
			/* IMUL r32,imm8 */
			case 0x6B:
				$number1	= parent::$cpu->read_address();
				$number2	= parent::$alu->extend(parent::$imm);

				$result		= parent::$alu->arith_imul($number1, $number2);

				parent::$register->write(parent::$mod['code'], $result[0]);

				break;

			/* IMUL r16,r/m16,imm16 */
			/* IMUL r32,r/m32,imm32 */
			/* IMUL r16,imm16 */
			/* IMUL r32,imm32 */
			case 0x69:
				$number1	= parent::$cpu->read_address();
				$number2	= parent::$imm;

				$result		= parent::$alu->arith_imul($number1, $number2);

				parent::$register->write(parent::$mod['code'], $result[0]);

				break;
		}

		return 0;
	}

	/* inc */
	static public function unit_inc() {
		plato_version_D3_template::increase (
			/* INC r/m8 */
			/* INC r/m16 */
			/* INC r/m32 */
				0xFE,
			/* INC r16 */
			/* INC r32 */
				0x40,

			array (__CLASS__, 'unit_inc_core')
		);

		return 0;
	}

	static public function unit_inc_core($number) {
		return parent::$alu->arith_add($number, 1);
	}

	/* int */
	static public function unit_int() {
		switch(parent::$opcode) {
			/* INT 3 */
			case 0xCC:
				parent::exception (PLATO_EX_UNIT_INT3);

			/* INT imm8 */
			case 0xCD:
				parent::exception (PLATO_EX_UNIT_INT);

			/* INTO */
			case 0xCE:
				if(parent::$register->OF) {
					parent::exception (PLATO_EX_UNIT_INTO);
				}
				break;
		}

		return 0;
	}
}
