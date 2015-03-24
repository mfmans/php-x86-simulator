<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ class/decoder   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_class_decoder implements plato_interface_decoder {
	private $ldr;
	private	$cpu;
	private	$memory;

	/* opcode */
	private	$code;

	/* 起始地址 */
	public	$address;

	/* 实际字节码 */
	public	$opcode;
	/* 基础字节码 */
	public	$opbase;
	/* 操作数长 */
	public	$opsize;
	/* 寻址 */
	public	$mod;
	/* 立即数 */
	public	$imm;
	/* 读取方向 */
	public	$direction;

	/* 指令详细信息 */
	public	$info;

	/* 重复 */
	public	$repeat;
	/* 条件判断 */
	public	$condition;

	/*
		__construct
	*/
	public function __construct($ldr) {
		$this->ldr		= $ldr;
		$this->cpu		= $ldr->cpu;
		$this->memory	= $ldr->memory;

		$this->code		= & $ldr->compiler->code;
	}

	/*
		exception
	*/
	private function exception($id) {
		throw new plato_exception ($id, array(), $this->ldr);
	}

	/*
		parse
	*/
	public function parse() {
		$this->address	= $this->cpu->register->eip;

		$this->opsize	= 4;
		$this->repeat	= 0;

		$code	= 0;
		$prefix	= true;

		/* 启动执行保护 */
		$this->memory->execute(true);

		/* prefix */
		while($prefix) {
			$code = $this->next_byte(false);

			switch($code) {
				/* 0x66  change default operand size */
				case 0x66: $this->opsize = 2;	break;

				/* 0xF3, 0xF2  repeat */
				case 0xF2: $this->repeat = 2;	break;
				case 0xF3: $this->repeat = 3;	break;

				/* 0x67  change default address size */
				case 0x67:
					$this->exception (PLATO_EX_DECODER_PRE_ADDRESS);

				/* 0xF0  lock */
				case 0xF0:
					$this->exception (PLATO_EX_DECODER_PRE_LOCK);

				/* segment */
				case 0x2E:
				case 0x36:
				case 0x3E:
				case 0x26:
				case 0x64:
				case 0x65:
					$this->exception (PLATO_EX_DECODER_PRE_SEGMENT);

				default:
					$prefix = false;
			}
		}

		/* 0x0F 前缀 */
		if($code == 0x0F) {
			$code = $this->next_byte();
			$code = $code | 0x0F00;
		}

		/* 无效指令 */
		if(!isset($this->code[$code])) {
			return false;
		}

		if($this->code[$code]) {
			$this->info = & $this->code[$code];

			/* mod r/m */
			if($this->info['mod']) {
				$this->mod();
			}
		} else {					/* mod r/m 分支 */
			$this->mod();

			/* reg code 域 */
			$code = ($code << 16) | $this->mod['code'];

			if(!isset($this->code[$code])) {
				return false;
			}

			$this->info = & $this->code[$code];
		}

		/* 操作数长为字节 */
		if($this->info['byte']) {
			$this->opsize = 1;
		}

		/* imm */
		if($this->info['imm']) {
			if(is_int($this->info['imm'])) {
				$imm = $this->info['imm'];
			} else {
				$imm = $this->opsize;
			}

			switch($imm) {
				case 1: $this->imm = $this->next_byte	(false);	break;
				case 2: $this->imm = $this->next_word	(false);	break;
				case 4: $this->imm = $this->next_dword	(false);	break;
			}
		}

		/* 关闭执行保护 */
		$this->memory->execute(false);

		$this->opcode		= $this->info['code'];
		$this->opbase		= $this->info['base'];
		$this->direction	= $this->info['direction'];

		/* 条件判断 */
		if($this->info['condition']) {
			$this->condition();
		}

		/* 重复执行器 */
		if($this->info['repeat'] && $this->repeat) {
			$this->repeat();

			return true;
		}

		return $this->info['call'];
	}

	/*
		next_byte
	*/
	public function next_byte($protect = true) {
		if($protect) {
			$this->memory->execute(true);
		}

		$address	= & $this->cpu->register->eip;
		$data		= $this->memory->read_byte($address);

		$address++;

		if($protect) {
			$this->memory->execute(false);
		}

		return $data;
	}

	/*
		next_word
	*/
	public function next_word($protect = true) {
		if($protect) {
			$this->memory->execute(true);
		}

		$address	= & $this->cpu->register->eip;
		$data		= $this->memory->read_word($address);

		$address	= $address + 2;

		if($protect) {
			$this->memory->execute(false);
		}

		return $data;
	}

	/*
		next_dword
	*/
	public function next_dword($protect = true) {
		if($protect) {
			$this->memory->execute(true);
		}

		$address	= & $this->cpu->register->eip;
		$data		= $this->memory->read_dword($address);

		$address	= $address + 4;

		if($protect) {
			$this->memory->execute(false);
		}

		return $data;
	}

	/*
		offset
	*/
	public function offset($number) {
		if(($address = $this->cpu->address()) === null) {
			return;
		}

		$this->mod = array (
			'method'	=> 2,
			'address'	=> $address + $number
		);
	}

	/*
		mod											解析 mod r/m + sib
	*/
	private function mod() {
		$this->mod	= array (
			'method'	=> 0,
			'byte'		=> false,			/* 使用字节偏移量 */
		);

		/* mod r/m */
		$modrm		= $this->next_byte(false);
		$modrm_mod	= ($modrm >> 6) & 0x03;
		$modrm_reg0	= ($modrm >> 3) & 0x07;
		$modrm_reg1	= $modrm & 0x07;

		/* code always @ mod r/m */
		$this->mod['code'] = $modrm_reg0;

		/* reg, reg */
		if($modrm_mod == 0x03) {
			$this->mod['register'] = $modrm_reg1;

			return;
		}

		/* reg, [imm32] */
		if(($modrm_mod == 0x00) && ($modrm_reg1 == 0x05)) {
			$this->mod['method']	= 1;
			$this->mod['address']	= $this->next_dword(false);

			return;
		}

		/* sib is unneeded */
		if($modrm_reg1 != 0x04) {
			switch($modrm_mod) {
				case 0x00:
					$this->mod['offset']	= 0;
					break;

				case 0x01:
					$this->mod['byte']		= true;
					$this->mod['offset']	= $this->next_byte(false);
					break;

				case 0x02:
					$this->mod['offset']	= $this->next_dword(false);
					break;
			}

			$this->mod['method']	= 2;
			$this->mod['index']		= $modrm_reg1;
			$this->mod['scale']		= 1;

			return;
		}

		/* sib */
		$sib		= $this->next_byte(false);
		$sib_scale	= ($sib >> 6) & 0x03;
		$sib_index	= ($sib >> 3) & 0x07;
		$sib_base	= $sib & 0x07;

		switch($modrm_mod) {
			case 0x00:
				if($sib_index == 0x04) {			/* index = esp */
					$this->mod['method']	= 2;
					$this->mod['index']		= $sib_base;
					$this->mod['scale']		= 1;
					$this->mod['offset']	= 0;
				} else if($sib_base == 0x05) {		/* base = ebp */
					$this->mod['method']	= 2;
					$this->mod['index']		= $sib_index;
					$this->mod['scale']		= 1 << $sib_scale;
					$this->mod['offset']	= $this->next_dword(false);
				} else {
					$this->mod['method']	= 3;
					$this->mod['index']		= $sib_index;
					$this->mod['scale']		= 1 << $sib_scale;
					$this->mod['base']		= $sib_base;
					$this->mod['offset']	= 0;
				}

				return;

			case 0x01:
				$this->mod['byte'] = true;
				$offset = $this->next_byte(false);
				break;

			case 0x02:
				$offset = $this->next_dword(false);
				break;
		}

		if($sib_index == 0x04) {			/* index = esp */
			$this->mod['method']	= 2;
			$this->mod['index']		= $sib_base;
			$this->mod['scale']		= 1;
			$this->mod['offset']	= $offset;
		} else {
			$this->mod['method']	= 3;
			$this->mod['index']		= $sib_index;
			$this->mod['scale']		= 1 << $sib_scale;
			$this->mod['base']		= $sib_base;
			$this->mod['offset']	= $offset;
		}
	}

	/*
		condition									判断执行条件
	*/
	private function condition() {
		$reg = $this->cpu->register;

		switch($this->opcode & 0x0F) {
			case 0x00:	$this->condition = $reg->OF == 1;	break;
			case 0x01:	$this->condition = $reg->OF == 0;	break;
			case 0x02:	$this->condition = $reg->CF == 1;	break;
			case 0x03:	$this->condition = $reg->CF == 0;	break;
			case 0x04:	$this->condition = $reg->ZF == 1;	break;
			case 0x05:	$this->condition = $reg->ZF == 0;	break;
			case 0x06:	$this->condition = ($reg->CF == 1) || ($reg->ZF == 1);	break;
			case 0x07:	$this->condition = ($reg->CF == 0) && ($reg->ZF == 0);	break;
			case 0x08:	$this->condition = $reg->SF == 1;	break;
			case 0x09:	$this->condition = $reg->SF == 0;	break;
			case 0x0A:	$this->condition = $reg->PF == 1;	break;
			case 0x0B:	$this->condition = $reg->PF == 0;	break;
			case 0x0C:	$this->condition = $reg->SF != $reg->OF;	break;
			case 0x0D:	$this->condition = $reg->SF == $reg->OF;	break;
			case 0x0E:	$this->condition = ($reg->ZF == 1) || ($reg->SF != $reg->OF);	break;
			case 0x0F:	$this->condition = ($reg->ZF == 0) && ($reg->SF == $reg->OF);	break;
		}
	}

	/*
		repeat										重复执行器
	*/
	private function repeat() {
		$call		= $this->info['call'];
		$counter	= false;

		if($this->info['repeat'] == 2) {
			$single = false;
		} else {
			$single = true;
		}

		while($this->repeat_check($counter, $single)) {
			call_user_func($call);
		}
	}

	/*
		repeat_check
	*/
	private function repeat_check(&$counter, $single) {
		$ecx = $this->cpu->register->read(1);

		/* 首次执行 */
		if($counter == false) {
			$counter = true;

			/* 无前缀执行一次 */
			if($this->repeat == 0) {
				return true;
			}

			/* ecx=0 时不执行 */
			if($ecx == 0) {
				return false;
			}
		}

		/* 无前缀第二次循环 */
		if($this->repeat == 0) {
			return false;
		}

		/* ecx-- */
		if($ecx == 0) {
			$this->cpu->register->write(1, 0xFFFFFFFF);
		} else {
			$this->cpu->register->write(1, $ecx - 1);
		}

		/* ecx-1 = 0 */
		if($ecx == 1) {
			return false;
		}

		if($single) {
			/* 不允许 0xF2 前缀 */
			if($this->repeat == 2) {
				$this->exception (PLATO_EX_DECODER_REPEAT);
			}
		} else {
			$ZF = $this->cpu->register->ZF;

			switch($this->repeat) {
				/* REPNE */
				case 2:
					if($ZF == 1) {
						return false;
					}
					break;

				/* REPE */
				case 3:
					if($ZF == 0) {
						return false;
					}
					break;
			}
		}

		return true;
	}
}
