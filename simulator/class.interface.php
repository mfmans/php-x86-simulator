<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ class/interface   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

/*
	plato_class_loader								�ļ�������
*/
interface plato_interface_loader {
	/*
		+ create									����������ʵ��
													��Ҫʹ�� new �����, ��������ڴ��˷ѻ�����ͬ��

		@ str	$file

		# class	plato_class_loader
	*/
	static public function create($file);

	/*
		load										�����ļ�

		@ call	$debug	= null						���Ժ���
	*/
	public function load($debug = null);

	/*
		recover										ִ��Ƕ�׼����ļ�����ʱ�Ļָ�����
	*/
	public function recover();

	/*
		call										��ʼ���ô���

		@ int	$address
		@ array	$args								����
	*/
	public function call($address, $args);

	/*
		destroy										�����ڴ沢���ص��ú�������ֵ

		@ int	$type								����ֵ����

		# mixed
	*/
	public function destroy($type);

	/*
		register									ע��һ������

		@ int	$function							������
		@ int	$address							����ִ�����
		@ int	$type								��������ֵ����
	*/
	public function register($function, $address, $type);

	/*
		pack										�Բ�ͬ�������ݽ��з�װ

		@ mixed	$data

		# int										��װ���˫������
	*/
	public function pack($data);

	/*
		unpack										�Բ�ͬ���������ͽ��н��

		@ int	$data
		@ int	$type

		# mixed
	*/
	public function unpack($data, $type);
}

/*
	plato_class_bin									PE �ļ�������
*/
interface plato_interface_bin {
	/*
		@ str	$file
		@ file	$fp
	*/
	public function __construct($file, $fp);

	/*
		find_export									�ڵ������в��Һ���

		@ str	$function

		# int										����ҵ�, ���غ�����ڵ�ַ
		# bool										���δ�ҵ�, ���� false
	*/
	public function find_export($function);

	/*
		find_section								���� RVA ��������

		@ int	$rva
		@ array	$section	&

		# bool										�����Ƿ��ҵ�����
	*/
	public function find_section($rva, &$section);

	/*
		read_byte									��ȡһ���ֽ�
		read_dword									��ȡһ��˫��
		read_string									��ȡ�ַ���

		@ int	$rva

		# int										read_byte/read_dword
		# str										read_string
		# null										��ȡʧ�ܷ��� null
	*/
	public function read_byte	($rva);
	public function read_dword	($rva);
	public function read_string	($rva);

	/*
		read_array									��ȡ����

		@ int	$rva
		@ int	$count								����
		@ int	$size	= 4							ÿ���

		# array										��ȡʧ�ܷ��ؿ�����
	*/
	public function read_array($rva, $count, $size = 4);

	/*
		conv_rva_offset								�� RVA תΪ�ļ�ƫ����

		@ int	$rva

		# int
		# bool										��ַ��Ч���� false
	*/
	public function conv_rva_offset($rva);
}

/*
	plato_class_compiler							opcode ������
*/
interface plato_interface_compiler {
	/*
		+ create									����������ʵ��
													��Ҫʹ�� new �����, �����˷Ѵ������ڴ�

		@ int	$version
		@ str	$file								opcode �ļ�·��

		# class	plato_class_compiler	&
	*/
	static public function & create($version, $file);
}

/*
	plato_class_memory								�ڴ�ģ����
*/
interface plato_interface_memory {
	/*
		@ class	plato_class_loader	$ldr
	*/
	public function __construct($ldr);

	/*
		read_byte									��ָ����ַ����ȡһ���ֽ�
		read_word									��ָ����ַ����ȡһ����
		read_dword									��ָ����ַ����ȡһ��˫��
		read_string									��ָ����ַ����ȡһ���ַ���

		@ int	$address

		# int										�����ص����ݴ�����Ч�ĸ�λ, ��Ϊ 0
		# str
	*/
	public function read_byte	($address);
	public function read_word	($address);
	public function read_dword	($address);
	public function read_string	($address);

	/*
		write_byte									��ָ����ַд��һ���ֽ�
		write_word									��ָ����ַд��һ����
		write_dword									��ָ����ַд��һ��˫��
		write_string								��ָ����ַд��һ���ַ���

		@ int	$address
		@ mixed	$data								��Ч�ĸ�λ���ᱻ����
	*/
	public function write_byte		($address, $data);
	public function write_word		($address, $data);
	public function write_dword		($address, $data);
	public function write_string	($address, $data);

	/*
		store_array									��������

		@ array	$array

		# int										����������ʼ��ַ
	*/
	public function store_array($array);

	/*
		store_string								�����ַ���������

		@ str	$string

		# int
	*/
	public function store_string($string);

	/*
		execute										������˳������ȡģʽ, ����ʵ��Ӧ�ó���ִ�б���

		@ bool	$enable
	*/
	public function execute($enable);

	/*
		base										��ģ�����ַ

		# int
	*/
	public function base();

	/*
		conv_address_rva							���ڴ��ַת��Ϊ��ʵ�� RVA, ���ڵ�ַ�ض�λ

		@ int	$address

		# int
		# bool										����ַ������ģ����, ���� false
	*/
	public function conv_address_rva($address);

	/*
		page_allocate								�����ڴ�ҳ

		@ int	$size								�����С, ��С�ᱻ�Զ�����ҳ����

		# int
	*/
	public function page_allocate($size);

	/*
		page_free									�ͷ��ڴ�ҳ

		@ int	$address
	*/
	public function page_free($address);

	/*
		page_flag									�ڴ�ҳ������Ϣ

		@ int	$address
		@ int	$readable	= -1					�ɶ� (0=��ֹ, 1=����, -1=��ѯ)
		@ int	$writable	= -1					��д
		@ int	$executable	= -1					��ִ��

		@ array	(R, W, E)							�����޸�ǰ�ڴ�ҳ������
	*/
	public function page_flag($address, $readable = -1, $writable = -1, $executable = -1);

	/*
		heap_allocate								�ڶ��������ڴ�

		@ int	$size

		# int
	*/
	public function heap_allocate($size);

	/*
		heap_reallocate								�ڶ����������ڴ�

		@ int	$address							ԭ��ַ
		@ int	$size

		# int
	*/
	public function heap_reallocate($address, $size);

	/*
		heap_free									�ڶ����ͷ��ڴ�

		@ int	$address
	*/
	public function heap_free($address);

	/*
		heap_zero									�����ڶ��Ϸ�����ڴ�

		@ int	$address
	*/
	public function heap_zero($address);

	/*
		stack_allocate								����ջ

		# int
	*/
	public function stack_allocate();

	/*
		stack_full									����Ƿ�ջ��

		# int	$address							��ǰ��ջ����ַ

		@ bool
	*/
	public function stack_full($address);

	/*
		release										�ͷ������Ѿ�������ڴ�
	*/
	public function release();
}

/*
	plato_class_cpu									CPU ģ����
*/
interface plato_interface_cpu {
	/*
		@ class	plato_class_loader	$ldr
	*/
	public function __construct($ldr);

	/*
		create										��ʼ�� CPU ����
	*/
	public function create();
	/*
		ready										׼�����л���
	*/
	public function ready();

	/*
		run											��ʼִ��ָ����ַ�Ĵ���

		@ int	$address
	*/
	public function run($address);

	/*
		address										��ǰָ��Ѱַ�ĵ�ַ

		# int
		# null										�Ĵ���Ѱַ����Ѱַ���� null
	*/
	public function address();

	/*
		read_address								����Ѱַ��ַ��ȡ����

		@ int	$order		= 0						��ȡ�Ķ��� (0=����Ѱַ��ַ, 1=��һ������, 2=�ڶ�������)
		@ int	$opsize		= 0

		# int
	*/
	public function read_address($order = 0, $opsize = 0);

	/*
		read_memory									��ȡ�ڴ�����

		@ int	$address
		@ int	$opsize		= 0
	*/
	public function read_memory($address, $opsize = 0);

	/*
		write_address								����Ѱַ��ַд������

		@ int	$data
		@ int	$order		= 0
		@ int	$opsize		= 0
	*/
	public function write_address($data, $order = 0, $opsize = 0);

	/*
		write_memory								д���ڴ�����

		@ int	$address
		@ int	$data
		@ int	$opsize		= 0
	*/
	public function write_memory($address, $data, $opsize = 0);

	/*
		jump										��ת��ָ����ַ

		@ int	$address
		@ int	$absolute	= true					���Ե�ַ
		@ int	$opsize		= 0						��ַ����
	*/
	public function jump($address, $absolute = true, $opsize = 0);

	/*
		push										����ѹջ

		@ int	$data
		@ int	$opsize		= 0
	*/
	public function push($data, $opsize = 0);

	/*
		pop											���ݳ�ջ

		@ int	$opsize		= 0

		# int										��Ч��λ��Ϊ 0
	*/
	public function pop($opsize = 0);

	/*
		exception_push								������������ѹ��һ����������ַ

		@ int	$address
	*/
	public function exception_push($address);

	/*
		exception_pop								�Ӵ���������е������Ĵ�������ַ

		# int
	*/
	public function exception_pop();

	/*
		breakpoint_add								��Ӷϵ�

		@ int	$address
	*/
	public function breakpoint_add($address);

	/*
		breakpoint_delete							ɾ���ϵ�

		@ int	$address
	*/
	public function breakpoint_delete($address);
}

/*
	plato_class_register							CPU �Ĵ�����ģ����
*/
interface plato_interface_register {
	/*
		@ class	plato_class_loader	$ldr
	*/
	public function __construct($ldr);

	/*
		@ str	$name
	*/
	public function __get($name);

	/*
		@ str	$name
		@ int	$value
	*/
	public function __set($name, $value);

	/*
		reset										�������мĴ���
	*/
	public function reset();

	/*
		read										���ݼĴ��� ID ��ȡֵ

		@ int	$id
		@ int	$opsize	= 0

		# int										��Ч�ĸ�λ��Ϊ 0
	*/
	public function read($id, $opsize = 0);

	/*
		read_address								���ݼĴ��� ID ��ֵ��Ϊ�ڴ��ַ��ȡ����

		@ int	$id
		@ int	$opsize	= 0

		# int
	*/
	public function read_address($id, $opsize = 0);

	/*
		write										���ݼĴ��� ID д��ֵ

		@ int	$id
		@ int	$data								��Ч�ĸ�λ�ᱻ����
		@ int	$opsize	= 0
	*/
	public function write($id, $data, $opsize = 0);

	/*
		write_address								���ݼĴ��� ID ��ֵ��Ϊ�ڴ��ַд������

		@ int	$id
		@ int	$data
		@ int	$opsize	= 0
	*/
	public function write_address($id, $data, $opsize = 0);

	/*
		esi_read									�� ESI ��ֵ��Ϊ�ڴ��ַ��ȡ����

		@ int	$opsize	= 0

		# int
	*/
	public function esi_read($opsize = 0);

	/*
		esi_next									ESI ��ֵ���� DF ��־λ����

		@ int	$opsize	= 0							����
	*/
	public function esi_next($opsize = 0);

	/*
		edi_read									�� EDI ��ֵ��Ϊ�ڴ��ַ��ȡ����

		@ int	$opsize	= 0

		# int
	*/
	public function edi_read($opsize = 0);

	/*
		edi_write									�� EDI ��ֵ��Ϊ�ڴ��ַд������

		@ int	$data
		@ int	$opsize	= 0
	*/
	public function edi_write($data, $opsize = 0);

	/*
		edi_next									EDI ��ֵ���� DF ��־λ����

		@ int	$opsize = 0
	*/
	public function edi_next($opsize = 0);
}

/*
	plato_class_alu									ALU ģ����
*/
interface plato_interface_alu {
	/*
		@ class	plato_class_loader	$ldr
	*/
	public function __construct($ldr);

	/*
		flag										���ݽ������ ZF/SF/PF ��־λ

		@ int	$number
		@ int	$opsize		= 0
	*/
	public function flag($number, $opsize = 0);

	/*
		extend										������չ

		@ int	$number
		@ int	$opsize		= 0

		# int
	*/
	public function extend($number, $opsize = 0);

	/*
		arith_add									�����ӷ�

		@ int	$number1
		@ int	$number2
		@ bool	$carry		= false					���� CF ��λ��־
		@ int	$opsize		= 0

		# int
	*/
	public function arith_add($number1, $number2, $carry = false, $opsize = 0);

	/*
		arith_sub									��������

		@ int	$minuend							������
		@ int	$subtrahend							����
		@ bool	$carry		= false
		@ int	$opsize		= 0

		# int
	*/
	public function arith_sub($minuend, $subtrahend, $carry = false, $opsize = 0);

	/*
		arith_mul									�����޷��ų˷�
		arith_imul									���������ų˷�

		@ int	$number1
		@ int	$number2
		@ int	$opsize		= 0

		# array	(L, H)
	*/
	public function arith_mul	($number1, $number2, $opsize = 0);
	public function arith_imul	($number1, $number2, $opsize = 0);

	/*
		arith_div									�����޷��ų���
		arith_idiv									���������ų���

		@ int	$dividend_l							��������λ
		@ int	$dividend_h							��������λ
		@ int	$divisor							����
		@ int	$opsize		= 0

		# array	(Q, R)								Q: ��, R: ����
	*/
	public function arith_div	($dividend_l, $dividend_h, $divisor, $opsize = 0);
	public function arith_idiv	($dividend_l, $dividend_h, $divisor, $opsize = 0);

	/*
		bit_get										��ȡλ

		@ int	$number
		@ int	$position
		@ int	$opsize		= 0

		# int										���� 0 �� 1
	*/
	public function bit_get($number, $position, $opsize = 0);

	/*
		bit_set										����λ

		@ int	$number
		@ int	$position
		@ int	$set
		@ int	$opsize		= 0

		# int
	*/
	public function bit_set($number, $position, $set, $opsize = 0);

	/*
		logic_and									�߼�������
		logic_or									�߼�������
		logic_xor									�߼��������

		@ int	$number1
		@ int	$number2

		# int
	*/
	public function logic_and	($number1, $number2);
	public function logic_or	($number1, $number2);
	public function logic_xor	($number1, $number2);

	/*
		logic_not									�߼�������
		logic_neg									�߼�������

		@ int	$number

		# int
	*/
	public function logic_not	($number);
	public function logic_neg	($number);

	/*
		shift_left									����
		shift_right									����

		@ int	$number
		@ int	$position							�ƶ�λ��
		@ bool	$logic		= false					�߼�λ��
		@ bool	$circle		= false					ѭ��λ��
		@ int	$opsize		= 0

		# array	(R, L, H)							L: ����Ƴ�һλ (CF), H: ��������Чλ
	*/
	public function shift_left	($number, $position, $logic = false, $circle = false, $opsize = 0);
	public function shift_right	($number, $position, $logic = false, $circle = false, $opsize = 0);
}

/*
	plato_class_decoder								opcode ������
*/
interface plato_interface_decoder {
	/*
		@ class	plato_class_loader	$ldr
	*/
	public function __construct($ldr);

	/*
		parse										���� EIP ��ַ������

		# call										����ɹ�, ����ִ�е�Ԫ��ģ�鷽����
		# bool										����ʧ��, ���� false, ʹ���ظ�ǰ׺ִ����Ϻ󷵻� true
	*/
	public function parse();

	/*
		next_byte									��ȡ��һ���ֽ�
		next_word									��ȡ��һ����
		next_dword									��ȡ��һ��˫��

		@ int	$protect	= true					��ȡ�������ܵ�ִ�б���

		# int
	*/
	public function next_byte	($protect = true);
	public function next_word	($protect = true);
	public function next_dword	($protect = true);

	/*
		offset										�޸�Ѱַָ����ڴ��ַ
													����ǼĴ���Ѱַ, �������κβ���

		@ int	$number								ƫ����
	*/
	public function offset($number);
}

/*
	plato_version_*_service							�������
*/
interface plato_interface_service {
	/*
		@ class	plato_class_loader	$ldr
		@ int	$table
	*/
	public function __construct($ldr, $table);

	/*
		reset										�����������л���
	*/
	public function reset();
}

/*
	plato_version_*_module							ִ�е�Ԫ����ģ��
*/
interface plato_interface_module {
	/*
		initialize									��ʼ��ģ��

		@ class	plato_class_loader	$ldr
	*/
	static public function initialize($ldr);
}
