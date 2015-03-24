<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ class/cpu   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_class_cpu implements plato_interface_cpu {
	private	$ldr;
	private	$memory;

	/* debug function */
	private	$debug;
	/* track mode */
	private	$track;

	/* CPU component */
	public	$register;
	public	$alu;
	public	$decoder;

	/* address map */
	public	$address	= array();

	/* exception process list */
	private	$exception	= array();

	/*
		__construct
	*/
	public function __construct($ldr) {
		$this->ldr		= $ldr;
		$this->memory	= $ldr->memory;

		$this->register	= null;
	}

	/*
		__exception
	*/
	private function __exception($id, $comment = array()) {
		throw new plato_exception ($id, $comment, $this->ldr);
	}

	/*
		create
	*/
	public function create() {
		if($this->register == null) {
			$this->register	= new plato_class_register	($this->ldr);
			$this->alu		= new plato_class_alu		($this->ldr);
			$this->decoder	= new plato_class_decoder	($this->ldr);
		}

		$this->debug = $this->ldr->debug;
		$this->track = 0;

		if(defined('PLATO_TRACK_STATUS')) {
			switch(PLATO_TRACK_STATUS) {
				/* 关闭 */
				case 0:
					break;

				/* 总是启用 */
				case 1:
					$this->track = 1;
					break;

				/* 被动启用 */
				case 2:
					if(defined('PLATO_TRACK_VARIABLE')) {
						if(isset($_GET[PLATO_TRACK_VARIABLE]) || isset($_POST[PLATO_TRACK_VARIABLE])) {
							$this->track = 1;
						}
					}
					break;
			}
		}

		if($this->track) {
			require_once PLATO_ROOT.'class.disassemble.php';
		}
	}

	/*
		ready
	*/
	public function ready() {
		$stack = $this->memory->stack_allocate();

		$this->register->reset();
		$this->register->ebp = $stack;
		$this->register->esp = $stack;

		/* 向栈底压入一个数据 */
		$this->push((int) 0xCCCCCCCC, 4);

		$this->exception = array();
	}

	/*
		run
	*/
	public function run($address) {
		/* address for return */
		$this->push(0, 4);
		/* eip */
		$this->register->eip = $address;

		/* 启动调度器 */
		$this->dispatch();
	}

	/*
		address
	*/
	public function address() {
		$mod = & $this->decoder->mod;

		switch($mod['method']) {
			/* register */
			case 0:
				return null;

			/* directly address */
			case 1:
				return $mod['address'];

			/* index + scale */
			case 2:
				$index	= $this->register->read($mod['index'], 4);
				$scale	= $mod['scale'];
				$base	= 0;
				break;

			/* index + scale + base */
			case 3:
				$index	= $this->register->read($mod['index'], 4);
				$scale	= $mod['scale'];
				$base	= $this->register->read($mod['base'], 4);
				break;
		}

		$address = $index * $scale + $base;

		if($offset = $mod['offset']) {
			if($mod['byte']) {
				$address += $this->alu->extend($offset, 1);
			} else {
				$address += (int) $offset;
			}
		}

		return $address;
	}

	/*
		read_address
	*/
	public function read_address($order = 0, $opsize = 0) {
		$register = false;

		if($opsize == 0) {
			$opsize = $this->decoder->opsize;
		}

		switch($order) {
			/* r, r/m */
			case 1:
				if($this->decoder->direction == 1) {
					$register = true;
				}
				break;

			/* r/m, r */
			case 2:
				if($this->decoder->direction == 0) {
					$register = true;
				}
				break;
		}

		if($register) {
			$register = $this->decoder->mod['code'];
		} else {
			if(($address = $this->address()) === null) {
				$register = $this->decoder->mod['register'];
			} else {
				return $this->read_memory($address, $opsize);
			}
		}

		return $this->register->read($register, $opsize);
	}

	/*
		read_memory
	*/
	public function read_memory($address, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->decoder->opsize;
		}

		switch($opsize) {
			case 1: return $this->memory->read_byte		($address); break;
			case 2: return $this->memory->read_word		($address); break;
			case 4: return $this->memory->read_dword	($address); break;
		}

		$this->__exception (
			PLATO_EX_CPU_SIZE_INVALID,
			array (
				'address%08X'	=> $address,
				'opsize'		=> $opsize
			)
		);
	}

	/*
		write_address
	*/
	public function write_address($data, $order = 0, $opsize = 0) {
		$register = false;

		if($opsize == 0) {
			$opsize = $this->decoder->opsize;
		}

		switch($order) {
			/* r, r/m */
			case 1:
				if($this->decoder->direction == 1) {
					$register = true;
				}
				break;

			/* r/m, r */
			case 2:
				if($this->decoder->direction == 0) {
					$register = true;
				}
				break;
		}

		if($register) {
			$register = $this->decoder->mod['code'];
		} else {
			if(($address = $this->address()) === null) {
				$register = $this->decoder->mod['register'];
			} else {
				$this->write_memory($address, $data, $opsize);

				return;
			}
		}

		$this->register->write($register, $data, $opsize);
	}

	/*
		write_memory
	*/
	public function write_memory($address, $data, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->decoder->opsize;
		}

		switch($opsize) {
			case 1: return $this->memory->write_byte	($address, $data); break;
			case 2: return $this->memory->write_word	($address, $data); break;
			case 4: return $this->memory->write_dword	($address, $data); break;
		}

		$this->__exception (
			PLATO_EX_CPU_SIZE_INVALID,
			array (
				'address%08X'	=> $address,
				'opsize'		=> $opsize
			)
		);
	}

	/*
		jump
	*/
	public function jump($address, $absolute = true, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->decoder->opsize;
		}

		if($absolute) {
			if($opsize != 4) {
				$this->__exception (PLATO_EX_CPU_JUMP_SIZE);
			}
		} else {
			$address = $this->alu->extend($address, $opsize);
			$address = $address + $this->register->eip;
		}

		$this->register->eip = $address;
	}

	/*
		push
	*/
	public function push($data, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->decoder->opsize;
		}

		$esp		= $this->register->esp;
		$address	= $esp - $opsize;

		/* overflow */
		if($this->memory->stack_full($esp)) {
			$this->__exception (PLATO_EX_CPU_STACK_FULL);
		}

		/* write to memory */
		$this->write_memory($address, $data, $opsize);

		$this->register->esp = $address;
	}

	/*
		pop
	*/
	public function pop($opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->decoder->opsize;
		}

		$esp	= $this->register->esp;
		$data	= $this->read_memory($esp, $opsize);

		$this->register->esp = $esp + $opsize;

		return $data;
	}

	/*
		exception_push
	*/
	public function exception_push($address) {
		$this->exception[] = $address;
	}

	/*
		exception_pop
	*/
	public function exception_pop() {
		if(empty($this->exception)) {
			return 0;
		}

		return array_pop($this->exception);
	}

	/*
		breakpoint_add
	*/
	public function breakpoint_add($address) {
		$this->address[$address] = array ($this, 'invoke_breakpoint');
	}

	/*
		breakpoint_delete
	*/
	public function breakpoint_delete($address) {
		if(isset($this->address[$address])) {
			unset($this->address[$address]);
		}
	}

	/*
		dispatch
	*/
	private function dispatch() {
		$eip		= & $this->register->eip;
		$map		= & $this->address;
		$decoder	= & $this->decoder;

		while($eip) {
			if(isset($map[$eip])) {
				if($this->invoke($map[$eip], true)) {
					continue;
				}
			}

			if(!$object = $decoder->parse($eip)) {
				$this->__exception (PLATO_EX_CPU_CODE_UNDEFINED);
			}

			if($object !== true) {
				try {
					$offset = $this->invoke($object, false);

					if(is_int($offset)) {
						$eip += $offset;
					}
				} catch (plato_exception $ex) {
					/* 没有错误处理器 */
					if(empty($this->exception)) {
						throw $ex;
					}

					/* 错误 ID 保存到 EAX 中 */
					$this->register->eax = $ex->id;

					/* 进入处理程序 */
					$eip = $this->exception_pop();
				}
			}
		}
	}

	/*
		invoke
	*/
	private function invoke($object, $map) {
		if($map) {
			if($object[0] == $this) {
				/* breakpoint */
				if($this->debug) {
					call_user_func($this->debug, $this->ldr, $this->register->eip);
				}

				return false;
			} else {
				if($this->track) {
					echo '[SERVICE] __plato_'.substr($object[1], 7).'<br />';
				}

				/* return address */
				$this->register->eip = $this->pop(4);

				/* call service program */
				call_user_func($object);

				return true;
			}
		}

		if($this->track) {
			printf('[C: 0x%08X] ', $this->decoder->address);

			echo plato_class_disassemble::parse($this->ldr);
			echo '<br />';
		}

		return call_user_func($object);
	}
}
