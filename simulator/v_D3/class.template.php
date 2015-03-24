<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/template   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_template extends plato_version_D3_module {
	/*
		calculate									公共运算器
	*/
	static public function calculate($code1, $code2, $code3, $code4, $call) {
		switch(parent::$opbase) {
			/* ??? AL,imm8 */
			/* ??? AX,imm16 */
			/* ??? EAX,imm32 */
			case $code1:
				$number1	= parent::$register->read(0);
				$number2	= parent::$imm;

				$result		= call_user_func($call, $number1, $number2);

				if($result !== null) {
					parent::$register->write(0, $result);
				}

				break;

			/* ??? r/m8,imm8 */
			/* ??? r/m16,imm16 */
			/* ??? r/m32,imm32 */
			case $code2:
				$number1	= parent::$cpu->read_address();
				$number2	= parent::$imm;

				$result		= call_user_func($call, $number1, $number2);

				if($result !== null) {
					parent::$cpu->write_address($result);
				}

				break;

			/* ??? r/m16,imm8 */
			/* ??? r/m32,imm8 */
			case $code3:
				$number1	= parent::$cpu->read_address();
				$number2	= parent::$alu->extend(parent::$imm, 1);

				$result		= call_user_func($call, $number1, $number2);

				if($result !== null) {
					parent::$cpu->write_address($result);
				}

				break;

			/* ??? r/m8,r8 */
			/* ??? r/m16,r16 */
			/* ??? r/m32,r32 */
			/* ??? r8,r/m8 */
			/* ??? r16,r/m16 */
			/* ??? r32,r/m32 */
			case $code4:
				$number1	= parent::$cpu->read_address(1);
				$number2	= parent::$cpu->read_address(2);

				$result		= call_user_func($call, $number1, $number2);

				if($result !== null) {
					parent::$cpu->write_address($result, 1);
				}

				break;
		}
	}

	/*
		bit											位运算器
	*/
	static public function bit($code1, $code2) {
		$bit = parent::$opsize << 3;

		switch(parent::$opcode) {
			/* ??? r/m16,r16 */
			/* ??? r/m32,r32 */
			case $code1:
				$imm	= false;
				$offset	= parent::$register->read(parent::$mod['code']);

				break;

			/* ??? r/m16,imm8 */
			/* ??? r/m32,imm8 */
			case $code2:
				$imm	= true;
				$offset	= parent::$imm;

				break;
		}

		if(parent::$mod['mode']) {
			/* 基址为内存操作数, 偏移量为立即数, 忽略高位 */
			if($imm) {
				if($bit == 16) {
					$offset = $offset & 0x07;
				} else {
					$offset = $offset & 0x1F;
				}
			} else {
				$offset = parent::$alu->extend($offset);
			}

			/* 修正地址 */
			parent::$decoder->offset(parent::$opsize * (int) ($offset / $bit));

			$offset = abs((int) ($offset % $bit));
		} else {
			/* 基址为寄存器操作数, 直接求模 */
			$offset = $offset % $bit;
		}

		$number	= parent::$cpu->read_address();
		$check	= parent::$alu->bit_get($number, $offset);

		return array ($check, $number);
	}

	/*
		increase									自增减
	*/
	static public function increase($code1, $code2, $call) {
		switch(parent::$opbase) {
			/* ??? r/m8 */
			/* ??? r/m16 */
			/* ??? r/m32 */
			case $code1:
				$number = parent::$cpu->read_address();
				$number = call_user_func($call, $number);

				parent::$cpu->write_address($number);

				break;

			/* ??? r16 */
			/* ??? r32 */
			case $code2:
				$number = parent::$register->read(parent::$opcode);
				$number = call_user_func($call, $number);

				parent::$register->write(parent::$opcode, $number);

				break;
		}
	}

	/*
		div											除法器
	*/
	static public function div($call) {
		switch(parent::$opcode) {
			/* ??? r/m8 */
			case 0xF6:
				$low		= parent::$register->ax;
				$high		= 0;

				$divisor	= parent::$cpu->read_address(0, 1);
				$result		= parent::$alu->$call($low, $high, $divisor, 1);

				parent::$register->al = $result[0];
				parent::$register->ah = $result[1];

				break;

			/* ??? r/m16 */
			/* ??? r/m32 */
			case 0xF7:
				$low		= parent::$register->read(0);
				$high		= parent::$register->read(2);

				$divisor	= parent::$cpu->read_address();
				$result		= parent::$alu->$call($low, $high, $divisor);

				parent::$register->write(0, $result[0]);
				parent::$register->write(2, $result[1]);

				break;
		}
	}

	/*
		shift										移位
	*/
	static public function shift($code1, $code2, $code3, $call, $logic, $circle) {
		switch(parent::$opbase) {
			/* ??? r/m8,1 */
			/* ??? r/m16,1 */
			/* ??? r/m32,1 */
			case $code1:
				$position = 1;
				break;

			/* ??? r/m8,CL */
			/* ??? r/m16,CL */
			/* ??? r/m32,CL */
			case $code2:
				$position = parent::$register->cl;
				break;

			/* ??? r/m8,imm8 */
			/* ??? r/m16,imm8 */
			/* ??? r/m32,imm8 */
			case $code3:
				$position = parent::$imm;
				break;
		}

		$number = parent::$cpu->read_address();
		$number = parent::$alu->$call($number, $position, false, true);

		parent::$cpu->write_address($number[0]);

		$cpu->register->CF	= $number[1];

		$number['position']	= $position;

		return $number;
	}
}
