<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ class/disassemble   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_class_disassemble {
	/*
		__construct
	*/
	private function __construct() {
		// do nothing
	}

	/*
		conv_condition
	*/
	static private function conv_condition($call, $code) {
		$condition = 'cc';

		switch($code & 0x0F) {
			case 0x00: $condition = 'O';	break;
			case 0x01: $condition = 'NO';	break;
			case 0x02: $condition = 'B';	break;
			case 0x03: $condition = 'NB';	break;
			case 0x04: $condition = 'Z';	break;
			case 0x05: $condition = 'NZ';	break;
			case 0x06: $condition = 'BE';	break;
			case 0x07: $condition = 'A';	break;
			case 0x08: $condition = 'S';	break;
			case 0x09: $condition = 'NS';	break;
			case 0x0A: $condition = 'P';	break;
			case 0x0B: $condition = 'PO';	break;
			case 0x0C: $condition = 'L';	break;
			case 0x0D: $condition = 'NL';	break;
			case 0x0E: $condition = 'LE';	break;
			case 0x0F: $condition = 'NLE';	break;
		}

		return substr($call, 0, -2).$condition;
	}

	/*
		conv_mod
	*/
	static private function conv_mod($cpu) {
		$mod = $cpu->decoder->mod;

		switch($mod['method']) {
			case 0:
				return self::conv_register($mod['register'], 4);

			case 1:
				return '['.sprintf('0x%08X', $mod['address']).']';

			case 2:
			case 3:
				$info = self::conv_register($mod['index'], 4);

				if($mod['scale'] > 1) {
					$info .= '*'.$mod['scale'];
				}
				if(isset($mod['base'])) {
					$info .= '+'.self::conv_register($mod['base'], 4);
				}
				if(isset($mod['offset']) && $mod['offset']) {
					if($mod['byte']) {
						$offset = $cpu->alu->extend($mod['offset'], 1);
					} else {
						$offset = $mod['offset'];
					}

					if($offset < 0) {
						$info .= $offset;
					} else {
						$info .= '+'.$offset;
					}
				}

				return '['.$info.']';
		}

		return '?';
	}

	/*
		conv_register
	*/
	static private function conv_register($id, $opsize) {
		$name = '?';

		if($opsize == 1) {
			switch($id) {
				case 0: $name = 'AL'; break;
				case 1: $name = 'CL'; break;
				case 2: $name = 'DL'; break;
				case 3: $name = 'BL'; break;
				case 4: $name = 'AH'; break;
				case 5: $name = 'CH'; break;
				case 6: $name = 'DH'; break;
				case 7: $name = 'BH'; break;
			}
		} else {
			switch($id) {
				case 0: $name = 'EAX'; break;
				case 1: $name = 'ECX'; break;
				case 2: $name = 'EDX'; break;
				case 3: $name = 'EBX'; break;
				case 4: $name = 'ESP'; break;
				case 5: $name = 'EBP'; break;
				case 6: $name = 'ESI'; break;
				case 7: $name = 'EDI'; break;
			}

			if($opsize == 2) {
				$name = substr($name, 1);
			}
		}

		return $name;
	}

	/*
		parse
	*/
	static public function parse($ldr) {
		if(!is_object($ldr) || !is_object($ldr->cpu)) {
			return false;
		}

		$cpu	= $ldr->cpu;
		$decdr	= $cpu->decoder;
		$info	= $decdr->info;

		if(empty($info) || empty($info['call'])) {
			return false;
		}

		$code	= strtoupper(substr($info['call'][1], 5));
		$args	= array();

		if($info['condition']) {
			$code = self::conv_condition($code, $info['code']);
		}

		/* 重复前缀 */
		switch($decdr->repeat) {
			case 2:
				$code = 'REPNE '.$code;
				break;

			case 3:
				if($info['repeat'] == 2) {
					$code = 'REPE '.$code;
				} else {
					$code = 'REP '.$code;
				}
				break;
		}

		/* 寄存器操作数 */
		if($info['raw'][4]) {
			$code .= ' '.self::conv_register($decdr->opcode - $decdr->opbase, $decdr->opsize);
		}

		/* 寻址信息 */
		if($info['mod']) {
			if(is_int($info['raw'][6])) {
				$args[] = self::conv_mod($cpu);
			} else {
				if($info['direction'] === 0) {
					$args[] = self::conv_mod($cpu);
					$args[] = self::conv_register($decdr->mod['code'], $decdr->opsize);
				} else {
					$args[] = self::conv_register($decdr->mod['code'], $decdr->opsize);
					$args[] = self::conv_mod($cpu);
				}
			}
		}

		/* 立即数 */
		if($info['imm'] !== false) {
			if($info['imm'] === true) {
				$size = $decdr->opsize;
			} else {
				$size = $info['imm'];
			}

			$args[] = sprintf('0x%0'.($size * 2).'X', $decdr->imm);
		}

		if(!empty($args)) {
			$code .= ' '.implode(', ', $args);
		}

		return $code;
	}
}
