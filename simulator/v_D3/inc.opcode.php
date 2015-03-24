<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:inc/opcode   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

/*
	@ unit				- 执行单元 (方法名)

	@ opcode			- 字节码
						  新字节码写为 0x0F??
						  使用局部寄存器集或指定操作数顺序时字节码的写为 w=0, d=0
						  使用寄存器 ID 时写为使用 EAX 寄存器时的字节码
						  使用条件判断时最后一个字节写为 0

	@ w					- 字节码中最后一位是否为 w 位, 指定使用局部寄存器集

	@ d					- 字节码中倒数第二位是否为 d 位, 指定操作数顺序

	@ r					- 字节码中最后三位是否用于表示寄存器 ID

	@ c					- 字节码中最后一字节是否用于表示判断条件

	@ mod				- mod r/m 中 code 域的值
						  设置为 true 表示需要 mod r/m 但不存在 code 域, 设置为 false 表示不存在 mod r/m

	@ imm				- 立即数大小, 单位为字节
						  设置为 false 表示不需要立即数, true 为自适应, 可选长度为 1/2/4

	@ rep				- 重复前缀
						  支持 REP 前缀设置为 1, 支持 REP(N)(E/Z) 前缀设置为 2
*/

$opcode = array (
	//			unit		opcode		w	d	r	c		mod		imm		rep
	/* A */
	array (		'aaa',		0x37,		0,	0,	0,	0,		false,	false,	false	),		/* AAA */
	array (		'aad',		0xD5,		0,	0,	0,	0,		false,	1,		false	),		/* AAD */
	array (		'aam',		0xD4,		0,	0,	0,	0,		false,	1,		false	),		/* AAM */
	array (		'aas',		0x3F,		0,	0,	0,	0,		false,	false,	false	),		/* AAS */
	array (		'adc',		0x14,		1,	0,	0,	0,		false,	true,	false	),		/* ADC */
	array (		'adc',		0x80,		1,	0,	0,	0,		2,		true,	false	),
	array (		'adc',		0x83,		0,	0,	0,	0,		2,		1,		false	),
	array (		'adc',		0x10,		1,	1,	0,	0,		true,	false,	false	),
	array (		'add',		0x04,		1,	0,	0,	0,		false,	true,	false	),		/* ADD */
	array (		'add',		0x80,		1,	0,	0,	0,		0,		true,	false	),
	array (		'add',		0x83,		0,	0,	0,	0,		0,		1,		false	),
	array (		'add',		0x00,		1,	1,	0,	0,		true,	false,	false	),
	array (		'and',		0x24,		1,	0,	0,	0,		false,	true,	false	),		/* AND */
	array (		'and',		0x80,		1,	0,	0,	0,		4,		true,	false	),
	array (		'and',		0x83,		0,	0,	0,	0,		4,		1,		false	),
	array (		'and',		0x20,		1,	1,	0,	0,		true,	false,	false	),
	/* B */
	array (		'bsf',		0x0FBC,		0,	0,	0,	0,		true,	false,	false	),		/* BSF */
	array (		'bsr',		0x0FBD,		0,	0,	0,	0,		true,	false,	false	),		/* BSR */
	array (		'bswap',	0x0FC8,		0,	0,	1,	0,		false,	false,	false	),		/* BSWAP */
	array (		'bt',		0x0FA3,		0,	0,	0,	0,		true,	false,	false	),		/* BT */
	array (		'bt',		0x0FBA,		0,	0,	0,	0,		4,		1,		false	),
	array (		'btc',		0x0FBB,		0,	0,	0,	0,		true,	false,	false	),		/* BTC */
	array (		'btc',		0x0FBA,		0,	0,	0,	0,		7,		1,		false	),
	array (		'btr',		0x0FB3,		0,	0,	0,	0,		true,	false,	false	),		/* BTR */
	array (		'btr',		0x0FBA,		0,	0,	0,	0,		6,		1,		false	),
	array (		'bts',		0x0FAB,		0,	0,	0,	0,		true,	false,	false	),		/* BTS */
	array (		'bts',		0x0FBA,		0,	0,	0,	0,		5,		1,		false	),
	//			unit		opcode		w	d	r	c		mod		imm		rep
	/* C */
	array (		'call',		0xE8,		0,	0,	0,	0,		false,	true,	false	),		/* CALL */
	array (		'call',		0xFF,		0,	0,	0,	0,		2,		false,	false	),
	array (		'call',		0x9A,		0,	0,	0,	0,		false,	true,	false	),
	array (		'call',		0xFF,		0,	0,	0,	0,		3,		false,	false	),
	array (		'cbw',		0x98,		0,	0,	0,	0,		false,	false,	false	),		/* CBW/CBWE */
	array (		'clc',		0xF8,		0,	0,	0,	0,		false,	false,	false	),		/* CLC */
	array (		'cld',		0xFC,		0,	0,	0,	0,		false,	false,	false	),		/* CLD */
	array (		'cmc',		0xF5,		0,	0,	0,	0,		false,	false,	false	),		/* CMC */
	array (		'cmovcc',	0x0F40,		0,	0,	0,	1,		true,	false,	false	),		/* CMOVcc */
	array (		'cmp',		0x3C,		1,	0,	0,	0,		false,	true,	false	),		/* CMP */
	array (		'cmp',		0x80,		1,	0,	0,	0,		7,		true,	false	),
	array (		'cmp',		0x83,		0,	0,	0,	0,		7,		1,		false	),
	array (		'cmp',		0x38,		1,	1,	0,	0,		true,	false,	false	),
	array (		'cmps',		0xA6,		1,	0,	0,	0,		false,	false,	2		),		/* CMPS/CMPSB/CMPSW/CMPS */
	array (		'cmpxchg',	0x0FB0,		1,	0,	0,	0,		true,	false,	false	),		/* CMPXCHG */
	array (		'cmpxchg8b',0x0FC7,		0,	0,	0,	0,		7,		false,	false	),		/* CMPXCHG8B */
	array (		'cpuid',	0x0FA2,		0,	0,	0,	0,		false,	false,	false	),		/* CPUID */
	/* D */
	array (		'daa',		0x27,		0,	0,	0,	0,		false,	false,	false	),		/* DAA */
	array (		'das',		0x2F,		0,	0,	0,	0,		false,	false,	false	),		/* DAS */
	array (		'dec',		0xFE,		1,	0,	0,	0,		1,		false,	false	),		/* DEC */
	array (		'dec',		0x48,		0,	0,	1,	0,		false,	false,	false	),
	array (		'div',		0xF6,		1,	0,	0,	0,		6,		false,	false	),		/* DIV */
	//			unit		opcode		w	d	r	c		mod		imm		rep
	/* I */
	array (		'idiv',		0xF6,		1,	0,	0,	0,		7,		false,	false	),		/* IDIV */
	array (		'imul',		0xF6,		1,	0,	0,	0,		5,		false,	false	),		/* IMUL */
	array (		'imul',		0x0FAF,		0,	0,	0,	0,		true,	false,	false	),
	array (		'imul',		0x6B,		0,	0,	0,	0,		true,	1,		false	),
	array (		'imul',		0x69,		0,	0,	0,	0,		true,	true,	false	),
	array (		'inc',		0xFE,		1,	0,	0,	0,		0,		false,	false	),		/* INC */
	array (		'inc',		0x40,		0,	0,	1,	0,		false,	false,	false	),
	array (		'int',		0xCC,		0,	0,	0,	0,		false,	false,	false	),		/* INT 3 */
	array (		'int',		0xCD,		0,	0,	0,	0,		false,	1,		false	),		/* INT */
	array (		'int',		0xCE,		0,	0,	0,	0,		false,	false,	false	),		/* INTO */
	/* J */
	array (		'jcc',		0x70,		0,	0,	0,	1,		false,	1,		false	),		/* Jcc */
	array (		'jcc',		0x0F80,		0,	0,	0,	1,		false,	true,	false	),
	array (		'jmp',		0xEB,		0,	0,	0,	0,		false,	1,		false	),		/* JMP */
	array (		'jmp',		0xE9,		0,	0,	0,	0,		false,	true,	false	),
	array (		'jmp',		0xFF,		0,	0,	0,	0,		4,		false,	false	),
	array (		'jmp',		0xEA,		0,	0,	0,	0,		false,	true,	false	),
	array (		'jmp',		0xFF,		0,	0,	0,	0,		5,		false,	false	),
	/* L */
	array (		'lahf',		0x9F,		0,	0,	0,	0,		false,	false,	false	),		/* LAHF */
	array (		'lea',		0x8D,		0,	0,	0,	0,		true,	false,	false	),		/* LEA */
	array (		'lods',		0xAC,		1,	0,	0,	0,		false,	false,	1		),		/* LODS/LODSB/LODSW/LODSD */
	array (		'loop',		0xE2,		0,	0,	0,	0,		false,	1,		false	),		/* LOOP */
	array (		'loop',		0xE1,		0,	0,	0,	0,		false,	1,		false	),		/* LOOPcc */
	array (		'loop',		0xE0,		0,	0,	0,	0,		false,	1,		false	),
	//			unit		opcode		w	d	r	c		mod		imm		rep
	/* M */
	array (		'mov',		0x88,		1,	1,	0,	0,		true,	false,	false	),		/* MOV */
	array (		'mov',		0xB0,		0,	0,	1,	0,		false,	1,		false	),
	array (		'mov',		0xB8,		0,	0,	1,	0,		false,	true,	false	),
	array (		'mov',		0xC6,		1,	0,	0,	0,		0,		true,	false	),
	array (		'movs',		0xA4,		1,	0,	0,	0,		false,	false,	1		),		/* MOVS/MOVSB/MOVSW/MOVSD */
	array (		'movsx',	0x0FBE,		0,	0,	0,	0,		true,	false,	false	),		/* MOVSX */
	array (		'movsx',	0x0FBF,		0,	0,	0,	0,		true,	false,	false	),
	array (		'movzx',	0x0FB6,		0,	0,	0,	0,		true,	false,	false	),		/* MOVZX */
	array (		'movzx',	0x0FB7,		0,	0,	0,	0,		true,	false,	false	),
	array (		'mul',		0xF6,		1,	0,	0,	0,		4,		false,	false	),		/* MUL */
	/* N */
	array (		'neg',		0xF6,		1,	0,	0,	0,		3,		false,	false	),		/* NEG */
	array (		'not',		0xF6,		1,	0,	0,	0,		2,		false,	false	),		/* NOT */
	/* O */
	array (		'or',		0x0C,		1,	0,	0,	0,		false,	true,	false	),		/* OR */
	array (		'or',		0x80,		1,	0,	0,	0,		1,		true,	false	),
	array (		'or',		0x83,		0,	0,	0,	0,		1,		1,		false	),
	array (		'or',		0x08,		1,	1,	0,	0,		true,	false,	false	),
	//			unit		opcode		w	d	r	c		mod		imm		rep
	/* P */
	array (		'pop',		0x8F,		0,	0,	0,	0,		0,		false,	false	),		/* POP */
	array (		'pop',		0x58,		0,	0,	1,	0,		false,	false,	false	),
	array (		'pop',		0x1F,		0,	0,	0,	0,		false,	false,	false	),
	array (		'pop',		0x07,		0,	0,	0,	0,		false,	false,	false	),
	array (		'pop',		0x17,		0,	0,	0,	0,		false,	false,	false	),
	array (		'pop',		0x0FA1,		0,	0,	0,	0,		false,	false,	false	),
	array (		'pop',		0x0FA9,		0,	0,	0,	0,		false,	false,	false	),
	array (		'popa',		0x61,		0,	0,	0,	0,		false,	false,	false	),		/* POPA/POPAD */
	array (		'popf',		0x9D,		0,	0,	0,	0,		false,	false,	false	),		/* POPF/POPFD */
	array (		'push',		0xFF,		0,	0,	0,	0,		6,		false,	false	),		/* PUSH */
	array (		'push',		0x50,		0,	0,	1,	0,		false,	false,	false	),
	array (		'push',		0x6A,		0,	0,	0,	0,		false,	1,		false	),
	array (		'push',		0x68,		0,	0,	0,	0,		false,	true,	false	),
	array (		'push',		0x0E,		0,	0,	0,	0,		false,	false,	false	),
	array (		'push',		0x16,		0,	0,	0,	0,		false,	false,	false	),
	array (		'push',		0x1E,		0,	0,	0,	0,		false,	false,	false	),
	array (		'push',		0x06,		0,	0,	0,	0,		false,	false,	false	),
	array (		'push',		0x0FA0,		0,	0,	0,	0,		false,	false,	false	),
	array (		'push',		0x0FA8,		0,	0,	0,	0,		false,	false,	false	),
	array (		'pusha',	0x60,		0,	0,	0,	0,		false,	false,	false	),		/* PUSHA/PUSHAD */
	array (		'pushf',	0x9C,		0,	0,	0,	0,		false,	false,	false	),		/* PUSHF/PUSHFD */
	//			unit		opcode		w	d	r	c		mod		imm		rep
	/* R */
	array (		'rcl',		0xD0,		1,	0,	0,	0,		2,		false,	false	),		/* RCL */
	array (		'rcl',		0xD2,		1,	0,	0,	0,		2,		false,	false	),
	array (		'rcl',		0xC0,		1,	0,	0,	0,		2,		1,		false	),
	array (		'rcr',		0xD0,		1,	0,	0,	0,		3,		false,	false	),		/* RCR */
	array (		'rcr',		0xD2,		1,	0,	0,	0,		3,		false,	false	),
	array (		'rcr',		0xC0,		1,	0,	0,	0,		3,		1,		false	),
	array (		'rdtsc',	0x0F31,		0,	0,	0,	0,		false,	false,	false	),		/* RDTSC */
	array (		'ret',		0xC3,		0,	0,	0,	0,		false,	false,	false	),		/* RET */
	array (		'ret',		0xCB,		0,	0,	0,	0,		false,	false,	false	),
	array (		'ret',		0xC2,		0,	0,	0,	0,		false,	2,		false	),
	array (		'ret',		0xCA,		0,	0,	0,	0,		false,	2,		false	),
	array (		'rol',		0xD0,		1,	0,	0,	0,		0,		false,	false	),		/* ROL */
	array (		'rol',		0xD2,		1,	0,	0,	0,		0,		false,	false	),
	array (		'rol',		0xC0,		1,	0,	0,	0,		0,		1,		false	),
	array (		'ror',		0xD0,		1,	0,	0,	0,		1,		false,	false	),		/* ROR */
	array (		'ror',		0xD2,		1,	0,	0,	0,		1,		false,	false	),
	array (		'ror',		0xC0,		1,	0,	0,	0,		1,		1,		false	),
	//			unit		opcode		w	d	r	c		mod		imm		rep
	/* S */
	array (		'sahf',		0x9E,		0,	0,	0,	0,		false,	false,	false	),		/* SAHF */
	array (		'sal',		0xD0,		1,	0,	0,	0,		4,		false,	false	),		/* SAL/SHL */
	array (		'sal',		0xD2,		1,	0,	0,	0,		4,		false,	false	),
	array (		'sal',		0xC0,		1,	0,	0,	0,		4,		1,		false	),
	array (		'sar',		0xD0,		1,	0,	0,	0,		7,		false,	false	),		/* SAR */
	array (		'sar',		0xD2,		1,	0,	0,	0,		7,		false,	false	),
	array (		'sar',		0xC0,		1,	0,	0,	0,		7,		1,		false	),
	array (		'sbb',		0x1C,		1,	0,	0,	0,		false,	true,	false	),		/* SBB */
	array (		'sbb',		0x80,		1,	0,	0,	0,		3,		true,	false	),
	array (		'sbb',		0x83,		0,	0,	0,	0,		3,		1,		false	),
	array (		'sbb',		0x18,		1,	1,	0,	0,		true,	false,	false	),
	array (		'scas',		0xAE,		1,	0,	0,	0,		false,	false,	2		),		/* SCAS/SCASB/SCASW/SCASD */
	array (		'setcc',	0x0F90,		0,	0,	0,	1,		true,	false,	false	),		/* SETcc */
	array (		'shr',		0xD0,		1,	0,	0,	0,		5,		false,	false	),		/* SHR */
	array (		'shr',		0xD2,		1,	0,	0,	0,		5,		false,	false	),
	array (		'shr',		0xC0,		1,	0,	0,	0,		5,		1,		false	),
	array (		'stc',		0xF9,		0,	0,	0,	0,		false,	false,	false	),		/* STC */
	array (		'std',		0xFD,		0,	0,	0,	0,		false,	false,	false	),		/* STD */
	array (		'stos',		0xAA,		1,	0,	0,	0,		false,	false,	1		),		/* STOS/STOSB/STOSW/STOSD */
	array (		'sub',		0x2C,		1,	0,	0,	0,		false,	true,	false	),		/* SUB */
	array (		'sub',		0x80,		1,	0,	0,	0,		5,		true,	false	),
	array (		'sub',		0x83,		0,	0,	0,	0,		5,		1,		false	),
	array (		'sub',		0x28,		1,	1,	0,	0,		true,	false,	false	),
	//			unit		opcode		w	d	r	c		mod		imm		rep
	/* T */
	array (		'test',		0xA8,		1,	0,	0,	0,		false,	true,	false	),		/* TEST */
	array (		'test',		0xF6,		1,	0,	0,	0,		0,		true,	false	),
	array (		'test',		0x84,		1,	0,	0,	0,		true,	false,	false	),
	/* U */
	array (		'ud2',		0x0F0B,		0,	0,	0,	0,		false,	false,	false	),		/* UD2 */
	/* X */
	array (		'xadd',		0x0FC0,		1,	0,	0,	0,		true,	false,	false	),		/* XADD */
	array (		'xchg',		0x90,		0,	0,	1,	0,		false,	false,	false	),		/* XCHG */
	array (		'xchg',		0x86,		1,	0,	0,	0,		true,	false,	false	),
	array (		'xlat',		0xD7,		0,	0,	0,	0,		false,	false,	false	),		/* XLAT/XLATB */
	array (		'xor',		0x34,		1,	0,	0,	0,		false,	true,	false	),		/* XOR */
	array (		'xor',		0x80,		1,	0,	0,	0,		6,		true,	false	),
	array (		'xor',		0x83,		0,	0,	0,	0,		6,		1,		false	),
	array (		'xor',		0x30,		1,	1,	0,	0,		true,	false,	false	),
);
