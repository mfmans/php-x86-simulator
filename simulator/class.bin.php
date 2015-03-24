<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ class/bin   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_class_bin implements plato_interface_bin {
	/* @ winnt.h */
	static private $const = array (
		/* for IMAGE_DOS_HEADER.e_magic */
		'IMAGE_DOS_SIGNATURE'				=> 0x5A4D,

		/* for IMAGE_NT_HEADERS.Signature */
		'IMAGE_NT_SIGNATURE'				=> 0x4550,

		/* for IMAGE_FILE_HEADER.Machine */
		'IMAGE_FILE_MACHINE_I386'			=> 0x014C,
		'IMAGE_FILE_MACHINE_IA64'			=> 0x0200,
		'IMAGE_FILE_MACHINE_AMD64'			=> 0x8664,

		/* for IMAGE_FILE_HEADER.Characteristics */
		'IMAGE_FILE_EXECUTABLE_IMAGE'		=> 0x0002,

		/* for IMAGE_OPTIONAL_HEADER32.Magic */
		'IMAGE_NT_OPTIONAL_HDR32_MAGIC'		=> 0x010B,
		'IMAGE_NT_OPTIONAL_HDR64_MAGIC'		=> 0x020B,

		/* for IMAGE_OPTIONAL_HEADER32.DataDirectory */
		'IMAGE_NUMBEROF_DIRECTORY_ENTRIES'	=> 16,
		'IMAGE_DIRECTORY_ENTRY_EXPORT'		=> 0,

		/* for IMAGE_SECTION_HEADER.Characteristics */
		'IMAGE_SCN_MEM_EXECUTE'				=> 0x20000000,
		'IMAGE_SCN_MEM_READ'				=> 0x40000000,
		'IMAGE_SCN_MEM_WRITE'				=> 0x80000000,
	);

	/* @ winnt.h */
	static private $struct = array (
		'IMAGE_DOS_HEADER'			=> 've_magic/ve_cblp/ve_cp/ve_crlc/ve_cparhdr/ve_minalloc/ve_maxalloc/ve_ss/ve_sp/ve_csum/ve_ip/ve_cs/ve_lfarlc/ve_ovno/v4e_res/ve_oemid/ve_oeminfo/v10e_res2/Ve_lfanew',
		'IMAGE_NT_HEADERS'			=> 'VSignature',
		'IMAGE_FILE_HEADER'			=> 'vMachine/vNumberOfSections/VTimeDateStamp/VPointerToSymbolTable/VNumberOfSymbols/vSizeOfOptionalHeader/vCharacteristics',
		'IMAGE_OPTIONAL_HEADER32'	=> 'vMagic/CMajorLinkerVersion/CMinorLinkerVersion/VSizeOfCode/VSizeOfInitializedData/VSizeOfUninitializedData/VAddressOfEntryPoint/VBaseOfCode/VBaseOfData/VImageBase/VSectionAlignment/VFileAlignment/vMajorOperatingSystemVersion/vMinorOperatingSystemVersion/vMajorImageVersion/vMinorImageVersion/vMajorSubsystemVersion/vMinorSubsystemVersion/VWin32VersionValue/VSizeOfImage/VSizeOfHeaders/VCheckSum/vSubsystem/vDllCharacteristics/VSizeOfStackReserve/VSizeOfStackCommit/VSizeOfHeapReserve/VSizeOfHeapCommit/VLoaderFlags/VNumberOfRvaAndSizes',
		'IMAGE_DATA_DIRECTORY'		=> 'VVirtualAddress/VSize',
		'IMAGE_EXPORT_DIRECTORY'	=> 'VCharacteristics/VTimeDateStamp/vMajorVersion/vMinorVersion/VName/VBase/VNumberOfFunctions/VNumberOfNames/VAddressOfFunctions/VAddressOfNames/VAddressOfNameOrdinals',
		'IMAGE_SECTION_HEADER'		=> 'C8Name/VMisc/VVirtualAddress/VSizeOfRawData/VPointerToRawData/VPointerToRelocations/VPointerToLinenumbers/vNumberOfRelocations/vNumberOfLinenumbers/VCharacteristics',
	);

	/* sizeof */
	static private $sizeof = array (
		'IMAGE_DOS_HEADER'			=> 0x40,
		'IMAGE_NT_HEADERS'			=> 0x04,
		'IMAGE_FILE_HEADER'			=> 0x14,
		'IMAGE_OPTIONAL_HEADER32'	=> 0x60,
		'IMAGE_DATA_DIRECTORY'		=> 0x08,
		'IMAGE_EXPORT_DIRECTORY'	=> 0x28,
		'IMAGE_SECTION_HEADER'		=> 0x28,
	);


	/* file */
	private	$file;
	private	$fp;

	/* export table */
	private	$export;
	private	$export_address;
	private	$export_name;
	private $export_index;

	/* image base address and size */
	public	$image_base;
	public	$image_size;

	/* section table */
	public	$section;

	/*
		__construct
	*/
	public function __construct($file, $fp) {
		rewind($fp);

		$this->file	= $file;
		$this->fp	= $fp;

		/* parse file header */
		$this->load_dos_header();
		$this->load_nt_header();
		$this->load_image_file_header();
		$this->load_image_optional_header();

		/* analyze directory */
		$this->load_directory();

		/* analyze section table */
		$this->load_section();

		/* initialize export table */
		$this->export_address	= array();
		$this->export_name		= array();
		$this->export_index		= array();
	}

	/*
		exception
	*/
	private function exception($id) {
		throw new plato_exception (
			$id,
			array (
				'file'		=> $this->file,
				'offset'	=> (int) @ftell($this->fp)
			)
		);
	}

	/*
		extract
	*/
	private function extract($key) {
		$length = self::$sizeof[$key];
		$struct = self::$struct[$key];

		if(!$data = @fread($this->fp, $length)) {
			return false;
		}

		if(!$data = @unpack($struct, $data)) {
			return false;
		}

		return $data;
	}

	/*
		seek
	*/
	private function seek($rva) {
		if(($address = $this->conv_rva_offset($rva)) !== false) {
			if(@fseek($this->fp, $address, SEEK_SET) == 0) {
				return true;
			}
		}

		return false;
	}

	/*
		load_dos_header								IMAGE_DOS_HEADER
	*/
	private function load_dos_header() {
		if(!$header = $this->extract('IMAGE_DOS_HEADER')) {
			$this->exception (PLATO_EX_BIN_DOS_NOT_FOUND);
		}

		/* IMAGE_DOS_HEADER.e_magic */
		if($header['e_magic'] != self::$const['IMAGE_DOS_SIGNATURE']) {
			$this->exception (PLATO_EX_BIN_DOS_MAGIC);
		}

		/* IMAGE_DOS_HEADER.e_lfanew */
		if($header['e_lfanew'] < self::$sizeof['IMAGE_DOS_HEADER']) {
			$this->exception (PLATO_EX_BIN_DOS_POINTER);
		}

		@fseek($this->fp, $header['e_lfanew'], SEEK_SET);
	}

	/*
		load_nt_header								IMAGE_NT_HEADERS
	*/
	private function load_nt_header() {
		if(!$header = $this->extract('IMAGE_NT_HEADERS')) {
			$this->exception (PLATO_EX_BIN_NT_NOT_FOUND);
		}

		/* IMAGE_NT_HEADERS.Signature */
		if($header['Signature'] != self::$const['IMAGE_NT_SIGNATURE']) {
			$this->exception (PLATO_EX_BIN_NT_SIGNATURE);
		}
	}

	/*
		load_image_file_header						IMAGE_FILE_HEADER
	*/
	private function load_image_file_header() {
		if(!$header = $this->extract('IMAGE_FILE_HEADER')) {
			$this->exception (PLATO_EX_BIN_FILE_NOT_FOUND);
		}

		/* IMAGE_FILE_HEADER.Machine */
		switch($header['Machine']) {
			case self::$const['IMAGE_FILE_MACHINE_I386']:
				break;

			case self::$const['IMAGE_FILE_MACHINE_IA64']:
			case self::$const['IMAGE_FILE_MACHINE_AMD64']:
				$this->exception (PLATO_EX_BIN_FILE_X64);

			default:
				$this->exception (PLATO_EX_BIN_FILE_MACHINE);
		}

		/* IMAGE_FILE_HEADER.NumberOfSections */
		if($header['NumberOfSections'] == 0) {
			$this->exception (PLATO_EX_BIN_FILE_SECTION);
		}

		/* IMAGE_FILE_HEADER.Characteristics */
		if(($header['Characteristics'] & self::$const['IMAGE_FILE_EXECUTABLE_IMAGE']) == 0) {
			$this->exception (PLATO_EX_BIN_FILE_EXECUTABLE);
		}

		/* init section table */
		$this->section = array_fill(0, $header['NumberOfSections'], false);
	}

	/*
		load_image_optional_header					IMAGE_OPTIONAL_HEADER32
	*/
	private function load_image_optional_header() {
		if(!$header = $this->extract('IMAGE_OPTIONAL_HEADER32')) {
			$this->exception (PLATO_EX_BIN_OPTIONAL_NOT_FOUND);
		}

		/* IMAGE_OPTIONAL_HEADER32.Magic */
		switch($header['Magic']) {
			case self::$const['IMAGE_NT_OPTIONAL_HDR32_MAGIC']:
				break;

			case self::$const['IMAGE_NT_OPTIONAL_HDR64_MAGIC']:
				$this->exception (PLATO_EX_BIN_OPTIONAL_X64);

			default:
				$this->exception (PLATO_EX_BIN_OPTIONAL_MAGIC);
		}

		/* IMAGE_OPTIONAL_HEADER32.SizeOfImage */
		if(!$header['SizeOfImage']) {
			$this->exception (PLATO_EX_BIN_OPTIONAL_IMAGE_SIZE);
		}

		$this->image_base = $header['ImageBase'];
		$this->image_size = $header['SizeOfImage'];
	}

	/*
		load_directory								IMAGE_DATA_DIRECTORY
	*/
	private function load_directory() {
		$count		= self::$const['IMAGE_NUMBEROF_DIRECTORY_ENTRIES'];
		$directory	= array();

		for($i = 0; $i < $count; $i++) {
			if(!$data = $this->extract('IMAGE_DATA_DIRECTORY')) {
				$this->exception (PLATO_EX_BIN_DIRECTOR_BROKEN);
			}

			$directory[] = $data['VirtualAddress'];
		}

		$this->export = $directory[self::$const['IMAGE_DIRECTORY_ENTRY_EXPORT']];

		if(!$this->export) {
			$this->exception (PLATO_EX_BIN_DIRECTORY_SECTION);
		}
	}

	/*
		load_section								IMAGE_SECTION_HEADER
	*/
	private function load_section() {
		foreach($this->section as $key => $dummy) {
			if(!$section = $this->extract('IMAGE_SECTION_HEADER')) {
				$this->exception (PLATO_EX_BIN_SECTION_BROKEN);
			}

			$this->section[$key] = array (
				'size'			=> $section['Misc'],							/* Misc.VirtualSize */
				'rva_start'		=> $section['VirtualAddress'],
				'rva_end'		=> $section['VirtualAddress'] + $section['Misc'],
				'va_start'		=> $section['VirtualAddress'] + $this->image_base,
				'va_end'		=> $section['VirtualAddress'] + $section['Misc'] + $this->image_base,
				'offset_start'	=> $section['PointerToRawData'],
				'offset_end'	=> $section['PointerToRawData'] + $section['SizeOfRawData'],
				'offset_size'	=> $section['SizeOfRawData'],
				'ratio'			=> $section['VirtualAddress'] - $section['PointerToRawData'],
				'executable'	=> (bool) ($section['Characteristics'] & self::$const['IMAGE_SCN_MEM_EXECUTE']),
				'readable'		=> (bool) ($section['Characteristics'] & self::$const['IMAGE_SCN_MEM_READ']),
				'writable'		=> (bool) ($section['Characteristics'] & self::$const['IMAGE_SCN_MEM_WRITE']),
			);
		}
	}

	/*
		find_export
	*/
	public function find_export($function) {
		if(empty($this->export_address)) {
			if(!$this->seek($this->export)) {
				return false;
			}

			if(!$table = $this->extract('IMAGE_EXPORT_DIRECTORY')) {
				$this->exception (PLATO_EX_BIN_EXPORT_NOT_FOUND);
			}

			$this->export_address	= $this->read_array($table['AddressOfFunctions'],		$table['NumberOfFunctions'],	4);
			$this->export_name		= $this->read_array($table['AddressOfNames'],			$table['NumberOfNames'],		4);
			$this->export_index		= $this->read_array($table['AddressOfNameOrdinals'],	$table['NumberOfNames'],		2);
		}

		if(empty($this->export_address) || empty($this->export_name) || empty($this->export_index)) {
			return false;
		}

		foreach($this->export_index as $key => $id) {
			if($this->read_string($this->export_name[$key]) == $function) {
				return $this->export_address[$id];
			}
		}

		return false;
	}

	/*
		find_section
	*/
	public function find_section($rva, &$section) {
		foreach($this->section as $sec) {
			if(($sec['rva_start'] <= $rva) && ($sec['rva_end'] >= $rva)) {
				$section = $sec;

				return true;
			}
		}

		return false;
	}

	/*
		read_byte
	*/
	public function read_byte($rva) {
		if($this->seek($rva)) {
			if($data = @fread($this->fp, 1)) {
				return ord($data);
			}
		}

		return null;
	}

	/*
		read_dword
	*/
	public function read_dword($rva) {
		if($this->seek($rva)) {
			if($data = @fread($this->fp, 4)) {
				if($data = @unpack('V', $data)) {
					return $data[1];
				}
			}
		}

		return null;
	}

	/*
		read_string
	*/
	public function read_string($rva) {
		$result = '';

		if($this->seek($rva) == false) {
			return null;
		}

		while(!feof($this->fp)) {
			if(($data = @fread($this->fp, 128)) === '') {
				break;
			}

			$code	= 0;
			$ascii	= @unpack('C*', $data);

			/* find null character */
			foreach($ascii as $len => $code) {
				if($code == 0) {
					$result .= substr($data, 0, $len - 1); break;
				}
			}

			if($code == 0) {
				break;
			}
		}

		return $result;
	}

	/*
		read_array
	*/
	public function read_array($rva, $count, $size = 4) {
		if($this->seek($rva)) {
			switch($size) {
				case 1: $format = 'C'; break;
				case 2: $format = 'v'; break;
				case 4: $format = 'V'; break;

				default:
					return array();
			}

			if($data = @fread($this->fp, $count * $size)) {
				if($data = @unpack($format.$count, $data)) {
					return array_values($data);
				}
			}
		}

		return array();
	}

	/*
		conv_rva_offset
	*/
	public function conv_rva_offset($rva) {
		$section = false;

		if($this->find_section($rva, $section) == false) {
			return false;
		}

		return $rva - $section['ratio'];
	}
}
