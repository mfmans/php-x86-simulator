<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/c   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_c extends plato_version_D3_module {
	/* call */
	static public function unit_call() {
		/* return address */
		$return		= parent::$register->eip;

		$address	= 0;
		$absolute	= true;

		switch(parent::$opcode) {
			/* CALL rel16 */
			/* CALL rel32 */
			case 0xE8:
				$address	= parent::$imm;
				$absolute	= false;
				break;

			/* CALL r/m16 */
			/* CALL r/m32 */
			case 0xFF;
				$address	= parent::$cpu->read_address();
				break;

			/* CALL ptr16:16 */
			/* CALL ptr16:32 */
			case 0x9A:
				$cs			= parent::$code->next_word();
				$address	= parent::$imm;

				parent::$cpu->push($cs, 2);

				break;

			/* CALL m16:16 */
			/* CALL m16:32 */
			case 0xFF:
				if(($memory = parent::$code->address()) === false) {
					parent::exception (PLATO_EX_UNIT_CALL);
				}

				$address	= parent::$cpu->read_memory($memory);
				$cs			= parent::$cpu->read_memory($memory + parent::$opsize, 2);

				parent::$cpu->push($cs, 2);

				break;
		}

		parent::$cpu->push($return, 4);
		parent::$cpu->jump($address, $absolute);

		return 0;
	}

	/* cbw */
	static public function unit_cbw() {
		$opsize = (int) (parent::$opsize / 2);

		$number = parent::$register->read(0, $opsize);
		$number = parent::$alu->extend($number, $opsize);

		parent::$register->write(0, $number);

		return 0;
	}

	/* clc */
	static public function unit_clc() {
		parent::$register->CF = 0;

		return 0;
	}

	/* cld */
	static public function unit_cld() {
		parent::$register->DF = 0;

		return 0;
	}

	/* cmc */
	static public function unit_cmc() {
		if(parent::$register->CF) {
			parent::$register->CF = 0;
		} else {
			parent::$register->CF = 1;
		}

		return 0;
	}

	/* cmovcc */
	static public function unit_cmovcc() {
		if(parent::$condition) {
			$data = parent::$cpu->read_address();

			parent::$register->write(parent::$mod['code'], $data);
		}

		return 0;
	}

	/* cmp */
	static public function unit_cmp() {
		plato_version_D3_template::calculate (
			/* CMP AL,imm8 */
			/* CMP AX,imm16 */
			/* CMP EAX,imm32 */
				0x3C,
			/* CMP r/m8,imm8 */
			/* CMP r/m16,imm16 */
			/* CMP r/m32,imm32 */
				0x80,
			/* CMP r/m16,imm8 */
			/* CMP r/m32,imm8 */
				0x83,
			/* CMP r/m8,r8 */
			/* CMP r/m16,r16 */
			/* CMP r/m32,r32 */
			/* CMP r8,r/m8 */
			/* CMP r16,r/m16 */
			/* CMP r32,r/m32 */
				0x38,

			array (__CLASS__, 'unit_cmp_core')
		);

		return 0;
	}

	static public function unit_cmp_core($number1, $number2) {
		parent::$alu->arith_sub($number1, $number2, false);

		return null;
	}

	/* cmps */
	static public function unit_cmps() {
		$data1 = parent::$register->esi_read();
		$data2 = parent::$register->edi_read();

		parent::$register->esi_next();
		parent::$register->edi_next();

		parent::$alu->arith_sub($data1, $data2, false);

		return 0;
	}

	/* cmpxchg */
	static public function unit_cmpxchg() {
		$data1	= parent::$register->read(0);
		$data2	= parent::$cpu->read_address();
		$data3	= parent::$register->read(parent::$mod['code']);

		if($data1 == $data2) {
			parent::$register->ZF = 1;

			parent::$cpu->write_address($data3);
		} else {
			parent::$register->ZF = 0;

			parent::$register->write(0, $data2);
		}

		return 0;
	}

	/* cmpxchg8b */
	static public function unit_cmpxchg8b() {
		$eax = parent::$register->eax;
		$edx = parent::$register->edx;

		if(($address = parent::$code->address()) == false) {
			parent::exception(PLATO_EX_UNIT_CMPXCHG8B);
		}

		$low	= parent::$cpu->read_memory($address,		4);
		$high	= parent::$cpu->read_memory($address + 4,	4);

		if(($low == $eax) && ($high == $edx)) {
			parent::$register->ZF = 1;

			$ecx = parent::$register->ecx;
			$ebx = parent::$register->ebx;

			parent::$cpu->write_memory($address,		$ebx, 4);
			parent::$cpu->write_memory($address + 4,	$ecx, 4);
		} else {
			parent::$register->ZF		= 0;

			parent::$register->eax	= $low;
			parent::$register->edx	= $high;
		}

		return 0;
	}

	/* cpuid */
	static public function unit_cpuid() {
		parent::exception(PLATO_EX_UNIT_CPUID);
	}
}
