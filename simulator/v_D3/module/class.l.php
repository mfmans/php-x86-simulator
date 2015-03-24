<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/l   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_l extends plato_version_D3_module {
	/* lahf */
	static public function unit_lahf() {
		$flag = 0x02;

		/* SF:ZF:0:AF:0:PF:1:CF */
		$flag |= (parent::$register->CF ? 0x01 : 0x00) << 0;
		$flag |= (parent::$register->PF ? 0x01 : 0x00) << 2;
		$flag |= (parent::$register->AF ? 0x01 : 0x00) << 4;
		$flag |= (parent::$register->ZF ? 0x01 : 0x00) << 5;
		$flag |= (parent::$register->SF ? 0x01 : 0x00) << 6;

		parent::$register->ah = $flag;

		return 0;
	}

	/* lea */
	static public function unit_lea() {
		if(($address = parent::$cpu->address()) === null) {
			parent::exception (PLATO_EX_UNIT_LEA);
		}

		parent::$register->write(parent::$mod['code'], $address);

		return 0;
	}

	/* lods */
	static public function unit_lods() {
		$data = parent::$register->esi_read();

		parent::$register->write(0, $data);
		parent::$register->esi_next();

		return 0;
	}

	/* loop */
	static public function unit_loop() {
		$jump	= false;

		$ecx	= parent::$register->ecx;
		$ZF		= parent::$register->ZF;

		/* ecx - 1 */
		if($ecx == 0) {
			parent::$register->write(1, (int) 0xFFFFFFFF, 4);
		} else {
			parent::$register->write(1, $ecx - 1, 4);
		}

		switch(parent::$opcode) {
			/* LOOP rel8 */
			case 0xE2:
				if($ecx != 1) {
					$jump = true;
				}
				break;

			/* LOOPE rel8 */
			/* LOOPZ rel8 */
			case 0xE1:
				if(($ecx != 1) && ($ZF == 1)) {
					$jump = true;
				}
				break;

			/* LOOPNE rel8 */
			/* LOOPNZ rel8 */
			case 0xE0:
				if(($ecx != 1) && ($ZF == 0)) {
					$jump = true;
				}
				break;
		}

		if($jump) {
			parent::$cpu->jump(parent::$imm, false, 1);
		}

		return 0;
	}
}
