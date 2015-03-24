<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/s   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_s extends plato_version_D3_module {
	/* sahf */
	static public function unit_sahf() {
		$flag = parent::$register->ah;

		/* SF:ZF:0:AF:0:PF:1:CF */
		parent::$register->CF = ($flag >> 0) & 0x01;
		parent::$register->PF = ($flag >> 2) & 0x01;
		parent::$register->AF = ($flag >> 4) & 0x01;
		parent::$register->ZF = ($flag >> 5) & 0x01;
		parent::$register->SF = ($flag >> 6) & 0x01;

		return 0;
	}

	/* sal */
	static public function unit_sal() {
		$number = plato_version_D3_template::shift (
			/* SAL r/m8 */
			/* SHL r/m8 */
			/* SAL r/m16 */
			/* SHL r/m16 */
			/* SAL r/m32 */
			/* SHL r/m32 */
				0xD0,
			/* SAL r/m8,CL */
			/* SHL r/m8,CL */
			/* SAL r/m16,CL */
			/* SHL r/m16,CL */
			/* SAL r/m32,CL */
			/* SHL r/m32,CL */
				0xD2,
			/* SAL r/m8,imm8 */
			/* SHL r/m8,imm8 */
			/* SAL r/m16,imm8 */
			/* SHL r/m16,imm8 */
			/* SAL r/m32,imm8 */
			/* SHL r/m32,imm8 */
				0xC0,

			'shift_left', false, false
		);

		if($number['position'] == 1) {
			parent::$register->OF = $number[1] ^ $number[2];
		}

		return 0;
	}

	/* sar */
	static public function unit_sar() {
		$number = plato_version_D3_template::shift (
			/* SAR r/m8 */
			/* SAR r/m16 */
			/* SAR r/m32 */
				0xD0,
			/* SAR r/m8,CL */
			/* SAR r/m16,CL */
			/* SAR r/m32,CL */
				0xD2,
			/* SAR r/m8,imm8 */
			/* SAR r/m16,imm8 */
			/* SAR r/m32,imm8 */
				0xC0,

			'shift_right', false, false
		);

		if($number['position'] == 1) {
			parent::$register->OF = 0;
		}

		return 0;
	}

	/* sbb */
	static public function unit_sbb() {
		plato_version_D3_template::calculate (
			/* SBB AL,imm8 */
			/* SBB AX,imm16 */
			/* SBB EAX,imm32 */
				0x1C,
			/* SBB r/m8,imm8 */
			/* SBB r/m16,imm16 */
			/* SBB r/m32,imm32 */
				0x80,
			/* SBB r/m16,imm8 */
			/* SBB r/m32,imm8 */
				0x83,
			/* SBB r/m8,r8 */
			/* SBB r/m16,r16 */
			/* SBB r/m32,r32 */
			/* SBB r8,r/m8 */
			/* SBB r16,r/m16 */
			/* SBB r32,r/m32 */
				0x18,

			array (__CLASS__, 'unit_sbb_core')
		);

		return 0;
	}

	static public function unit_sbb_core($number1, $number2) {
		return parent::$alu->arith_sub($number1, $number2, true);
	}

	/* scas */
	static public function unit_scas() {
		$data1	= parent::$register->read(0);
		$data2	= parent::$register->edi_read();

		parent::$register->edi_next();

		parent::$alu->arith_sub($data1, $data2);

		return 0;
	}

	/* setcc */
	static public function unit_setcc() {
		if(parent::$condition) {
			$data = 1;
		} else {
			$data = 0;
		}

		parent::$cpu->write_address($data, 0, 1);

		return 0;
	}

	/* shr */
	static public function unit_shr() {
		$number = plato_version_D3_template::shift (
			/* SHR r/m8 */
			/* SHR r/m16 */
			/* SHR r/m32 */
				0xD0,
			/* SHR r/m8,CL */
			/* SHR r/m16,CL */
			/* SHR r/m32,CL */
				0xD2,
			/* SHR r/m8,imm8 */
			/* SHR r/m16,imm8 */
			/* SHR r/m32,imm8 */
				0xC0,

			'shift_right', true, false
		);

		if($number['position'] == 1) {
			parent::$register->OF = ($number['number'] >> (parent::$opsize * 8 - 1)) & 0x01;
		}

		return 0;
	}

	/* stc */
	static public function unit_stc() {
		parent::$register->CF = 1;

		return 0;
	}

	/* std */
	static public function unit_std() {
		parent::$register->DF = 1;

		return 0;
	}

	/* stos */
	static public function unit_stos() {
		$data	= parent::$register->read(0);

		parent::$register->edi_write($data);
		parent::$register->edi_next();

		return 0;
	}

	/* sub */
	static public function unit_sub() {
		plato_version_D3_template::calculate (
			/* SUB AL,imm8 */
			/* SUB AX,imm16 */
			/* SUB EAX,imm32 */
				0x2C,
			/* SUB r/m8,imm8 */
			/* SUB r/m16,imm16 */
			/* SUB r/m32,imm32 */
				0x80,
			/* SUB r/m16,imm8 */
			/* SUB r/m32,imm8 */
				0x83,
			/* SUB r/m8,r8 */
			/* SUB r/m16,r16 */
			/* SUB r/m32,r32 */
			/* SUB r8,r/m8 */
			/* SUB r16,r/m16 */
			/* SUB r32,r/m32 */
				0x28,

			array (__CLASS__, 'unit_sub_core')
		);

		return 0;
	}

	static public function unit_sub_core($number1, $number2) {
		return parent::$alu->arith_sub($number1, $number2, false);
	}
}
