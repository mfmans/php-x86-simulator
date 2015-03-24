<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module/p   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_module_p extends plato_version_D3_module {
	/* pop */
	static public function unit_pop() {
		switch(parent::$opbase) {
			/* POP m16 */
			/* POP m32 */
			case 0x8F:
				$data = parent::$cpu->pop();

				parent::$code->write_address($data);

				break;

			/* POP r16 */
			/* POP r32 */
			case 0x58:
				$data = parent::$cpu->pop();

				parent::$register->write(parent::$opcode, $data);

				break;

			/* POP DS */
			case 0x1F:		parent::$register->ds = parent::$cpu->pop(2); break;
			/* POP ES */
			case 0x07:		parent::$register->es = parent::$cpu->pop(2); break;
			/* POP SS */
			case 0x17:		parent::$register->ss = parent::$cpu->pop(2); break;
			/* POP FS */
			case 0x0FA1:	parent::$register->fs = parent::$cpu->pop(2); break;
			/* POP GS */
			case 0x0FA9:	parent::$register->gs = parent::$cpu->pop(2); break;
		}

		return 0;
	}

	/* popa */
	static public function unit_popa() {
		$edi = parent::$cpu->pop();
		$esi = parent::$cpu->pop();
		$ebp = parent::$cpu->pop();
		$esp = parent::$cpu->pop();
		$ebx = parent::$cpu->pop();
		$edx = parent::$cpu->pop();
		$ecx = parent::$cpu->pop();
		$eax = parent::$cpu->pop();

		parent::$register->write(7, $edi);
		parent::$register->write(6, $esi);
		parent::$register->write(5, $ebp);
		parent::$register->write(3, $ebx);
		parent::$register->write(2, $edx);
		parent::$register->write(1, $ecx);
		parent::$register->write(0, $eax);

		return 0;
	}

	/* popf */
	static public function unit_popf() {
		parent::$register->eflags = parent::$cpu->pop();
	}

	/* push */
	static public function unit_push() {
		switch(parent::$opbase) {
			/* PUSH r/m16 */
			/* PUSH r/m32 */
			case 0xFF:
				$data = parent::$cpu->read_address();

				parent::$cpu->push($data);

				break;

			/* PUSH r16 */
			/* PUSH r32 */
			case 0x50:
				$data = parent::$register->read(parent::$opcode);

				parent::$cpu->push($data);

				break;

			/* PUSH imm8 */
			case 0x6A:
				$data = parent::$alu->extend(parent::$imm, 1);

				parent::$cpu->push($data);

				break;

			/* PUSH imm16 */
			/* PUSH imm32 */
			case 0x68:
				parent::$cpu->push(parent::$imm);
				break;

			/* PUSH CS */
			case 0x0E:		parent::$cpu->push(parent::$register->cs, 2); break;
			/* PUSH SS */
			case 0x16:		parent::$cpu->push(parent::$register->ss, 2); break;
			/* PUSH DS */
			case 0x1E:		parent::$cpu->push(parent::$register->ds, 2); break;
			/* PUSH ES */
			case 0x06:		parent::$cpu->push(parent::$register->es, 2); break;
			/* PUSH FS */
			case 0x0FA0:	parent::$cpu->push(parent::$register->fs, 2); break;
			/* PUSH GS */
			case 0x0FA8:	parent::$cpu->push(parent::$register->gs, 2); break;
		}

		return 0;
	}

	/* pusha */
	static public function unit_pusha() {
		$eax = parent::$register->read(0);
		$ecx = parent::$register->read(1);
		$edx = parent::$register->read(2);
		$ebx = parent::$register->read(3);
		$esp = parent::$register->read(4);
		$ebp = parent::$register->read(5);
		$esi = parent::$register->read(6);
		$edi = parent::$register->read(7);

		parent::$cpu->push($eax);
		parent::$cpu->push($ecx);
		parent::$cpu->push($edx);
		parent::$cpu->push($ebx);
		parent::$cpu->push($esp);
		parent::$cpu->push($ebp);
		parent::$cpu->push($esi);
		parent::$cpu->push($edi);

		return 0;
	}

	/* pushf */
	static public function pushf() {
		parent::$cpu->push(parent::$register->eflags);

		return 0;
	}
}
