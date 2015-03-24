<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/d   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_d extends plato_version_D3_module {
	/* daa */
	static public function unit_daa() {
		$al = parent::$register->al;

		$AF = parent::$register->AF;
		$CF = parent::$register->CF;

		if((($al & 0x0F) > 9) || $AF) {
			$al = $al + 6;
			$AF = 1;

			if($CF || ($al > 0xFF)) {
				$CF = 1;
			} else {
				$CF = 0;
			}
		} else {
			$AF = 0;
		}

		if((($al & 0xF0) > 0x90) || $CF) {
			$al = $al + 0x60;

			$CF = 1;
		} else {
			$CF = 0;
		}

		parent::$register->al = $al;

		parent::$register->AF = $AF;
		parent::$register->CF = $CF;

		return 0;
	}

	/* das */
	static public function unit_das() {
		$al = parent::$register->al;

		$AF = parent::$register->AF;
		$CF = parent::$register->CF;

		if((($al & 0x0F) > 9) || $AF) {
			if($CF || ($al < 6)) {
				$CF = 1;
			} else {
				$CF = 0;
			}

			$al = $al - 6;
			$AF = 1;
		} else {
			$AF = 0;
		}

		if(($al > 0x9F) || $CF) {
			$al = $al - 0x60;

			$CF = 1;
		} else {
			$CF = 0;
		}

		parent::$register->al = $al;

		parent::$register->AF = $AF;
		parent::$register->CF = $CF;

		return 0;
	}

	/* dec */
	static public function unit_dec() {
		plato_version_D3_template::increase (
			/* DEC r/m8 */
			/* DEC r/m16 */
			/* DEC r/m32 */
				0xFE,
			/* DEC r16 */
			/* DEC r32 */
				0x48,

			array (__CLASS__, 'unit_dec_core')
		);

		return 0;
	}

	static public function unit_dec_core($number) {
		return parent::$alu->arith_sub($number, 1);
	}

	/* div */
	static public function unit_div() {
		plato_version_D3_template::div('arith_div');

		return 0;
	}
}
