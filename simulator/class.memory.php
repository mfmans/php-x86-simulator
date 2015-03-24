<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ class/memory   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_class_memory implements plato_interface_memory {
	private	$ldr;
	private	$bin;
	private	$cfg;

	/* 执行模式 */
	private	$mode			= false;

	/* 内存数据 (align by 32bit) */
	private $data			= array();

	/* 已申请页 */
	private	$page			= array();
	/* 已在堆中申请的空间 */
	private $heap			= array();

	/* 主模块 */
	private	$module_start	= 0;
	private	$module_end		= 0;

	/* 栈 */
	private	$stack_start	= 0;
	private	$stack_end		= 0;

	/*
		__construct
	*/
	public function __construct($ldr) {
		$this->ldr	= $ldr;
		$this->bin	= $ldr->bin;
		$this->cfg	= & $ldr->config;

		/* module base */
		$base = $this->bin->image_base;

		if(($base <= 0) || ($base <= $this->cfg['ADDRESS_NULL']) || ($base > $this->cfg['ADDRESS_HIGH'])) {
			$base = $this->cfg['ADDRESS_MODULE'];
		}

		$this->module_start	= $base;
		$this->module_end	= $base + $this->bin->image_size;
	}

	/*
		exception
	*/
	private function exception($id, $comment = array()) {
		throw new plato_exception ($id, $comment, $this->ldr);
	}

	/*
		check
	*/
	private function check($address, $write) {
		$error = 0;

		if($address <= 0) {
			$error = PLATO_EX_MEMORY_ADDRESS_INVALID;
		} else if($address <= $this->cfg['ADDRESS_NULL']) {
			$error = PLATO_EX_MEMORY_ADDRESS_NULL;
		} else if($address >= $this->cfg['ADDRESS_HIGH']) {
			$error = PLATO_EX_MEMORY_ADDRESS_HIGH;
		}

		if($error) {
			$this->exception ($error, array ('address%08X' => $address));
		}

		if(($rva = $this->conv_address_rva($address)) === false) {
			$page = false;

			/* 遍历已分配内存表 */
			foreach($this->page as $start => $data) {
				if(($address >= $start) && ($address < $data['end'])) {
					$page = & $data;

					break;
				}
			}

			if(!$page) {
				$this->exception (
					PLATO_EX_MEMORY_ADDRESS_RESERVE,
					array ('address%08X' => $address)
				);
			}
		} else {
			$page = false;

			if($this->bin->find_section($rva, $page) == false) {
				$this->exception (
					PLATO_EX_MEMORY_ADDRESS_SECTION,
					array (
						'address%08X'	=> $address,
						'rva%08X'		=> $rva
					)
				);
			}
		}

		if($write) {
			if(!$page['writable']) {
				$error = PLATO_EX_MEMORY_VIOLENT_WRITE;
			}
		} else {
			if($this->mode) {
				if($this->cfg['EXECUTE_PROTECT'] && !$page['executable']) {
					$error = PLATO_EX_MEMORY_VIOLENT_EXECUTE;
				}
			} else {
				if(!$page['readable']) {
					$error = PLATO_EX_MEMORY_VIOLENT_READ;
				}
			}
		}

		if($error) {
			$this->exception ($error, array ('address%08X' => $address));
		}
	}

	/*
		read
	*/
	private function read($address, &$index, $uninitialized = false) {
		$index = $address & 0xFFFFFFFC;

		if(isset($this->data[$index])) {
			return $this->data[$index];
		}

		if(($rva = $this->conv_address_rva($address)) === false) {
			if($uninitialized) {
				return 0;
			}

			$this->exception (
				PLATO_EX_MEMORY_READ_UNINITIALIZED,
				array (
					'address%08X'	=> $address,
					'index%08X'		=> $index
				)
			);
		}

		if(($data = $this->bin->read_dword($rva)) === null) {
			if($uninitialized) {
				return 0;
			}

			$this->exception (
				PLATO_EX_MEMORY_READ_BIN,
				array (
					'address%08X'	=> $address,
					'rva%08X'		=> $rva
				)
			);
		}

		/* 读取对齐的数据 */
		if($offset = $address - $index) {
			if(($prev = $this->bin->read_dword($rva - $offset)) === null) {
				$data = $data << ($offset << 3);
			} else {
				$data = $prev;
			}
		}

		$this->data[$index] = $data;

		return $data;
	}

	/*
		write
	*/
	private function write($index, $data) {
		$this->data[$index] = (int) $data;
	}

	/*
		read_byte
	*/
	public function read_byte($address) {
		$this->check($address, false);

		$index	= 0;
		$data	= $this->read($address, $index);

		if($offset = $address - $index) {
			return ($data >> ($offset << 3)) & 0xFF;
		} else {
			return $data & 0xFF;
		}
	}

	/*
		read_word
	*/
	public function read_word($address) {
		$this->check($address, false);

		$index	= 0;
		$data	= $this->read($address, $index);
		$offset	= $address - $index;

		if($offset == 3) {
			$this->check($index + 4, false);

			$next = $this->read($index + 4, $index, true);

			return (($data >> 24) & 0xFF) | ($next & 0xFF);
		} else {
			if($offset) {
				return ($data >> ($offset << 3)) & 0xFF;
			} else {
				return $data & 0xFFFF;
			}
		}
	}

	/*
		read_dword
	*/
	public function read_dword($address) {
		$this->check($address, false);

		$index	= 0;
		$data	= $this->read($address, $index);

		if($offset = $address - $index) {
			$this->check($index + 4, false);

			$next = $this->read($index + 4, $index, true);

			$offset	= $offset << 3;
			$ratio	= 32 - $offset;

			return ($data >> $offset) & ~(0xFFFFFFFF << $ratio) | ($next << $ratio);
		} else {
			return $data;
		}
	}

	/*
		read_string
	*/
	public function read_string($address) {
		$this->check($address, false);

		$index	= 0;
		$data	= $this->read($address, $index, true);

		if($offset = $address - $index) {
			$data = $data >> ($offset << 3);
		}

		$done	= false;
		$string	= '';

		while($done == false) {
			for(; $offset < 4; $offset++) {
				/* null-character found */
				if(($char = $data & 0xFF) == 0) {
					$done = true;

					break;
				}

				$string	.= chr($char);

				$data = $data >> 8;
			}

			if($done == false) {
				$this->check($index + 4, false);

				$data	= $this->read($index + 4, $index, true);
				$offset	= 0;
			}
		}

		return $string;
	}

	/*
		write_byte
	*/
	public function write_byte($address, $data) {
		$this->check($address, true);

		$index	= 0;
		$dword	= $this->read($address, $index, true);
		$offset	= ($address - $index) << 3;

		$dword	= ($dword & ~(0xFF << $offset)) | (($data & 0xFF) << $offset);

		$this->write($index, $dword);
	}

	/*
		write_word
	*/
	public function write_word($address, $data) {
		$this->check($address, true);

		$index	= 0;
		$dword	= $this->read($address, $index, true);
		$offset	= ($address - $index) << 3;

		switch($offset) {
			case 0:
				$dword = ($dword & 0xFFFF0000) | ($data & 0xFFFF);
				break;

			case 8:
			case 16:
				$dword = ($dword & ~(0xFFFF << $offset)) | (($data & 0xFFFF) << $offset);
				break;

			default:
				$this->check($index + 4, true);

				/* first dword */
				$this->write($index, ($dword & 0xFFFFFF) | (($data & 0xFF) << 24));

				/* second dword */
				$dword	= $this->read($index + 4, $index, true);
				$dword	= ($dword & 0xFFFFFF00) | (($data >> 8) & 0xFF);
		}

		$this->write($index, $dword);
	}

	/*
		write_dword
	*/
	public function write_dword($address, $data) {
		$this->check($address, true);

		$index	= 0;
		$dword	= $this->read($address, $index, true);
		$offset	= ($address - $index) * 8;

		if($offset == 0) {
			$this->write($index, $data);

			return;
		}

		$this->check($index + 4, true);

		/* first dword */
		$this->write($index, ($dword & ~((~0) << $offset)) | ($data << $offset));

		/* second dword */
		$dword = $this->read($index + 4, $index, true);

		$this->write($index, ($dword & ((~0) << $offset)) | ($data >> (32 - $offset)));
	}

	/*
		write_string
	*/
	public function write_string($address, $data) {
		$length = strlen($data);

		for($i = 0; $i < $length; $i++) {
			$this->write_byte($address++, ord($data[$i]));
		}

		/* null-character */
		$this->write_byte($address, 0);
	}

	/*
		store_array
	*/
	public function store_array($array) {
		$size		= count($array) * 4;
		$address	= $this->heap_allocate($size);

		$current	= $address;

		foreach($array as $value) {
			$data = $this->ldr->pack($value);

			$this->write_dword($current, $data);

			$current = $current + 4;
		}

		return $address;
	}

	/*
		store_string
	*/
	public function store_string($string) {
		$size		= strlen($string) + 1;
		$address	= $this->heap_allocate($size);

		$this->write_string($address, $string);

		return $address;
	}

	/*
		base
	*/
	public function base() {
		return $this->module_start;
	}

	/*
		conv_address_rva
	*/
	public function conv_address_rva($address) {
		if(($address < $this->module_start) || ($address >= $this->module_end)) {
			return false;
		}

		return $address - $this->module_start;
	}

	/*
		execute
	*/
	public function execute($enable) {
		$this->mode = (bool) $enable;
	}

	/*
		page_allocate
	*/
	public function page_allocate($size) {
		$page		= $this->cfg['PAGE'];

		/* 默认起始地址 */
		$address	= $this->cfg['ALLOCATE'];

		/* 所有已分配地址 */
		$reserved	= array_keys($this->page);
		$reserved[]	= $this->module_start;

		/* 对所有已分配地址进行升序排序 */
		sort($reserved, SORT_NUMERIC);

		/* 页对齐 */
		if($size % $page) {
			$size = ((int) ($size / $page) + 1) * $page;
		} else {
			$size = ((int) ($size / $page)) * $page;
		}

		foreach($reserved as $start) {
			if(($address + $size) < $start) {
				break;
			} else {
				if($start == $this->module_start) {
					$end = $this->module_end;
				} else {
					$end = $this->page[$start]['end'];
				}

				$address = (int) ($end + $page);
			}
		}

		if(($address <= 0) || ($address >= $this->cfg['ADDRESS_HIGH'])) {
			$this->exception (
				PLATO_EX_MEMORY_PAGE_FULL,
				array ('size' => $size)
			);
		}

		$this->page[$address] = array (
			'end'			=> $address + $size,
			'readable'		=> true,
			'writable'		=> true,
			'executable'	=> false
		);

		return $address;
	}

	/*
		page_free
	*/
	public function page_free($address) {
		if(!isset($this->page[$address])) {
			$this->exception (
				PLATO_EX_MEMORY_PAGE_FREE,
				array ('address%08X' => $address)
			);
		}

		$start	= $address;
		$end	= $this->page[$address]['end'];

		unset($this->page[$address]);

		foreach($this->data as $address => $value) {
			if(($address >= $start) && ($address < $end)) {
				unset($this->data[$address]);
			}
		}
	}

	/*
		page_flag
	*/
	public function page_flag($address, $readable = -1, $writable = -1, $executable = -1) {
		if(!isset($this->page[$address])) {
			$this->exception (
				PLATO_EX_MEMORY_PAGE_FLAG,
				array ('address%08X' => $address)
			);
		}

		$page = & $this->page[$address];

		/* current flag */
		$flag = array (
			$page['readable'],
			$page['writable'],
			$page['executable']
		);

		if($readable >= 0) {
			$page['readable']	= (bool) $readable;
		}
		if($writable >= 0) {
			$page['writable']	= (bool) $writable;
		}
		if($executable >= 0) {
			$page['executable']	= (bool) $executable;
		}

		return $flag;
	}

	/*
		heap_allocate
	*/
	public function heap_allocate($size) {
		$piece = $this->cfg['HEAP_ALLOCATE'];

		if($size < $piece) {
			$size = $piece;
		} else {
			if($size % $piece) {
				$size = ((int) ($size / $piece) + 1) * $piece;
			} else {
				$size = (int) $size;
			}
		}

		foreach($this->heap as $address => $heap) {
			$id = $address;

			if($heap['free'] < $size) {
				continue;
			}

			foreach($heap['list'] as $start => $end) {
				if(($address + $size) >= $start) {
					$address = $end;
				} else {
					$this->heap[$id]['free'] -= $size;
					$this->heap[$id]['list'][$address] = $address + $size;

					return $address;
				}
			}
		}

		/* size of new heap */
		$page_size = $this->cfg['PAGE'];
		$heap_size = $this->cfg['HEAP_SIZE'];

		if($heap_size <= $size) {
			$heap_size = ((int) ($size / $page_size) + 1) * $page_size;
		}

		$start	= $this->page_allocate($heap_size);
		$end	= $start + $heap_size;

		$this->heap[$start] = array (
			'end'	=> $end,
			'size'	=> $heap_size,
			'free'	=> $heap_size - $size,
			'list'	=> array (
				$start => $start + $size
			)
		);

		return $start;
	}

	/*
		heap_reallocate
	*/
	public function heap_reallocate($address, $size) {
		$id		= false;
		$start	= -1;
		$end	= -1;

		foreach($this->heap as $start => $heap) {
			if(($address >= $start) && ($address < $heap['end'])) {
				if(isset($heap['list'][$address])) {
					/* page */
					$id		= $start;
					/* list */
					$start	= $address;
					$end	= $heap['list'][$address];
				}

				break;
			}
		}

		if($id === false) {
			$this->exception (
				PLATO_EX_MEMORY_HEAP_REALLOCATE,
				array (
					'address%08X'	=> $address,
					'size'			=> $size
				)
			);
		}

		if(($end - $start) >= $size) {
			return $address;
		}

		/* new address */
		$new	= $this->heap_allocate($size);
		$offset	= $new - $start;

		/* map */
		foreach($this->data as $address => $key) {
			if(($address >= $start) && ($address < $end)) {
				$this->data[$address + $offset] = $key;

				unset($this->data[$address]);
			}
		}

		$this->heap[$id]['free'] += $end - $start;

		unset($this->heap[$id]['list'][$address]);

		/* free */
		if($this->heap[$id]['free'] == $this->heap[$id]['size']) {
			unset($this->heap[$id]);
			unset($this->page[$id]);
		}

		return $new;
	}

	/*
		heap_free
	*/
	public function heap_free($address) {
		$id = false;

		foreach($this->heap as $start => $heap) {
			if(($address >= $start) && ($address < $heap['end'])) {
				$id = $start;

				break;
			}
		}

		if($id !== false) {
			if(isset($heap['list'][$address])) {
				$start	= $address;
				$end	= $heap['list'][$address];

				/* remove data */
				foreach($this->data as $address => $data) {
					if(($address >= $start) && ($address < $end)) {
						unset($this->data[$address]);
					}
				}

				$this->heap[$id]['free'] += $end - $start;

				unset($this->heap[$id]['list'][$address]);

				/* free */
				if($this->heap[$id]['free'] == $this->heap[$id]['size']) {
					unset($this->heap[$id]);
					unset($this->page[$id]);
				}

				return true;
			}
		}

		$this->exception (
			PLATO_EX_MEMORY_HEAP_FREE,
			array ('address%08X' => $address)
		);
	}

	/*
		heap_zero
	*/
	public function heap_zero($address) {
		$end = false;

		foreach($this->heap as $start => $heap) {
			if(($address >= $start) && ($address < $heap['end'])) {
				if(isset($heap['list'][$address])) {
					$end = $heap['list'][$address];
				}

				break;
			}
		}

		if($end === false) {
			$this->exception (
				PLATO_EX_MEMORY_HEAP_ZERO,
				array ('address%08X' => $address)
			);
		}

		for($i = $address; $i < $end; $i += 4) {
			$this->write_dword($i, 0);
		}
	}

	/*
		stack_allocate
	*/
	public function stack_allocate() {
		$size		= $this->cfg['STACK'];
		$address	= $this->page_allocate($size);

		$this->stack_start	= $address;
		$this->stack_end	= $address + $size;

		return $this->stack_end;
	}

	/*
		stack_full
	*/
	public function stack_full($address) {
		if($address <= $this->stack_start) {
			return true;
		}

		return false;
	}

	/*
		release
	*/
	public function release() {
		$this->data			= array();
		$this->page			= array();
		$this->heap			= array();

		$this->stack_start	= 0;
		$this->stack_end	= 0;
	}
}
