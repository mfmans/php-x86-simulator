<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ class/loader   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_class_loader implements plato_interface_loader {
	/* 支持的版本及对应配置 */
	static private $support = array (
		0xD3 => array()
	);

	/* 对象缓存 */
	static private $object		= array();
	/* 初始化 */
	static private $initialized	= false;

	/*
		+ create
	*/
	static public function create($file) {
		self::initialize();

		if($hash = self::hash($file)) {
			if(isset(self::$object[$hash])) {
				return self::$object[$hash];
			}
		}

		return new plato_class_loader ($file);
	}

	/*
		+ hash
	*/
	static private function hash($file) {
		if($file = trim($file)) {
			if($file = realpath($file)) {
				return md5($file);
			}
		}

		return false;
	}

	/*
		+ initialize
	*/
	static private function initialize() {
		if(self::$initialized == true) {
			return;
		}

		require_once PLATO_ROOT.'class.bin.php';
		require_once PLATO_ROOT.'class.compiler.php';
		require_once PLATO_ROOT.'class.memory.php';
		require_once PLATO_ROOT.'class.cpu.php';
		require_once PLATO_ROOT.'class.register.php';
		require_once PLATO_ROOT.'class.alu.php';
		require_once PLATO_ROOT.'class.decoder.php';

		self::$initialized = true;
	}


	/* index */
	private	$hash;
	/* file */
	private	$file;
	private	$fp;
	/* version */
	private	$version;

	/* debug function */
	public	$debug;

	/* configuration */
	public	$config;

	/* virtual machine */
	public	$bin;
	public	$compiler;
	public	$memory;
	public	$cpu;
	public	$service;

	/* address */
	public	$address_base;
	public	$address_table;
	public	$address_main;

	/*
		__construct
	*/
	private function __construct($file) {
		$this->hash	= self::hash($file);

		$this->file	= $file;
		$this->fp	= null;

		$this->version			= 0;
		$this->address_table	= 0;
		$this->address_main		= 0;
	}

	/*
		exception
	*/
	private function exception($id, $comment = array()) {
		$runtime = array (
			'file'				=> $this->file,
			'version%X'			=> $this->version,
			'address_table%08X'	=> $this->address_table,
			'address_main%08X'	=> $this->address_main,
		);

		throw new plato_exception (
			$id,
			array_merge($runtime, $comment),
			$this
		);
	}

	/*
		load
	*/
	public function load($debug = null) {
		/* file loaded */
		if(is_resource($this->fp)) {
			return true;
		}

		if(!$this->fp = @fopen($this->file, 'rb')) {
			$this->exception (PLATO_EX_LOADER_FILE_ACCESS);
		}

		/* debug function */
		if(is_callable($debug)) {
			$this->debug = $debug;
		}

		$this->load_export();
		$this->load_table();
		$this->load_component();

		/* initialize module */
		$this->recover();

		/* call debug function for initialize */
		if($this->debug) {
			call_user_func($this->debug, $this, 0);
		}
	}

	/*
		load_export
	*/
	private function load_export() {
		$this->bin = new plato_class_bin ($this->file, $this->fp);

		/* service table */
		if(!$this->address_table = $this->bin->find_export(PLATO_EXPORT_TABLE)) {
			$this->exception (PLATO_EX_LOADER_EXPORT_TABLE);
		}

		/* main function */
		if(!$this->address_main = $this->bin->find_export(PLATO_EXPORT_MAIN)) {
			$this->exception (PLATO_EX_LOADER_EXPORT_MAIN);
		}
	}

	/*
		load_table
	*/
	private function load_table() {
		$version	= & $this->version;
		$address	= & $this->address_table;

		/* jump code (for vc debug built) */
		if(0xE9 == $this->bin->read_byte($address)) {
			$address = $address + $this->bin->read_dword($address + 1) + 5;
		}

		/* 00-03: int3 int3 nop nop */
		if((int) 0x9090CCCC != $this->bin->read_dword($address)) {
			$this->exception (PLATO_EX_LOADER_TABLE_FLAG);
		}
		/* 04-07: nop int3 nop int3 */
		if((int) 0xCC90CC90 != $this->bin->read_dword($address + 4)) {
			$this->exception (PLATO_EX_LOADER_TABLE_FLAG);
		}

		/* 08-0B: version */
		if(!$version = $this->bin->read_dword($address + 8)) {
			$this->exception (PLATO_EX_LOADER_TABLE_VERSION_INVALID);
		}
		if(!isset(self::$support[$version])) {
			$this->exception (
				PLATO_EX_LOADER_TABLE_VERSION_SUPPORT,
				array ('version%X' => $version)
			);
		}

		/* 0C-0F: reserved */
		/* 10-13: reserved */
		/* 14-18: table address */
		if(!$address = $this->bin->read_dword($address + 20)) {
			$this->exception (PLATO_EX_LOADER_TABLE_ADDRESS);
		}
	}

	/*
		load_service
	*/
	private function load_component() {
		/* version for path */
		$version		= sprintf('%X', $this->version);
		/* working directory */
		$root			= PLATO_ROOT.'v_'.$version.'/';

		$file_config	= $root.'inc.config.php';
		$file_opcode	= $root.'inc.opcode.php';

		$this->config	= & self::$support[$this->version];

		/* load class file */
		include_once	$root.'class.service.php';
		include_once	$root.'class.module.php';
		include_once	$root.'class.template.php';

		/* load config */
		if(empty($this->config)) {
			$config = & $this->config;

			@include $file_config;

			if(!isset($config) || empty($config) || !is_array($config)) {
				$this->exception (PLATO_EX_LOADER_COMPONENT_CONFIG);
			}
		}

		/* load compiler */
		$this->compiler	= plato_class_compiler::create ($this->version, $file_opcode);

		/* create virtual machine */
		$this->memory	= new plato_class_memory	($this);
		$this->cpu		= new plato_class_cpu		($this);

		/* create cpu */
		$this->cpu->create();

		/* base address */
		$this->address_base		= $this->memory->base();
		$this->address_table	= $this->address_table	- $this->address_base;
		$this->address_main		= $this->address_main	+ $this->address_base;

		/* load service */
		$class_service	= 'plato_version_'.$version.'_service';
		$this->service	= new $class_service ($this, $this->address_table);
	}

	/*
		recover
	*/
	public function recover() {
		$version	= sprintf('%X', $this->version);

		$module		= 'plato_version_'.$version.'_module';
		$method		= 'initialize';

		/* initialize module */
		if(class_exists($module)) {
			if(method_exists($module, $method)) {
				call_user_func($module.'::'.$method, $this);
			}
		}
	}

	/*
		call
	*/
	public function call($address, $args) {
		$this->cpu->ready();

		if($address == 0) {
			$address = $this->address_main;

			if($args) {
				$argument = $this->memory->store_array($args);

				$this->cpu->push($argument,		4);
				$this->cpu->push(count($args),	4);
			} else {
				$this->cpu->push(0, 4);
				$this->cpu->push(0, 4);
			}
		} else if($args) {
			$array = array_reverse($args);

			foreach($array as $data) {
				$data = $this->pack($data);

				$this->cpu->push($data, 4);
			}
		}

		$this->cpu->run($address);
	}

	/*
		destroy
	*/
	public function destroy($type) {
		$data = $this->cpu->register->eax;
		$data = $this->unpack($data, $type);

		$this->memory->release();

		return $data;
	}

	/*
		register
	*/
	public function register($function, $address, $type) {
		/* add into registered object table */
		if(!isset(self::$object[$this->hash])) {
			self::$object[$this->hash] = $this;
		}

		if(function_exists($function)) {
			$this->exception (PLATO_EX_LOADER_REGISTER_EXIST);
		}

		/* return type */
		switch($type) {
			case PLATO_TYPE_FLOAT:
				$type = 'PLATO_TYPE_FLOAT';			break;

			case PLATO_TYPE_STRING:
				$type = 'PLATO_TYPE_STRING';		break;
			case PLATO_TYPE_STRING_ANSI:
				$type = 'PLATO_TYPE_STRING_ANSI';	break;

			default:
				$type = 'PLATO_TYPE_INTEGER';		break;
		}

		$code =	"function {$function} () {".
				"	\$args		= func_get_args();".
				"	\$object	= plato_class_loader::create('{$this->file}');".
				"	try {".
				"		\$object->call({$address}, \$args);".
				"	} catch (Exception \$ex) {".
				"		if(\$ex instanceof plato_exception == false) {".
				"			\$ex = new plato_exception (\$ex);".
				"		}".
				"		\$ex->report();".
				"	}".
				"	return \$object->destroy({$type});".
				"}";

		if(eval($code) === false) {
			$this->exception (PLATO_EX_LOADER_REGISTER_FAILED);
		}
	}

	/*
		pack
	*/
	public function pack($data) {
		switch(gettype($data)) {
			case 'boolean':
				if($data == true) {
					return 1;
				} else {
					return 0;
				}

			case 'integer':
				return $data;

			case 'double':
				return (int) @pack('f', $data);

			case 'string':
				return $this->memory->store_string($data);

			case 'array':
				return $this->memory->store_array($data);
		}

		return 0;
	}

	/*
		unpack
	*/
	public function unpack($data, $type) {
		switch($type) {
			case PLATO_TYPE_FLOAT:
				if($data = @unpack('f', $data)) {
					return $data[0];
				} else {
					return 0.;
				}

			case PLATO_TYPE_STRING:
			case PLATO_TYPE_STRING_ANSI:
				/* is null ? */
				if($data) {
					return $this->memory->read_string($data);
				} else {
					return null;
				}
		}

		return $data;		
	}
}
