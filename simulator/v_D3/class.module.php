<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/module   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

class plato_version_D3_module implements plato_interface_module {
	static private $module = array (
		'a', 'b', 'c', 'd', 'i', 'j', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'x'
	);

	static protected $ldr;
	static protected $cpu;
	static protected $decoder;
	static protected $register;
	static protected $alu;

	/* at decoder */
	static protected $info;
	static protected $opcode;
	static protected $opbase;
	static protected $opsize;
	static protected $mod;
	static protected $imm;
	static protected $direction;
	static protected $condition;

	/*
		+ initialize
	*/
	static public function initialize($ldr) {
		self::$ldr		= $ldr;
		self::$cpu		= $ldr->cpu;
		self::$decoder	= $ldr->cpu->decoder;
		self::$register	= $ldr->cpu->register;
		self::$alu		= $ldr->cpu->alu;

		self::$info		= & $ldr->cpu->decoder->info;
		self::$opcode	= & $ldr->cpu->decoder->opcode;
		self::$opbase	= & $ldr->cpu->decoder->opbase;
		self::$opsize	= & $ldr->cpu->decoder->opsize;
		self::$mod		= & $ldr->cpu->decoder->mod;
		self::$imm		= & $ldr->cpu->decoder->imm;

		self::$direction	= & $ldr->cpu->decoder->direction;
		self::$condition	= & $ldr->cpu->decoder->condition;

		if(!empty(self::$module)) {
			$prefix = dirname(__FILE__).'/module/class.';

			foreach(self::$module as $item) {
				require_once $prefix.$item.'.php';
			}

			self::$module = false;
		}
	}

	/*
		+ exception
	*/
	static protected function exception($id) {
		throw new plato_exception ($id, array(), self::$ldr);
	}
}
