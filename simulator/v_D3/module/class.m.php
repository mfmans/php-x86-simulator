<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/m   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_m extends plato_version_D3_module {
	/* mov */
	static public function unit_mov() {
		$opsize = parent::$opsize;

		switch(parent::$opbase) {
			/* MOV r/m8,r8 */
			/* MOV r/m16,r16 */
			/* MOV r/m32,r32 */
			/* MOV r8,r/m8 */
			/* MOV r16,r/m16 */
			/* MOV r32,r/m32 */
			case 0x88:
				$data = parent::$cpu->read_address(2);

				parent::$cpu->write_address($data, 1);

				break;

			/* MOV r8,imm8 */
			case 0xB0:
				$opsize = 1;
			/* MOV r16,imm16 */
			/* MOV r32,imm32 */
			case 0xB8:
				parent::$register->write(parent::$opcode, parent::$imm, $opsize);

				break;

			/* MOV r/m8,imm8 */
			case 0xC6:
			/* MOV r/m16,imm16 */
			/* MOV r/m32,imm32 */
			case 0xC7:
				parent::$cpu->write_address(parent::$imm);

				break;
		}

		return 0;
	}

	/* movs */
	static public function unit_movs() {
		$data = parent::$register->esi_read();

		parent::$register->edi_write($data);

		parent::$register->esi_next();
		parent::$register->edi_next();

		return 0;
	}

	/* movsx */
	static public function unit_movsx() {
		switch(parent::$opcode) {
			/* MOVSX r16,r/m8 */
			/* MOVSX r32,r/m8 */
			case 0x0FBE:
				$data = parent::$cpu->read_address() & 0xFF;
				$data = parent::$alu->extend($data, 1);

				parent::$register->write(parent::$mod['code'], $data);

				break;

			/* MOVSX r32,r/m16 */
			case 0x0FBF:
				$data = parent::$cpu->read_address() & 0xFFFF;
				$data = parent::$alu->extend($data, 2);

				parent::$register->write(parent::$mod['code'], $data, 4);

				break;
		}

		return 0;
	}

	/* movzx */
	static public function unit_movzx() {
		switch(parent::$opcode) {
			/* MOVZX r16,r/m8 */
			/* MOVZX r32,r/m8 */
			case 0x0FB6:
				$data = parent::$cpu->read_address() & 0xFF;

				parent::$register->write(parent::$mod['code'], $data);

				break;

			/* MOVZX r32,r/m16 */
			case 0x0FB7:
				$data = parent::$cpu->read_address() & 0xFFFF;

				parent::$register->write(parent::$mod['code'], $data, 4);

				break;
		}

		return 0;
	}

	/* mul */
	static public function unit_mul() {
		switch(parent::$opcode) {
			/* MUL r/m8 */
			case 0xF6:
				$number1	= parent::$register->al;
				$number2	= parent::$cpu->read_address();

				$result		= parent::$alu->arith_mul($number1, $number2, 1);

				parent::$register->al = $result[0];
				parent::$register->ah = $result[0];

				break;

			/* MUL r/m16 */
			/* MUL r/m32 */
			case 0xF7:
				$number1	= parent::$register->read(0);
				$number2	= parent::$cpu->read_address();

				$result		= parent::$alu->arith_mul($number1, $number2);

				parent::$register->write(0, $result[0]);
				parent::$register->write(3, $result[1]);

				break;
		}

		return 0;
	}
}
