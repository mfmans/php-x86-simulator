<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ class/alu   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_class_alu implements plato_interface_alu {
	static private $initialized = false;

	/* for sign bit test */
	static private $value_sign = array (
		1 => 0x80,
		2 => 0x8000,
		4 => /* int */	0x80000000,
	);

	/* max value */
	static private $value_max = array (
		1 => 0xFF,
		2 => 0xFFFF,
		4 => /* int */	0xFFFFFFFF,
	);

	/* higher pos value */
	static private $value_high = array (
		1 => /* int */	0xFFFFFF00,
		2 => /* int */	0xFFFF0000,
		4 => 0x00,					/* !!! unavailable */
	);

	/*
		+ initialize
	*/
	static private function initialize() {
		if(self::$initialized == true) {
			return;
		}

		/* 0x80000000 */
		self::$value_sign[4]	= (int) self::$value_sign[4];
		/* 0xFFFFFFFF */
		self::$value_max[4]		= (int) self::$value_max[4];

		/* 0xFFFFFF00 */
		self::$value_high[1]	= (int) self::$value_high[1];
		/* 0xFFFFFF00 */
		self::$value_high[2]	= (int) self::$value_high[2];

		self::$initialized = true;
	}


	private	$ldr;
	private $cpu;

	/*
		__construct
	*/
	public function __construct($ldr) {
		self::initialize();

		$this->ldr = $ldr;
		$this->cpu = $ldr->cpu;
	}

	/*
		exception
	*/
	private function exception($id) {
		throw new plato_exception ($id, array(), $this->ldr);
	}

	/*
		pack										将 2 个 32Bit 无符号整数压缩成 10 进制字符串整数
	*/
	private function pack($low, $high) {
		if($high) {
			$source = sprintf('%X%08X', $high, $low);
		} else {
			$source = sprintf('%X', $low);
		}

		$result	= '0';
		$length	= strlen($source);

		for($i = 0; $i < $length; $i++) {
			$ratio	= base_convert($source[$i], 16, 10);
			$result	= bcadd(bcmul($result, 16), $ratio);
		}

		return $result;
	}

	/*
		unpack										10 进制字符串整数拆解成 2 个 32Bit 整数
	*/
	private function unpack($number) {
		/* 符号位 */
		if($number[0] == '-') {
			$sign	= true;
			$number	= substr($number, 1);
		} else {
			$sign	= false;
		}

		$result = '';

		/* 整除求余法 */
		while(bccomp($number, '0', 0) > 0) {
			$mod	= intval(bcmod($number, 16));
			$result	= base_convert($mod, 10, 16).$result;

			$number	= bcdiv($number, 16, 0);
		}

		/* 补足长度 */
		$result = str_pad($result, 16, '0', STR_PAD_LEFT);

		/* 拆解高低双字 */
		$return = array (
			/* L */		(int) hexdec(substr($result, 8, 8)),
			/* H */		(int) hexdec(substr($result, 0, 8))
		);

		/* 处理负数 */
		if($sign) {
			/* 取反 */
			$return[0] = ~$return[0];
			$return[1] = ~$return[1];

			/* 测试进位 */
			if($return[0] == self::$value_max[4]) {
				$carry = 1;
			} else {
				$carry = 0;
			}

			/* 低位 +1 */
			$return[0]++;

			/* 进位到高位 */
			if($carry) {
				$return[1]++;
			}
		}

		return $return;
	}

	/*
		flag
	*/
	public function flag($number, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		/* zero check */
		if(($number & self::$value_max[$opsize]) == 0) {
			$this->cpu->register->ZF = 1;
		} else {
			$this->cpu->register->ZF = 0;
		}

		/* sign check */
		if($number & self::$value_sign[$opsize]) {
			$this->cpu->register->SF = 1;
		} else {
			$this->cpu->register->SF = 0;
		}

		/* bit check */
		for($i = 0, $j = $opsize << 3, $count = 0; $i < $j; $i++) {
			if($number & 0x01) {
				$count++;
			}

			$number = $number >> 1;
		}

		if($count % 2) {
			$this->cpu->register->PF = 0;
		} else {
			$this->cpu->register->PF = 1;
		}
	}

	/*
		extend
	*/
	public function extend($number, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		if($number & self::$value_sign[$opsize]) {
			return (int) ($number | self::$value_high[$opsize]);
		} else {
			return $number;
		}
	}

	/*
		arith_add
	*/
	public function arith_add($number1, $number2, $carry = false, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		$max	= self::$value_max[$opsize];
		$sign	= self::$value_sign[$opsize];

		if($carry) {
			if($carry = $this->cpu->register->CF) {
				if(($number2 & $max) == $max) {
					$number2	= 0;
				} else {
					$carry		= 0;
					$number2++;
				}
			} else {
				$carry = 0;
			}
		} else {
			$carry = 0;
		}

		if($number1 == 0) {
			$result = $number2;
		} else if($number2 == 0) {
			$result = $number1;
		} else {
			switch($opsize) {
				case 1:
				case 2:
					$result = $number1 + $number2;

					if($result & self::$value_high[$opsize]) {
						$carry = 1;
					} else {
						$carry = 0;
					}

					break;

				case 4:
					$low1	= $number1 & 0xFFFF;
					$low2	= $number2 & 0xFFFF;
					$high1	= ($number1 >> 16) & 0xFFFF;
					$high2	= ($number2 >> 16) & 0xFFFF;

					$res1	= $low1 + $low2;
					$res2	= $high1 + $high2 + ($res1 >> 16);

					$result	= ($res2 << 16) | ($res1 & 0xFFFF);

					if($res2 & self::$value_high[2]) {
						$carry = 1;
					} else {
						$carry = 0;
					}

					break;
			}
		}

		/* set carry */
		$this->cpu->register->CF = $carry;

		/* sign */
		$sign1	= $sign & $number1;
		$sign2	= $sign & $number2;
		$signr	= $sign & $result;

		/* overflow */
		if(($sign1 == $sign2) && ($sign1 != $signr)) {
			$this->cpu->register->OF = 1;
		} else {
			$this->cpu->register->OF = 0;
		}

		/* half byte */
		$byte1	= $number1 & 0x0F;
		$byte2	= $number2 & 0x0F;
		$byter	= $byte1 + $byte2;

		/* half byte carry */
		if($byter & 0xF0) {
			$this->cpu->register->AF = 1;
		} else {
			$this->cpu->register->AF = 0;
		}

		$this->flag($result, $opsize);

		return $result;
	}

	/*
		arith_sub
	*/
	public function arith_sub($minuend, $subtrahend, $carry = false, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		$max = self::$value_max[$opsize];

		/* 借位判断 */
		do {
			if($carry) {
				if($carry = $this->cpu->register->CF) {
					if(($subtrahend & $max) == $max) {
						$subtrahend = 0;

						break;
					} else {
						$subtrahend++;
					}
				}
			}
		
			if($minuend < $subtrahend) {
				$carry = 1;
			} else {
				$carry = 0;
			}
		} while(0);

		/* 补码加法 */
		$result = $this->arith_add($minuend, ((~$subtrahend) + 1) & $max, false, $opsize);

		if($carry) {
			$this->cpu->register->CF = 1;
		} else {
			$this->cpu->register->CF = 0;
		}

		return $result;
	}

	/*
		arith_mul
	*/
	public function arith_mul($number1, $number2, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		switch($opsize) {
			case 1:
			case 2:
				$max		= self::$value_max[$opsize];

				$number1	= $number1 & $max;
				$number2	= $number2 & $max;

				$result	= $number1 * $number2;

				$return	= array (
					/* L */		$result & $max,
					/* H */		($result >> ($opsize << 3)) & $max
				);

				break;

			case 4:
				$number1	= sprintf('%u', $number1);
				$number2	= sprintf('%u', $number2);

				$result	= bcmul($number1, $number2, 0);
				$return	= $this->unpack($result);

				break;
		}

		if($return[1]) {
			$this->cpu->register->OF = 1;
			$this->cpu->register->CF = 1;
		} else {
			$this->cpu->register->OF = 0;
			$this->cpu->register->CF = 0;
		}

		return $return;
	}

	/*
		arith_imul
	*/
	public function arith_imul($number1, $number2, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		$max	= self::$value_max[$opsize];
		$sign	= self::$value_sign[$opsize];

		switch($opsize) {
			case 1:
			case 2:
				/* 去除符号位 */
				if($sign1 = ($number1 & $sign)) {
					$number1 = (~($number1 - 1)) & $max;
				}
				if($sign2 = ($number2 & $sign)) {
					$number2 = (~($number2 - 1)) & $max;
				}

				/* 计算结果 */
				$result	= $number1 * $number2;

				/* 判断有效进位 */
				if($result & self::$value_high[$opsize]) {
					$carry = 1;
				} else {
					$carry = 0;
				}

				/* 计算符号位 */
				if($sign1 != $sign2) {
					$result = ((~$result) + 1) & self::$value_max[$opsize << 1];
				}

				$return	= array (
					/* L */		$result & $max,
					/* H */		($result >> ($opsize << 3)) & $max
				);

				break;

			case 4:
				$result = bcmul((string) $number1, (string) $number2, 0);
				$return	= $this->unpack($result);

				/* 进位判断  unbelievable !!! */
				if(($return[1] == 0) || ($return[1] == $max)) {
					/* 判断是否为符号扩展 */
					if(($return[1] & $sign) == ($return[0] & $sign)) {
						$carry = 0;
					} else {
						$carry = 1;
					}
				} else {
					$carry = 1;
				}

				break;
		}

		if($carry) {
			$this->cpu->register->OF = 1;
			$this->cpu->register->CF = 1;
		} else {
			$this->cpu->register->OF = 0;
			$this->cpu->register->CF = 0;
		}

		return $return;
	}

	/*
		arith_div
	*/
	public function arith_div($dividend_l, $dividend_h, $divisor, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}
		if($divisor == 0) {
			$this->exception (PLATO_EX_ALU_DIVISION_ZERO);
		}

		$max = self::$value_max[$opsize];

		switch($opsize) {
			case 1:
			case 2:
				$dividend_l	= $dividend_l & $max;
				$dividend_h	= $dividend_h & $max;

				$dividend_l	= $dividend_l | ($dividend_h << ($opsize << 3));
				$dividend_h	= 0;
		}

		$dividend	= $this->pack($dividend_l, $dividend_h);
		$divisor	= sprintf('%u', $divisor);

		$quotient	= bcdiv($dividend, $divisor, 0);
		$quotient	= $this->unpack($quotient);

		$remainder	= bcmod($dividend, $divisor);
		$remainder	= $this->unpack($remainder);

		/* 商溢出 */
		if($quotient[1] || ($quotient[0] & self::$value_high[$opsize])) {
			$this->exception (PLATO_EX_ALU_DIVISION_OVERFLOW);
		}

		return array (
			$quotient[0]	& $max,
			$remainder[0]	& $max
		);
	}

	/*
		arith_idiv
	*/
	public function arith_idiv($dividend_l, $dividend_h, $divisor, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}
		if($divisor == 0) {
			$this->exception (PLATO_EX_ALU_DIVISION_ZERO);
		}

		$max	= self::$value_max[$opsize];
		$sign	= self::$value_sign[$opsize];

		$dividend_l	= $dividend_l & $max;
		$dividend_h	= $dividend_h & $max;

		/* 判断符号 */
		if($dividend_h & $sign) {
			/* 被除数低位 -1 */
			if($dividend_l == 0) {
				$dividend_l = $max;
				$carry		= true;
			} else {
				$dividend_l--;
				$carry		= false;
			}

			/* 低位 -1 时发生借位 */
			if($carry) {
				$dividend_h--;
			}

			/* 全部取反 */
			$dividend_l	= ~$dividend_l;
			$dividend_h	= ~$dividend_h;

			$negetive = true;
		} else {
			$negetive = false;
		}

		switch($opsize) {
			case 1:
			case 2:
				$dividend_l	= $dividend_l & $max;
				$dividend_h	= $dividend_h & $max;

				$dividend_l	= $dividend_l | ($dividend_h << ($opsize << 3));
				$dividend_h	= 0;
		}

		$dividend	= $this->pack($dividend_l, $dividend_h);
		$divisor	= sprintf('%d', $divisor);

		if($negetive) {
			$dividend = '-'.$dividend;
		}

		$quotient	= bcdiv($dividend, $divisor, 0);
		$quotient	= $this->unpack($quotient);

		$remainder	= bcmod($dividend, $divisor);
		$remainder	= $this->unpack($remainder);

		/* 商溢出 */
		do {
			if(($quotient[1] == 0) || ($quotient[1] == $max)) {
				if(($quotient[1] & $sign) == ($quotient[0] & $sign)) {
					break;
				}
			}

			$this->exception (PLATO_EX_ALU_DIVISION_OVERFLOW);
		} while(0);

		return array (
			$quotient[0]	& $max,
			$remainder[0]	& $max
		);
	}

	/*
		bit_get
	*/
	public function bit_get($number, $position, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		$position = $position % ($opsize << 3);

		return ($number >> $position) & 0x01;
	}

	/*
		bit_set
	*/
	public function bit_set($number, $position, $set, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		$position = $position % ($opsize << 3);
		$template = 0x01 << $position;

		if($set) {
			return $number | $template;
		} else {
			return $number & ~$template;
		}
	}

	/*
		logic_and
	*/
	public function logic_and($number1, $number2) {
		$result = (int) ($number1 & $number2);

		$this->flag($result);

		$this->cpu->register->CF = 0;
		$this->cpu->register->OF = 0;

		return $result;
	}

	/*
		logic_or
	*/
	public function logic_or($number1, $number2) {
		$result = (int) ($number1 | $number2);

		$this->flag($result);

		$this->cpu->register->CF = 0;
		$this->cpu->register->OF = 0;

		return $result;
	}

	/*
		logic_xor
	*/
	public function logic_xor($number1, $number2) {
		$result = (int) ($number1 ^ $number2);

		$this->flag($result);

		$this->cpu->register->CF = 0;
		$this->cpu->register->OF = 0;

		return $result;
	}

	/*
		logic_not
	*/
	public function logic_not($number) {
		return (int) ~$number;
	}

	/*
		logic_neg
	*/
	public function logic_neg($number) {
		return -((int) $number);
	}

	/*
		shift_left
	*/
	public function shift_left($number, $position, $logic = false, $circle = false, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		/* 只保留低 5 位 */
		$position = $position & 0x1F;

		/* 超出范围 */
		if($position > $opsize) {
			/* 非循环移位, 有效位全部移出 */
			if($circle == false) {
				/* 最后移出位 */
				if($position == ($opsize + 1)) {
					/* 恰好最低位移出 */
					$last = $number & 0x01;
				} else {
					$last = 0;
				}

				return array (0, $last, 0);
			}

			/* 循环移位求模 */
			$position = $position % $opsize;
		}

		/* 最后移出位 */
		if($circle && ($logic == false)) {
			$last = $this->cpu->register->CF;
		} else {
			$last = 0;
		}

		/* 最高位检测 */
		$check = self::$value_sign[$opsize];

		for($i = 0; $i < $position; $i++) {
			if(($number & $check) == $check) {
				$high = 1;
			} else {
				$high = 0;
			}

			/* 逻辑左移 */
			$number = $number << 1;

			/* 循环移位 */
			if($circle) {
				if($logic) {		/* 循环逻辑移位 */
					$number = $number | $high;
				} else {			/* 循环算术移位, 移入 CF */
					$number = $number | $last;
				}
			}

			$last = $high;
		}

		/* 结果最高有效位 */
		if($number & $check) {
			$high = 1;
		} else {
			$high = 0;
		}

		$this->flag($number, $opsize);

		return array ($number, $last, $high);
	}

	/*
		shift_right
	*/
	public function shift_right($number, $position, $logic = false, $circle = false, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		$check		= self::$value_sign[$opsize];
		$sign		= $number & $check;

		$bit		= $opsize << 3;
		$offset		= $bit - 1;

		$position	= $position & 0x1F;

		if($position > $opsize) {
			if($circle == false) {
				if($sign) {
					$last	= 1;
					$number	= self::$value_max[$opsize];
				} else {
					$last	= 0;
					$number	= 0;
				}

				/* 逻辑移位 */
				if($logic) {
					if($position > ($opsize + 1)) {
						$last = 0;
					}

					$number	= 0;
				}

				return array ($number, $last, 0);
			}

			/* 循环移位求模 */
			$position = $position % $opsize;
		}

		if($circle && ($logic == false)) {
			$last = $this->cpu->register->CF;
		} else {
			$last = 0;
		}

		/* 保存位 */
		switch($opsize) {
			case 1: $base = 0x7F;		break;
			case 2: $base = 0x7FFF;		break;
			case 4: $base = 0x7FFFFFFF;	break;
		}

		for($i = 0; $i < $position; $i++) {
			$low	= $number & 0x01;
			$number	= $number >> 1;			/* !!! 算术右移 */

			/* 清空高位 */
			$number	= $number & $base;

			if($circle) {
				if($logic) {		/* 循环逻辑右移, 移入最低位 */
					$number = $number | ($low << $offset);
				} else {			/* 循环算术右移, 移入 CF */
					$number = $number | ($last << $offset);
				}
			} else {
				if($logic == false) {
					$number = $number | $sign;
				}
			}

			$last = $low;
		}

		if($number & $check) {
			$high = 1;
		} else {
			$high = 0;
		}

		$this->flag($number, $opsize);

		return array ($number, $last, $high);
	}
}
