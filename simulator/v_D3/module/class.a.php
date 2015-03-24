<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/a   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_a extends plato_version_D3_module {
	/* aaa */
	static public function unit_aaa() {
		$al = parent::$register->al;
		$ah = parent::$register->ah;

		if((($al & 0x0F) > 9) || parent::$register->AF) {
			$al = $al + 6;
			$ah = $ah + 1;

			parent::$register->AF = 1;
			parent::$register->CF = 1;
		} else {
			parent::$register->AF = 0;
			parent::$register->CF = 0;
		}

		parent::$register->al = $al & 0x0F;
		parent::$register->ah = $ah;

		return 0;
	}

	/* aad */
	static public function unit_aad() {
		$al = parent::$register->al;
		$ah = parent::$register->ah;

		$al = $al + ($ah * parent::$imm);

		parent::$register->al = $al;
		parent::$register->ah = 0;

		parent::$alu->flag($al, 1);

		return 0;
	}

	/* aam */
	static public function unit_aam() {
		$al = parent::$register->al;

		$ah = $al / parent::$imm;
		$al = $al % parent::$imm;

		parent::$register->al = $al;
		parent::$register->ah = (int) $ah;

		parent::$alu->flag($al, 1);

		return 0;
	}

	/* aas */
	static public function unit_aas() {
		$al = parent::$register->al;
		$ah = parent::$register->ah;

		if((($al & 0x0F) > 9) || parent::$register->AF) {
			$al = $al - 6;
			$ah = $ah - 1;

			parent::$register->AF = 1;
			parent::$register->CF = 1;
		} else {
			parent::$register->AF = 0;
			parent::$register->CF = 0;
		}

		parent::$register->al = $al;
		parent::$register->ah = $ah;

		return 0;
	}

	/* adc */
	public function unit_adc() {
		plato_version_D3_template::calculate (
			/* ADC AL,imm8 */
			/* ADC AX,imm16 */
			/* ADC EAX,imm32 */
				0x14,
			/* ADC r/m8,imm8 */
			/* ADC r/m16,imm16 */
			/* ADC r/m32,imm32 */
				0x80,
			/* ADC r/m16,imm8 */
			/* ADC r/m32,imm8 */
				0x83,
			/* ADC r/m8,r8 */
			/* ADC r/m16,r16 */
			/* ADC r/m32,r32 */
			/* ADC r8,r/m8 */
			/* ADC r16,r/m16 */
			/* ADC r32,r/m32 */
				0x10,

			array (__CLASS__, 'unit_adc_core')
		);

		return 0;
	}

	public function unit_adc_core($number1, $number2) {
		return parent::$alu->arith_add($number1, $number2, true);
	}

	/* add */
	public function unit_add() {
		plato_version_D3_template::calculate (
			/* ADD AL,imm8 */
			/* ADD AX,imm16 */
			/* ADD EAX,imm32 */
				0x04,
			/* ADD r/m8,imm8 */
			/* ADD r/m16,imm16 */
			/* ADD r/m32,imm32 */
				0x80,
			/* ADD r/m16,imm8 */
			/* ADD r/m32,imm8 */
				0x83,
			/* ADD r/m8,r8 */
			/* ADD r/m16,r16 */
			/* ADD r/m32,r32 */
			/* ADD r8,r/m8 */
			/* ADD r16,r/m16 */
			/* ADD r32,r/m32 */
				0x00,

			array (__CLASS__, 'unit_add_core')
		);

		return 0;
	}

	public function unit_add_core($number1, $number2) {
		return parent::$alu->arith_add($number1, $number2, false);
	}

	/* and */
	public function unit_and() {
		plato_version_D3_template::calculate (
			/* AND AL,imm8 */
			/* AND AX,imm16 */
			/* AND EAX,imm32 */
				0x24,
			/* AND r/m8,imm8 */
			/* AND r/m16,imm16 */
			/* AND r/m32,imm32 */
				0x80,
			/* AND r/m16,imm8 */
			/* AND r/m32,imm8 */
				0x83,
			/* AND r/m8,r8 */
			/* AND r/m16,r16 */
			/* AND r/m32,r32 */
			/* AND r8,r/m8 */
			/* AND r16,r/m16 */
			/* AND r32,r/m32 */
				0x20,

			array (__CLASS__, 'unit_and_core')
		);

		return 0;
	}

	public function unit_and_core($number1, $number2) {
		return parent::$alu->logic_and($number1, $number2);
	}
}
