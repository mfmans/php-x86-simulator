<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/x   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_x extends plato_version_D3_module {
	/* xadd */
	static public function unit_xadd() {
		$number1	= parent::$cpu->read_address();
		$number2	= parent::$register->read(parent::$mod['code']);

		$result		= parent::$alu->add($number1, $number2);

		parent::$register->write(parent::$mod['code'], $number2);
		parent::$cpu->write_address($result);

		return 0;
	}

	/* xchg */
	static public function unit_xchg() {
		/* NOP */
		if(parent::$opcode == 0x90) {
			return 0;
		}

		switch(parent::$opbase) {
			/* XCHG AX, r16 */
			/* XCHG r16, AX */
			/* XCHG EAX, r32 */
			/* XCHG r32, EAX */
			case 0x90:
				$data1	= parent::$register->read(0);
				$data2	= parent::$register->read(parent::$opcode);

				parent::$register->write($data1, parent::$opcode);
				parent::$register->write($data2, 0);

				break;

			/* XCHG r/m8, r8 */
			/* XCHG r8, r/m8 */
			case 0x86:
			/* XCHG r/m16, r16 */
			/* XCHG r16, r/m16 */
			/* XCHG r/m32, r32 */
			/* XCHG r32, r/m32 */
			case 0x87:
				$data1	= parent::$register->read(parent::$mod['code']);
				$data2	= parent::$cpu->read_address();

				parent::$cpu->write_address($data1);
				parent::$register->write(parent::$mod['code'], $data2);

				break;
		}

		return 0;
	}

	/* xlat */
	static public function unit_xlat() {
		$address	= parent::$register->ebx;
		$offset		= parent::$register->al;

		$data		= parent::$cpu->read_memory($address + $offset, 1);

		parent::$register->al = $data;

		return 0;
	}

	/* xor */
	static public function unit_xor() {
		plato_version_D3_template::calculate (
			/* XOR AL,imm8 */
			/* XOR AX,imm16 */
			/* XOR EAX,imm32 */
				0x34,
			/* XOR r/m8,imm8 */
			/* XOR r/m16,imm16 */
			/* XOR r/m32,imm32 */
				0x80,
			/* XOR r/m16,imm8 */
			/* XOR r/m32,imm8 */
				0x83,
			/* XOR r/m8,r8 */
			/* XOR r/m16,r16 */
			/* XOR r/m32,r32 */
			/* XOR r8,r/m8 */
			/* XOR r16,r/m16 */
			/* XOR r32,r/m32 */
				0x30,

			array (__CLASS__, 'unit_xor_core')
		);

		return 0;
	}

	static public function unit_xor_core($number1, $number2) {
		return parent::$alu->logic_xor($number1, $number2);
	}
}
