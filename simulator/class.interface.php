<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ class/interface   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

/*
	plato_class_loader								文件加载器
*/
interface plato_interface_loader {
	/*
		+ create									创建加载器实例
													不要使用 new 运算符, 以免造成内存浪费或函数不同步

		@ str	$file

		# class	plato_class_loader
	*/
	static public function create($file);

	/*
		load										载入文件

		@ call	$debug	= null						调试函数
	*/
	public function load($debug = null);

	/*
		recover										执行嵌套加载文件返回时的恢复工作
	*/
	public function recover();

	/*
		call										开始调用代码

		@ int	$address
		@ array	$args								参数
	*/
	public function call($address, $args);

	/*
		destroy										回收内存并返回调用函数返回值

		@ int	$type								返回值类型

		# mixed
	*/
	public function destroy($type);

	/*
		register									注册一个函数

		@ int	$function							函数名
		@ int	$address							函数执行入口
		@ int	$type								函数返回值类型
	*/
	public function register($function, $address, $type);

	/*
		pack										对不同类型数据进行封装

		@ mixed	$data

		# int										封装后的双字数据
	*/
	public function pack($data);

	/*
		unpack										对不同的数据类型进行解封

		@ int	$data
		@ int	$type

		# mixed
	*/
	public function unpack($data, $type);
}

/*
	plato_class_bin									PE 文件处理器
*/
interface plato_interface_bin {
	/*
		@ str	$file
		@ file	$fp
	*/
	public function __construct($file, $fp);

	/*
		find_export									在导出表中查找函数

		@ str	$function

		# int										如果找到, 返回函数入口地址
		# bool										如果未找到, 返回 false
	*/
	public function find_export($function);

	/*
		find_section								根据 RVA 查找区段

		@ int	$rva
		@ array	$section	&

		# bool										返回是否找到区段
	*/
	public function find_section($rva, &$section);

	/*
		read_byte									读取一个字节
		read_dword									读取一个双字
		read_string									读取字符串

		@ int	$rva

		# int										read_byte/read_dword
		# str										read_string
		# null										读取失败返回 null
	*/
	public function read_byte	($rva);
	public function read_dword	($rva);
	public function read_string	($rva);

	/*
		read_array									读取数组

		@ int	$rva
		@ int	$count								项数
		@ int	$size	= 4							每项长度

		# array										读取失败返回空数组
	*/
	public function read_array($rva, $count, $size = 4);

	/*
		conv_rva_offset								将 RVA 转为文件偏移量

		@ int	$rva

		# int
		# bool										地址无效返回 false
	*/
	public function conv_rva_offset($rva);
}

/*
	plato_class_compiler							opcode 编译器
*/
interface plato_interface_compiler {
	/*
		+ create									创建编译器实例
													不要使用 new 运算符, 以免浪费大量的内存

		@ int	$version
		@ str	$file								opcode 文件路径

		# class	plato_class_compiler	&
	*/
	static public function & create($version, $file);
}

/*
	plato_class_memory								内存模拟器
*/
interface plato_interface_memory {
	/*
		@ class	plato_class_loader	$ldr
	*/
	public function __construct($ldr);

	/*
		read_byte									从指定地址处读取一个字节
		read_word									从指定地址处读取一个字
		read_dword									从指定地址处读取一个双字
		read_string									从指定地址处读取一个字符串

		@ int	$address

		# int										若返回的数据存在无效的高位, 总为 0
		# str
	*/
	public function read_byte	($address);
	public function read_word	($address);
	public function read_dword	($address);
	public function read_string	($address);

	/*
		write_byte									向指定地址写入一个字节
		write_word									向指定地址写入一个字
		write_dword									向指定地址写入一个双字
		write_string								向指定地址写入一个字符串

		@ int	$address
		@ mixed	$data								无效的高位将会被忽略
	*/
	public function write_byte		($address, $data);
	public function write_word		($address, $data);
	public function write_dword		($address, $data);
	public function write_string	($address, $data);

	/*
		store_array									储存数组

		@ array	$array

		# int										返回数组起始地址
	*/
	public function store_array($array);

	/*
		store_string								储存字符串到堆上

		@ str	$string

		# int
	*/
	public function store_string($string);

	/*
		execute										进入或退出代码读取模式, 用于实现应用程序执行保护

		@ bool	$enable
	*/
	public function execute($enable);

	/*
		base										主模块基地址

		# int
	*/
	public function base();

	/*
		conv_address_rva							将内存地址转换为真实的 RVA, 用于地址重定位

		@ int	$address

		# int
		# bool										若地址不在主模块中, 返回 false
	*/
	public function conv_address_rva($address);

	/*
		page_allocate								申请内存页

		@ int	$size								申请大小, 大小会被自动进行页对齐

		# int
	*/
	public function page_allocate($size);

	/*
		page_free									释放内存页

		@ int	$address
	*/
	public function page_free($address);

	/*
		page_flag									内存页属性信息

		@ int	$address
		@ int	$readable	= -1					可读 (0=禁止, 1=允许, -1=查询)
		@ int	$writable	= -1					可写
		@ int	$executable	= -1					可执行

		@ array	(R, W, E)							返回修改前内存页的属性
	*/
	public function page_flag($address, $readable = -1, $writable = -1, $executable = -1);

	/*
		heap_allocate								在堆上申请内存

		@ int	$size

		# int
	*/
	public function heap_allocate($size);

	/*
		heap_reallocate								在堆上重申请内存

		@ int	$address							原地址
		@ int	$size

		# int
	*/
	public function heap_reallocate($address, $size);

	/*
		heap_free									在堆上释放内存

		@ int	$address
	*/
	public function heap_free($address);

	/*
		heap_zero									清零在堆上分配的内存

		@ int	$address
	*/
	public function heap_zero($address);

	/*
		stack_allocate								创建栈

		# int
	*/
	public function stack_allocate();

	/*
		stack_full									检测是否栈满

		# int	$address							当前的栈顶地址

		@ bool
	*/
	public function stack_full($address);

	/*
		release										释放所有已经申请的内存
	*/
	public function release();
}

/*
	plato_class_cpu									CPU 模拟器
*/
interface plato_interface_cpu {
	/*
		@ class	plato_class_loader	$ldr
	*/
	public function __construct($ldr);

	/*
		create										初始化 CPU 部件
	*/
	public function create();
	/*
		ready										准备运行环境
	*/
	public function ready();

	/*
		run											开始执行指定地址的代码

		@ int	$address
	*/
	public function run($address);

	/*
		address										当前指令寻址的地址

		# int
		# null										寄存器寻址或无寻址返回 null
	*/
	public function address();

	/*
		read_address								根据寻址地址读取数据

		@ int	$order		= 0						读取的对象 (0=总是寻址地址, 1=第一操作数, 2=第二操作数)
		@ int	$opsize		= 0

		# int
	*/
	public function read_address($order = 0, $opsize = 0);

	/*
		read_memory									读取内存数据

		@ int	$address
		@ int	$opsize		= 0
	*/
	public function read_memory($address, $opsize = 0);

	/*
		write_address								根据寻址地址写入数据

		@ int	$data
		@ int	$order		= 0
		@ int	$opsize		= 0
	*/
	public function write_address($data, $order = 0, $opsize = 0);

	/*
		write_memory								写入内存数据

		@ int	$address
		@ int	$data
		@ int	$opsize		= 0
	*/
	public function write_memory($address, $data, $opsize = 0);

	/*
		jump										跳转到指定地址

		@ int	$address
		@ int	$absolute	= true					绝对地址
		@ int	$opsize		= 0						地址长度
	*/
	public function jump($address, $absolute = true, $opsize = 0);

	/*
		push										数据压栈

		@ int	$data
		@ int	$opsize		= 0
	*/
	public function push($data, $opsize = 0);

	/*
		pop											数据出栈

		@ int	$opsize		= 0

		# int										无效高位总为 0
	*/
	public function pop($opsize = 0);

	/*
		exception_push								向错误处理程序中压入一个处理程序地址

		@ int	$address
	*/
	public function exception_push($address);

	/*
		exception_pop								从错误处理程序中弹出最后的处理程序地址

		# int
	*/
	public function exception_pop();

	/*
		breakpoint_add								添加断点

		@ int	$address
	*/
	public function breakpoint_add($address);

	/*
		breakpoint_delete							删除断点

		@ int	$address
	*/
	public function breakpoint_delete($address);
}

/*
	plato_class_register							CPU 寄存器组模拟器
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
		reset										重置所有寄存器
	*/
	public function reset();

	/*
		read										根据寄存器 ID 读取值

		@ int	$id
		@ int	$opsize	= 0

		# int										无效的高位总为 0
	*/
	public function read($id, $opsize = 0);

	/*
		read_address								根据寄存器 ID 的值作为内存地址读取数据

		@ int	$id
		@ int	$opsize	= 0

		# int
	*/
	public function read_address($id, $opsize = 0);

	/*
		write										根据寄存器 ID 写入值

		@ int	$id
		@ int	$data								无效的高位会被忽略
		@ int	$opsize	= 0
	*/
	public function write($id, $data, $opsize = 0);

	/*
		write_address								根据寄存器 ID 的值作为内存地址写入数据

		@ int	$id
		@ int	$data
		@ int	$opsize	= 0
	*/
	public function write_address($id, $data, $opsize = 0);

	/*
		esi_read									将 ESI 的值作为内存地址读取数据

		@ int	$opsize	= 0

		# int
	*/
	public function esi_read($opsize = 0);

	/*
		esi_next									ESI 的值根据 DF 标志位增长

		@ int	$opsize	= 0							增量
	*/
	public function esi_next($opsize = 0);

	/*
		edi_read									将 EDI 的值作为内存地址读取数据

		@ int	$opsize	= 0

		# int
	*/
	public function edi_read($opsize = 0);

	/*
		edi_write									将 EDI 的值作为内存地址写入数据

		@ int	$data
		@ int	$opsize	= 0
	*/
	public function edi_write($data, $opsize = 0);

	/*
		edi_next									EDI 的值根据 DF 标志位增长

		@ int	$opsize = 0
	*/
	public function edi_next($opsize = 0);
}

/*
	plato_class_alu									ALU 模拟器
*/
interface plato_interface_alu {
	/*
		@ class	plato_class_loader	$ldr
	*/
	public function __construct($ldr);

	/*
		flag										根据结果设置 ZF/SF/PF 标志位

		@ int	$number
		@ int	$opsize		= 0
	*/
	public function flag($number, $opsize = 0);

	/*
		extend										符号扩展

		@ int	$number
		@ int	$opsize		= 0

		# int
	*/
	public function extend($number, $opsize = 0);

	/*
		arith_add									算术加法

		@ int	$number1
		@ int	$number2
		@ bool	$carry		= false					考虑 CF 进位标志
		@ int	$opsize		= 0

		# int
	*/
	public function arith_add($number1, $number2, $carry = false, $opsize = 0);

	/*
		arith_sub									算术减法

		@ int	$minuend							被减数
		@ int	$subtrahend							减数
		@ bool	$carry		= false
		@ int	$opsize		= 0

		# int
	*/
	public function arith_sub($minuend, $subtrahend, $carry = false, $opsize = 0);

	/*
		arith_mul									算术无符号乘法
		arith_imul									算术带符号乘法

		@ int	$number1
		@ int	$number2
		@ int	$opsize		= 0

		# array	(L, H)
	*/
	public function arith_mul	($number1, $number2, $opsize = 0);
	public function arith_imul	($number1, $number2, $opsize = 0);

	/*
		arith_div									算术无符号除法
		arith_idiv									算术带符号除法

		@ int	$dividend_l							被除数低位
		@ int	$dividend_h							被除数高位
		@ int	$divisor							除数
		@ int	$opsize		= 0

		# array	(Q, R)								Q: 商, R: 余数
	*/
	public function arith_div	($dividend_l, $dividend_h, $divisor, $opsize = 0);
	public function arith_idiv	($dividend_l, $dividend_h, $divisor, $opsize = 0);

	/*
		bit_get										提取位

		@ int	$number
		@ int	$position
		@ int	$opsize		= 0

		# int										返回 0 或 1
	*/
	public function bit_get($number, $position, $opsize = 0);

	/*
		bit_set										设置位

		@ int	$number
		@ int	$position
		@ int	$set
		@ int	$opsize		= 0

		# int
	*/
	public function bit_set($number, $position, $set, $opsize = 0);

	/*
		logic_and									逻辑与运算
		logic_or									逻辑或运算
		logic_xor									逻辑异或运算

		@ int	$number1
		@ int	$number2

		# int
	*/
	public function logic_and	($number1, $number2);
	public function logic_or	($number1, $number2);
	public function logic_xor	($number1, $number2);

	/*
		logic_not									逻辑非运算
		logic_neg									逻辑补运算

		@ int	$number

		# int
	*/
	public function logic_not	($number);
	public function logic_neg	($number);

	/*
		shift_left									左移
		shift_right									右移

		@ int	$number
		@ int	$position							移动位数
		@ bool	$logic		= false					逻辑位移
		@ bool	$circle		= false					循环位移
		@ int	$opsize		= 0

		# array	(R, L, H)							L: 最后移出一位 (CF), H: 结果最高有效位
	*/
	public function shift_left	($number, $position, $logic = false, $circle = false, $opsize = 0);
	public function shift_right	($number, $position, $logic = false, $circle = false, $opsize = 0);
}

/*
	plato_class_decoder								opcode 解码器
*/
interface plato_interface_decoder {
	/*
		@ class	plato_class_loader	$ldr
	*/
	public function __construct($ldr);

	/*
		parse										解码 EIP 地址的数据

		# call										解码成功, 返回执行单元的模块方法名
		# bool										解码失败, 返回 false, 使用重复前缀执行完毕后返回 true
	*/
	public function parse();

	/*
		next_byte									读取下一个字节
		next_word									读取下一个字
		next_dword									读取下一个双字

		@ int	$protect	= true					读取的内容受到执行保护

		# int
	*/
	public function next_byte	($protect = true);
	public function next_word	($protect = true);
	public function next_dword	($protect = true);

	/*
		offset										修改寻址指向的内存地址
													如果是寄存器寻址, 不进行任何操作

		@ int	$number								偏移量
	*/
	public function offset($number);
}

/*
	plato_version_*_service							服务程序
*/
interface plato_interface_service {
	/*
		@ class	plato_class_loader	$ldr
		@ int	$table
	*/
	public function __construct($ldr, $table);

	/*
		reset										重置所有运行环境
	*/
	public function reset();
}

/*
	plato_version_*_module							执行单元共享模块
*/
interface plato_interface_module {
	/*
		initialize									初始化模块

		@ class	plato_class_loader	$ldr
	*/
	static public function initialize($ldr);
}
