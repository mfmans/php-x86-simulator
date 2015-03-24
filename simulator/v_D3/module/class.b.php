<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/b   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_b extends plato_version_D3_module {
	/* bsf */
	static public function unit_bsf() {
		$max	= parent::$opsize * 8;
		$source	= parent::$cpu->read_address();

		if($source == 0) {
			parent::$register->ZF = 1;
		} else {
			parent::$register->ZF = 0;

			for($i = 0; $i < $max; $i++) {
				if($source & 0x01) {
					parent::$register->write(parent::$mod['code'], $i);
					break;
				}

				$source = $source >> 1;
			}
		}

		return 0;
	}

	/* bsr */
	static public function unit_bsr() {
		$max	= parent::$opsize * 8;
		$source	= parent::$cpu->read_address();

		$check	= 0x01 << ($max - 1);

		if($source == 0) {
			parent::$register->ZF = 1;
		} else {
			parent::$register->ZF = 0;

			for($i = 0; $i < $max; $i++) {
				if($source & $check) {
					parent::$register->write(parent::$mod['code'], $i);
					break;
				}

				$source = $source << 1;
			}
		}

		return 0;
	}

	/* bswap */
	static public function unit_bswap() {
		$source = parent::$register->read(parent::$opcode, 4);
		$result = 0;

		$result |= (($source >>  0) & 0xFF) << 24;
		$result |= (($source >>  8) & 0xFF) << 16;
		$result |= (($source >> 16) & 0xFF) <<  8;
		$result |= (($source >> 24) & 0xFF) <<  0;

		parent::$register->write(parent::$opcode, $result, 4);

		return 0;
	}

	/* bt */
	static public function unit_bt() {
		$result = plato_version_D3_template::bit (
			/* BT r/m16,r16 */
			/* BT r/m32,r32 */
				0x0FA3,
			/* BT r/m16,imm8 */
			/* BT r/m32,imm8 */
				0x0FBA
		);

		parent::$register->CF = $result[0];

		return 0;
	}

	/* btc */
	static public function unit_btc() {
		$result = plato_version_D3_template::bit (
			/* BTC r/m16,r16 */
			/* BTC r/m32,r32 */
				0x0FBB,
			/* BTC r/m16,imm8 */
			/* BTC r/m32,imm8 */
				0x0FBA
		);

		if($result[0]) {
			$result[1] = parent::$alu->bit_set($result[1], 0);
		} else {
			$result[1] = parent::$alu->bit_set($result[1], 1);
		}

		parent::$register->CF = $result[0];

		parent::$cpu->write_address($result[1]);

		return 0;
	}

	/* btr */
	static public function unit_btr() {
		$result = plato_version_D3_template::bit (
			/* BTR r/m16,r16 */
			/* BTR r/m32,r32 */
				0x0FB3,
			/* BTR r/m16,imm8 */
			/* BTR r/m32,imm8 */
				0x0FBA
		);

		$result[1] = parent::$alu->bit_set($result[1], 0);

		parent::$register->CF = $result[0];

		parent::$cpu->write_address($result[1]);

		return 0;
	}

	/* bts */
	static public function unit_bts() {
		$result = plato_version_D3_template::bit (
			/* BTS r/m16,r16 */
			/* BTS r/m32,r32 */
				0x0FAB,
			/* BTS r/m16,imm8 */
			/* BTS r/m32,imm8 */
				0x0FBA
		);

		$result[1] = parent::$alu->bit_set($result[1], 1);

		parent::$register->CF = $result[0];

		parent::$cpu->write_address($result[1]);

		return 0;
	}
}
