<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ class/compiler   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_class_compiler implements plato_interface_compiler {
	/* ¶ÔÏó»º´æ */
	static private $object = array();

	/*
		+ create
	*/
	static public function & create($version, $file) {
		if(!isset(self::$object[$version])) {
			self::$object[$version] = new plato_class_compiler ($version, $file);
		}

		return self::$object[$version];
	}


	/* opcode table */
	public	$code;

	/* version */
	private	$version;

	/* file path */
	private $file_data;
	private	$file_cache;

	/*
		__construct
	*/
	private function __construct($version, $file) {
		$this->code			= array();
		$this->version		= sprintf('%X', $version);

		$this->file_data	= $file;
		$this->file_cache	= '';

		if(defined('PLATO_COMPILE_ROOT') && PLATO_COMPILE_ROOT) {
			$cache = true;

			$this->file_cache = PLATO_COMPILE_ROOT.$this->version.'.php';

			/* load from cache */
			if($this->cache_read()) {
				return true;
			}
		} else {
			$cache = false;
		}

		$this->load();

		if($cache) {
			$this->cache_write();
		}
	}

	/*
		exception
	*/
	private function exception($id, $comment = array()) {
		$runtime = array (
			'version'		=> $this->version,
			'file_data'		=> $this->file_data,
			'file_cache'	=> $this->file_cache
		);

		throw new plato_exception (
			$id,
			array_merge($runtime, $comment)
		);
	}

	/*
		cache_read
	*/
	private function cache_read() {
		if(!file_exists($this->file_cache)) {
			return false;
		}

		$code		= array();
		$refresh	= true;

		if(defined('PLATO_COMPILE_REFRESH')) {
			$refresh = PLATO_COMPILE_REFRESH;
		}

		/* check expire */
		if($refresh) {
			$source	= @filemtime($this->file_data);
			$target	= @filemtime($this->file_cache);

			if($target < $source) {
				return false;
			}

			if(is_int($refresh) && (($target - $source) > $refresh)) {
				return false;
			}
		}

		@include $this->file_cache;

		if(!is_array($code) || empty($code)) {
			return false;
		}

		$this->code = $code;

		return true;
	}

	/*
		cache_write
	*/
	private function cache_write() {
		if(!$fp = @fopen($this->file_cache, 'w')) {
			return false;
		}

		@flock($fp, LOCK_EX);

		@fwrite($fp, "<"."?php\n\n");
		@fwrite($fp, "/*\n");
		@fwrite($fp, "\t\$ Plato x86 Simulator   (C) 2005-2013 MF\n");
		@fwrite($fp, "\t\$ ".$this->version.":cache   #D3\n");
		@fwrite($fp, "*/\n\n");
		@fwrite($fp, "\$code = ".var_export($this->code, true).";\n");

		/* for PHP >= 5.3.2 */
		@flock($fp, LOCK_UN);

		@fclose($fp);

		return true;
	}

	/*
		load
	*/
	private function load() {
		$opcode	= array();

		/* load from data file */
		@include $this->file_data;

		if(!is_array($opcode) || empty($opcode)) {
			$this->exception (PLATO_EX_COMPILER_FILE_LOAD);
		}

		$this->compile($opcode);
	}

	/*
		compile
	*/
	private function compile(&$opcode) {
		foreach($opcode as $code) {
			if(!is_array($code) || !isset($code[8])) {
				$this->exception (
					PLATO_EX_COMPILER_OPCODE_INVALID,
					array ('code' => $code)
				);
			}

			/* with register */
			if($code[4]) {
				$this->add_with_register($code);
				continue;
			}

			/* with c bit */
			if($code[5]) {
				$this->add_with_condition($code);
				continue;
			}

			if(($code[2] == 0) && ($code[3] == 0)) {		/* without w bit and d bit */
				$this->add($code);
			} else if($code[3] == 0) {						/* with w bit */
				$this->add_with_word($code);
			} else if($code[2] == 0) {						/* with d bit */
				$this->add_with_direction($code);
			} else {										/* with w bit and d bit */
				$this->add_with_word_direction($code);
			}
		}
	}

	/*
		add
	*/
	private function add(&$code) {
		$this->append($code);
	}

	/*
		add_with_register
	*/
	private function add_with_register(&$code) {
		/* for EAX(0) to EDI(7) */
		for($i = 0; $i < 8; $i++) {
			$this->append($code, $code[1] + $i);
		}
	}

	/*
		add_with_condition
	*/
	private function add_with_condition(&$code) {
		/* from 0x00 to 0x0F */
		for($i = 0; $i < 0x10; $i++) {
			$this->append($code, $code[1] + $i);
		}
	}

	/*
		add_with_word
	*/
	private function add_with_word(&$code) {
		/* 8-bit mode */
		$this->append($code);

		/* word/dword mode */
		$this->append($code, $code[1] | 0x01);
	}

	/*
		add_with_direction
	*/
	private function add_with_direction(&$code) {
		/* CODE r/m, r */
		$this->append($code);

		/* CODE r, r/m */
		$this->append($code, $code[1] | 0x02);
	}

	/*
		add_with_word_direction
	*/
	private function add_with_word_direction(&$code) {
		/* CODE r8/m8, r */
		$this->append($code);
		/* CODE r/m, r */
		$this->append($code, $code[1] | 0x01);

		/* CODE r8, r8/m8 */
		$this->append($code, $code[1] | 0x02);
		/* CODE r, r/m */
		$this->append($code, $code[1] | 0x03);
	}

	/*
		append
	*/
	private function append($code, $opcode = null) {
		if($opcode === null) {
			$index	= $code[1];
			$opcode	= $index;
		} else {
			$index	= $opcode;
		}

		/* mod r/m code reg */
		if(is_int($code[6])) {
			/* code reg symbol */
			$this->code[$index] = false;

			/* high word: opcode, low word: code reg */
			$index = ($index << 16) | $code[6];
		}

		/* 8-bit code with 8-bit imm */
		if($code[2] && (($opcode & 0x01) == 0)) {
			$byte = true;
		} else {
			$byte = false;
		}

		/* direction */
		if($code[3]) {
			$direction = ($opcode >> 1) & 0x01;
		} else {
			$direction = false;
		}

		/* raw data */
		$raw = $code;
		unset($raw[0]);

		$class	= 'plato_version_'.$this->version.'_module_'.strtolower($code[0][0]);
		$method	= 'unit_'.$code[0];

		$this->code[$index] = array (
			/* callable */
			'call'		=> array ($class, $method),
			/* real opcode */
			'code'		=> $opcode,
			/* base code */
			'base'		=> $code[1],
			/* byte opcode */
			'byte'		=> $byte,

			/* mod r/m required */
			'mod'		=> $code[6] !== false,
			/* imm size */
			'imm'		=> $code[7],
			/* direction */
			'direction'	=> $direction,
			/* condition */
			'condition'	=> (bool) $code[5],
			/* repeat mode */
			'repeat'	=> $code[8],

			/* raw data */
			'raw'		=> $raw,
		);
	}
}
