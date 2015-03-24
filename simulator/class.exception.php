<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ class/exception   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_exception extends Exception {
	public	$system;

	public	$id;
	public	$message;

	/* comment */
	public	$comment;

	/* calling stack */
	public	$stack;
	/* calling argument */
	public	$argument;

	/* runtime environment */
	public	$eip;
	public	$rva;
	public	$offset;
	public	$opcode;

	/*
		__construct
	*/
	public function __construct($id, $comment = array(), $ldr = null) {
		if(is_object($id)) {
			if($id instanceof Exception) {
				return $this->create_with_exception($id);
			}

			$id = PLATO_EX_UNDEFINED;
		}

		/* system exception */
		$this->system	= false;
		/* base information */
		$this->id		= $id;
		$this->message	= $this->create_message();
		/* comment */
		$this->comment	= $this->create_comment((array) $comment);

		/* stack */
		$this->create_stack();

		/* runtime */
		$this->create_runtime($ldr);
	}

	/*
		create_with_exception
	*/
	private function create_with_exception($ex) {
		$code		= $ex->getCode();
		$message	= $ex->getMessage();
		$this->system	= true;

		$this->id		= 'SYSTEM_EXCEPTION';
		$this->message	= "(#$code) $message";

		return true;
	}

	/*
		create_message
	*/
	private function create_message() {
		$constant = get_defined_constants(true);

		foreach($constant['user'] as $key => $value) {
			if($value == $this->id) {
				if(substr($key, 0, 9) == 'PLATO_EX_') {
					return substr($key, 9);
				}
			}
		}

		return 'Unknown';
	}

	/*
		create_comment
	*/
	private function create_comment($comments) {
		$return = array();

		foreach($comments as $key => $value) {
			if(empty($key)) {
				continue;
			}

			if(($offset = strpos($key, '%')) !== false) {
				$format	= substr($key, $offset);
				$key	= substr($key, 0, $offset);

				if(is_array($value)) {
					$argument = array_merge (
						array ($format),
						$value
					);
				} else {
					$argument = array ($format, $value);
				}

				$value = call_user_func_array('sprintf', $argument);
			}

			$return[$key] = $this->output($value);
		}

		return $return;
	}

	/*
		create_stack
	*/
	private function create_stack() {
		$this->stack	= array();
		$this->argument	= array();

		$stack	= debug_backtrace();
		$inside	= false;

		/* plato_exception::create_stack */
		unset($stack[0]);

		foreach($stack as $value) {
			if(isset($value['class'])) {
				if($value['type'] == '->') {
					$function = '('.$value['class'].')->'.$value['function'];
				} else {
					$function = $value['class'].$value['type'].$value['function'];
				}

				if($value['class'] == 'plato_class_loader') {
					$inside = true;
				}
			} else {
				$function = $value['function'];

				if($inside == true) {
					$inside = null;
				}
			}

			if(!isset($value['file'])) {
				$value['file'] = '?';
			}
			if(!isset($value['line'])) {
				$value['line'] = '?';
			}

			$this->stack[] = array (
				'function'	=> $function,
				'file'		=> $value['file'],
				'line'		=> $value['line']
			);

			if($inside === null) {
				/* argument */
				if(isset($value['args']) && !empty($value['args'])) {
					foreach($value['args'] as $argument) {
						$this->argument[] = $this->output($argument);
					}
				}

				break;
			}
		}
	}

	/*
		create_runtime
	*/
	private function create_runtime($ldr) {
		$this->eip		= null;
		$this->rva		= null;
		$this->offset	= null;

		if($ldr) {
			try {
				if(is_object($ldr->cpu)) {
					require_once PLATO_ROOT.'class.disassemble.php';

					$this->eip		= $ldr->cpu->register->eip;
					$this->rva		= $ldr->memory->conv_address_rva($this->eip);
					$this->offset	= $ldr->bin->conv_rva_offset($this->rva);

					/* 反汇编 */
					$this->opcode	= plato_class_disassemble::parse($ldr);
				}
			} catch (Exception $ex) {
				// do nothing
			}
		}
	}

	/*
		report										输出异常报告

		@ bool	$mode	= true						根据 PHP 运行模式进行个性化输出
	*/
	public function report($mode = true) {
		if($mode) {
			if(php_sapi_name() == 'cli') {
				$this->report_cli();

				return;
			}
		}

		$this->report_web();
	}

	/*
		report_web
	*/
	private function report_web() {
		if($this->system) {
			$this->message	= $this->newline($this->message, false);
		} else {
			$this->id		= sprintf('%08X', $this->id);
			$this->message	= $this->newline($this->message, true);

			foreach($this->comment as $key => $value) {
				$this->comment[$key] = $this->newline($value, true);
			}
			foreach($this->argument as $key => $value) {
				$this->argument[$key] = $this->newline($value, true);
			}
		}

		@include PLATO_ROOT.'inc.exception.php';
	}

	/*
		report_cli
	*/
	private function report_cli() {
		echo "Plato x86 Simulator Exception Reporter\n\n";

		if($this->system) {
			echo "ID : {$this->id}\n";
			echo "MSG: ".$this->newline($this->message, false)."\n";

			return;
		}

		echo "ID : ".sprintf('%08X', $this->id)."\n";
		echo "MSG: {$this->message}\n";

		if($this->eip !== null) {
			printf("EIP: %08X\n",	$this->eip);
		}
		if($this->rva !== null) {
			printf("RVA: %08X\n",	$this->rva);
		}
		if($this->offset !== null) {
			printf("OFS: %08X\n",	$this->offset);
		}
		if(!empty($this->opcode)) {
			echo 'CODE: '.$this->opcode."\n";
		}

		echo "\n";

		if($this->comment) {
			echo "COMMENT:\n";

			foreach($this->comment as $key => $value) {
				echo "\t[{$key}]\t".$this->newline($value, false)."\n";
			}

			echo "\n";
		}

		if($this->stack) {
			echo "STACK:\n";

			foreach($this->stack as $data) {
				echo "\t{$data['function']} @ Line {$data['line']}, {$data['file']}\n";
			}

			echo "\n";
		}

		if($this->argument) {
			echo "ARGUMENT:\n";

			foreach($this->argument as $key => $value) {
				echo "\t[#{$key}]\t".$this->newline($value, false)."\n";
			}

			echo "\n";
		}
	}

	/*
		newline
	*/
	private function newline($string, $br) {
		if($br) {
			$replace = '<br />';
		} else {
			$replace = ' ';
		}

		return str_replace(array ("\r", "\n"), array ('', $replace), $string);
	}

	/*
		output
	*/
	private function output($data) {
		switch(gettype($data)) {
			case 'boolean':
				if($data == true) {
					return 'TRUE';
				} else {
					return 'FALSE';
				}
			case 'object':
				return '{OBJECT}';

			case 'resource':
				return '{RESOURCE}';

			case 'NULL':
				return 'NULL';

			case 'array':
				$string = array();

				foreach($data as $key => $value) {
					$string[] = $key.': '.$this->output($value);
				}

				if($string) {
					return 'ARRAY ['.implode(', ', $string).']';
				} else {
					return 'ARRAY []';
				}

			default:
				return (string) $data;
		}
	}
}
