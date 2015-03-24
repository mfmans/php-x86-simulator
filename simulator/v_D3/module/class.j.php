<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/j   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_j extends plato_version_D3_module {
	/* jcc */
	static public function unit_jcc() {
		if(parent::$condition) {
			if((parent::$opbase & 0x0F00) == 0) {
				$opsize = 1;
			} else {
				$opsize = parent::$opsize;
			}

			parent::$cpu->jump(parent::$imm, false, $opsize);
		}

		return 0;
	}

	/* jmp */
	static public function unit_jmp() {
		switch(parent::$opcode) {
			/* EB cb */
			case 0xEB:
				parent::$cpu->jump(parent::$imm, false, 1);
				break;

			/* JMP rel16 */
			/* JMP rel32 */
			case 0xE9:
				parent::$cpu->jump(parent::$imm, false);
				break;

			/* JMP r/m16 */
			/* JMP r/m32 */
			case 0xFF:
				parent::$cpu->jump(parent::$cpu->read_address());
				break;

			/* JMP ptr16:16 */
			/* JMP ptr16:32 */
			case 0xEA:
				/* cs */
				parent::$code->next_word();

				parent::$cpu->jump(parent::$imm);
				break;

			/* JMP m16:16 */
			/* JMP m16:32 */
			case 0xFF:
				if(($address = parent::$cpu->address()) === null) {
					parent::exception (PLATO_EX_UNIT_JUMP);
				}

				parent::$cpu->jump(parent::$cpu->read_address($address));

				break;
		}

		return 0;
	}
}
