<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ class/register   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_class_register implements plato_interface_register {
	private	$ldr;
	private	$cpu;

	/* general register */
	public	$eax, $ecx, $edx, $ebx;
	public	$esp, $ebp, $esi, $edi;

	/* instruction register */
	public	$eip;

	/* segment register */
	public	$cs, $ds, $ss;
	public	$es, $fs, $gs;

	/* eflags */
	public	$CF, $ZF, $SF, $DF, $OF, $PF, $AF;

	/*
		__construct
	*/
	public function __construct($ldr) {
		$this->ldr = $ldr;
		$this->cpu = $ldr->cpu;
	}

	/*
		__get
	*/
	public function __get($name) {
		switch($name) {
			/* 16 bit register */
			case 'ax':
			case 'cx':
			case 'dx':
			case 'bx':
			case 'sp':
			case 'bp':
			case 'si':
			case 'di':
			case 'ip':
				$name	= 'e'.$name;
				$value	= $this->$name;

				return $value & 0xFFFF;

			/* local register */
			case 'al': case 'ah':
			case 'cl': case 'ch':
			case 'dl': case 'dh':
			case 'bl': case 'bh':
				$name	= 'e'.$name[0].'x';
				$value	= $this->$name;

				/* high byte */
				if($name[1] == 'h') {
					$value = $value >> 8;
				}

				return $value & 0xFF;

			/* flags / eflags */
			case 'flags':
			case 'eflags':
				return $this->eflags_get();
		}

		$this->exception (
			PLATO_EX_REGISTER_UNKNOWN,
			array ('name' => $name)
		);
	}

	/*
		__set
	*/
	public function __set($name, $value) {
		switch($name) {
			/* 16 bit register */
			case 'ax':
			case 'cx':
			case 'dx':
			case 'bx':
			case 'sp':
			case 'bp':
			case 'si':
			case 'di':
				$name	= 'e'.$name;
				$value	= $this->$name & 0xFFFF0000 & ($value & 0xFFFF);

				$this->$name = $value;

				return;

			/* local register */
			case 'al': $id = 0; break;
			case 'cl': $id = 1; break;
			case 'dl': $id = 2; break;
			case 'bl': $id = 3; break;
			case 'ah': $id = 4; break;
			case 'ch': $id = 5; break;
			case 'dh': $id = 6; break;
			case 'bh': $id = 7; break;

			/* flags / eflags */
			case 'flags':
			case 'eflags':
				return $this->eflags_set($value);

			default:
				$this->exception (
					PLATO_EX_REGISTER_UNKNOWN,
					array ('name' => $name)
				);
		}

		/* for local register */
		$this->write($id, $data, 1);
	}

	/*
		exception
	*/
	private function exception($id, $comment = array()) {
		throw new plato_exception ($id, $comment, $this->ldr);
	}

	/*
		reset
	*/
	public function reset() {
		$this->eax	= 0;
		$this->ecx	= 0;
		$this->edx	= 0;
		$this->ebx	= 0;
		$this->esp	= 0;
		$this->ebp	= 0;
		$this->esi	= 0;
		$this->edi	= 0;

		$this->eip	= 0;

		$this->cs	= 0;
		$this->ds	= 0;
		$this->ss	= 0;
		$this->es	= 0;
		$this->fs	= 0;
		$this->gs	= 0;

		$this->CF	= 0;
		$this->ZF	= 0;
		$this->SF	= 0;
		$this->DF	= 0;
		$this->OF	= 0;
		$this->PF	= 0;
		$this->AF	= 0;
	}

	/*
		read
	*/
	public function read($id, $opsize = 0) {
		$id = $id & 0x07;

		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		switch($opsize) {
			case 1:
				switch($id) {
					case 0: return $this->eax & 0xFF;
					case 1: return $this->ecx & 0xFF;
					case 2: return $this->edx & 0xFF;
					case 3: return $this->ebx & 0xFF;
					case 4: return ($this->eax >> 8) & 0xFF;
					case 5: return ($this->ecx >> 8) & 0xFF;
					case 6: return ($this->edx >> 8) & 0xFF;
					case 7: return ($this->ebx >> 8) & 0xFF;
				}
				break;

			case 2:
				switch($id) {
					case 0: return $this->eax & 0xFFFF;
					case 1: return $this->ecx & 0xFFFF;
					case 2: return $this->edx & 0xFFFF;
					case 3: return $this->ebx & 0xFFFF;
					case 4: return $this->esp & 0xFFFF;
					case 5: return $this->ebp & 0xFFFF;
					case 6: return $this->esi & 0xFFFF;
					case 7: return $this->edi & 0xFFFF;
				}
				break;

			case 4:
				switch($id) {
					case 0: return $this->eax;
					case 1: return $this->ecx;
					case 2: return $this->edx;
					case 3: return $this->ebx;
					case 4: return $this->esp;
					case 5: return $this->ebp;
					case 6: return $this->esi;
					case 7: return $this->edi;
				}
				break;
		}

		$this->exception (
			PLATO_EX_REGISTER_ID_INVALID,
			array (
				'id'		=> $id,
				'opsize'	=> $opsize
			)
		);
	}

	/*
		read_address
	*/
	public function read_address($id, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		$address = $this->read($id, 4);

		return $this->cpu->read_memory($address, $opsize);
	}

	/*
		write
	*/
	public function write($id, $data, $opsize = 0) {
		$data = (int) $data;

		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		switch($opsize) {
			case 1:
				switch($id & 0x03) {
					case 0: $reg = & $this->eax; break;
					case 1: $reg = & $this->ecx; break;
					case 2: $reg = & $this->edx; break;
					case 3: $reg = & $this->ebx; break;

					default:
						$this->exception (
							PLATO_EX_REGISTER_ID_INVALID,
							array ('id' => $id)
						);
				}

				if($id & 0x04) {
					$reg = ($reg & (int) 0xFFFF00FF) | (($data << 8) & 0xFF00);
				} else {
					$reg = ($reg & (int) 0xFFFFFF00) | ($data & 0xFF);
				}

				return;

			case 2:
			case 4:
				switch($id & 0x07) {
					case 0: $reg = & $this->eax; break;
					case 1: $reg = & $this->ecx; break;
					case 2: $reg = & $this->edx; break;
					case 3: $reg = & $this->ebx; break;
					case 4: $reg = & $this->esp; break;
					case 5: $reg = & $this->ebp; break;
					case 6: $reg = & $this->esi; break;
					case 7: $reg = & $this->edi; break;

					default:
						$this->exception (
							PLATO_EX_REGISTER_ID_INVALID,
							array ('id' => $id)
						);
				}

				if($opsize == 2) {
					$reg = ($reg & (int) 0xFFFF0000) | ($data & 0xFFFF);
				} else {
					$reg = $data;
				}

				return;
		}

		$this->exception (
			PLATO_EX_REGISTER_ID_INVALID,
			array (
				'id'		=> $id,
				'opsize'	=> $opsize
			)
		);
	}

	/*
		write_address
	*/
	public function write_address($id, $data, $opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		$address = $this->read($id, 4);

		return $this->cpu->write_memory($address, $data, $opsize);
	}

	/*
		esi_read
	*/
	public function esi_read($opsize = 0) {
		return $this->read_address(6, $opsize);
	}

	/*
		esi_next
	*/
	public function esi_next($opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		$esi = $this->esi;

		if($this->DF == 0) {
			$esi = (int) ($esi + $opsize);
		} else {
			$esi = (int) ($esi - $opsize);
		}

		$this->esi = $esi;
	}

	/*
		edi_read
	*/
	public function edi_read($opsize = 0) {
		return $this->read_address(7, $opsize);
	}

	/*
		edi_write
	*/
	public function edi_write($data, $opsize = 0) {
		$this->write_address(7, $data, $opsize);
	}

	/*
		edi_next
	*/
	public function edi_next($opsize = 0) {
		if($opsize == 0) {
			$opsize = $this->cpu->decoder->opsize;
		}

		$edi = $this->edi;

		if($this->DF == 0) {
			$edi = (int) ($edi + $opsize);
		} else {
			$edi = (int) ($edi - $opsize);
		}

		$this->edi = $edi;
	}

	/*
		eflags_get
	*/
	private function eflags_get() {
		$flag = 0;

		$flag |= ($this->CF ? 1 : 0) <<  0;
		$flag |= ($this->PF ? 1 : 0) <<  2;
		$flag |= ($this->AF ? 1 : 0) <<  4;
		$flag |= ($this->ZF ? 1 : 0) <<  6;
		$flag |= ($this->SF ? 1 : 0) <<  7;
		$flag |= ($this->DF ? 1 : 0) << 10;
		$flag |= ($this->OF ? 1 : 0) << 11;

		return $flag;
	}

	/*
		eflags_set
	*/
	private function eflags_set($flag) {
		$this->CF = ($flag >>  0) & 0x01;
		$this->PF = ($flag >>  2) & 0x01;
		$this->AF = ($flag >>  4) & 0x01;
		$this->ZF = ($flag >>  6) & 0x01;
		$this->SF = ($flag >>  7) & 0x01;
		$this->DF = ($flag >> 10) & 0x01;
		$this->OF = ($flag >> 11) & 0x01;
	}
}
