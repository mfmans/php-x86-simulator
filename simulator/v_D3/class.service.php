<?php

/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ D3:class/service   #D3
*/

if(!defined('PLATO')) {
	exit('Access Denied');
}

final class plato_version_D3_service implements plato_interface_service {
	static private $function = array (
		/* @ exception */
		'handle_exception_start',
		'handle_exception_end',
		/* @ function call */
		'handle_call',
		'handle_argument',
		'handle_argument_int',
		'handle_argument_float',
		'handle_argument_string',
		'handle_invoke',
		'handle_invoke_instant',
		'handle_invoke_static',
		'handle_store',
		'handle_store_int',
		'handle_store_float',
		'handle_store_string',
		/* @ class instance */
		'handle_instant',
		/* @ function register */
		'handle_register',
		/* @ variable register, read, write */
		'handle_var',
		'handle_var_in_int',
		'handle_var_in_float',
		'handle_var_in_string',
		'handle_var_out_int',
		'handle_var_out_float',
		'handle_var_out_string',
		/* @ variable type check */
		'handle_var_is_null',
		'handle_var_is_object',
		'handle_var_is_resource',
		'handle_var_is_array',
		'handle_var_is_bool',
		'handle_var_is_int',
		'handle_var_is_float',
		'handle_var_is_string',
		'handle_var_is_callable',
		'handle_var_is_scalar',
		/* @ variable copy, check, delete */
		'handle_var_global',
		'handle_var_copy',
		'handle_var_bind',
		'handle_var_bind_array',
		'handle_var_isset',
		'handle_var_isset_array',
		'handle_var_unset',
		'handle_var_unset_array',
		/* @ memory manage */
		'handle_memory_page_allocate',
		'handle_memory_page_free',
		'handle_memory_page_flag',
		'handle_memory_heap_allocate',
		'handle_memory_heap_reallocate',
		'handle_memory_heap_free',
		'handle_memory_heap_zero',
		/* @ language struct */
		'handle_php_exit',
		'handle_php_echo',
		'handle_php_eval',
		'handle_php_include',
		'handle_php_include_once',
		'handle_php_function_exists',
		/* @ plato function */
		'handle_plato_mode',
		'handle_plato_version',
		'handle_plato_include',
	);


	private $ldr;
	private	$cpu;
	private	$table;

	/* variable pool */
	private	$list_variable;
	/* argument pool */
	private	$list_argument;

	/*
		__construct
	*/
	public function __construct($ldr, $table) {
		$this->ldr		= $ldr;
		$this->cpu		= $ldr->cpu;
		$this->table	= $table;

		$this->install();
	}

	/*
		reset
	*/
	public function reset() {
		$this->list_variable = array();
		$this->list_argument = array();
	}

	/*
		exception
	*/
	private function exception($id, $comment = array()) {
		throw new plato_exception ($id, $comment, $this->ldr);
	}

	/*
		install
	*/
	private function install() {
		$count	= count(self::$function);
		$list	= $this->ldr->bin->read_array($this->table, $count, 4);

		/* table not found */
		if(empty($list)) {
			$this->exception (PLATO_EX_SERVICE_TABLE_NOT_FOUND);
		}

		/* not complete */
		if(count($list) != $count) {
			$this->exception (
				PLATO_EX_SERVICE_TABLE_NOT_COMPLETE,
				array (
					'count_need' => $count,
					'count_real' => count($list),
				)
			);
		}

		/* map */
		foreach($list as $id => $address) {
			$method = self::$function[$id];

			if(!$address) {
				$this->exception (
					PLATO_EX_SERVICE_TABLE_NULL,
					array (
						'id'			=> $id,
						'offset%08X'	=> $this->table + $id * 4,
						'address%08X'	=> $address,
						'method'		=> $method
					)
				);
			}

			$this->cpu->address[$address] = array ($this, $method);
		}
	}

	/*
		pop_variable
	*/
	private function pop_variable($key, $exist = true) {
		$var = $this->cpu->pop(4);

		/* not exist */
		if($exist && !isset($this->list_variable[$var])) {
			$this->exception (
				PLATO_EX_SERVICE_CALL_VARIABLE,
				array ("ARG #{$key}" => $var)
			);
		}

		return $var;
	}

	/*
		pop_int
	*/
	private function pop_int() {
		return $this->cpu->pop(4);
	}

	/*
		pop_float
	*/
	private function pop_float($key) {
		$raw = $this->cpu->pop(4);

		/* invald IEEE format */
		if(!$data = @unpack('f', $raw)) {
			$this->exception (
				PLATO_EX_SERVICE_CALL_FLOAT,
				array ("ARG #{$key}%x" => $raw)
			);
		}

		return $data[1];
	}

	/*
		pop_string
	*/
	private function pop_string($key, $empty = false) {
		if($address = $this->cpu->pop(4)) {
			if($string = $this->ldr->memory->read_string($address)) {
				return $string;
			}
		}

		if($empty) {
			return null;
		}

		$this->exception (
			PLATO_EX_SERVICE_CALL_STRING,
			array ("ARG #{$key}%x" => $address)
		);
	}

	/*
		result
	*/
	private function result($data = 0) {
		$this->ldr->cpu->register->eax = (int) $data;
	}

	/*
		handle_exception_start						���ô����쳣�й�

		@ int	$address							�쳣���������
	*/
	public function handle_exception_start() {
		$address = $this->pop_int();

		$this->cpu->exception_push($address);
	}

	/*
		handle_exception_end						�ر��쳣�����й�
	*/
	public function handle_exception_end() {
		$this->cpu->exception_pop();
	}

	/*
		handle_call									��ʼ����������
	*/
	public function handle_call() {
		$this->list_argument	= array();
		$this->list_variable[0]	= null;
	}

	/*
		handle_argument								�ӱ�������ѡ��һ������ѹ�������

		@ str	$var
	*/
	public function handle_argument() {
		$var = $this->pop_variable(0);

		$this->list_argument[] = $this->list_variable[$var];
	}

	/*
		handle_argument_int							�������ѹ��һ������

		@ int	$data
	*/
	public function handle_argument_int() {
		$this->list_argument[] = $this->pop_int();
	}

	/*
		handle_argument_float						�������ѹ��һ��������

		@ float	$data
	*/
	public function handle_argument_float() {
		$this->list_argument[] = $this->pop_float(0);
	}

	/*
		handle_argument_string						�������ѹ��һ���ַ���

		@ str	$data
	*/
	public function handle_argument_string() {
		$this->list_argument[] = $this->pop_string(0, true);
	}

	/*
		handle_invoke								���ú���

		@ str	$function

		# int
	*/
	public function handle_invoke() {
		$function = $this->pop_string(0);

		if(empty($function) || !function_exists($function)) {
			$this->exception (
				PLATO_EX_SERVICE_CALL_FUNCTION,
				array ('function' => $function)
			);
		}

		$this->list_variable[0] = call_user_func_array($function, $this->list_argument);

		$this->result();
	}

	/*
		handle_invoke_instant						������ʵ������

		@ int	$var
		@ str	$method

		# int
	*/
	public function handle_invoke_instant() {
		$object		= $this->pop_variable	(0);
		$method		= $this->pop_string		(1);

		$callback	= array ($object, $method);

		/* is_callable() for __call */
		if(empty($method) || !is_object($object) || !is_callable($callback)) {
			$this->exception (
				PLATO_EX_SERVICE_CALL_FUNCTION,
				array (
					'class'		=> (string) @get_class($object),
					'method'	=> $method
				)
			);
		}

		$this->list_variable[0] = call_user_func_array($callback, $this->list_argument);

		$this->result();
	}

	/*
		handle_invoke_static						�����ྲ̬����

		@ str	$class
		@ str	$method

		# int
	*/
	public function handle_invoke_static() {
		$class		= $this->pop_string(0);
		$method		= $this->pop_string(1);

		$callback	= array ($class, $method);

		if(empty($method) || !class_exists($class) || !is_callable($callback)) {
			$this->exception (
				PLATO_EX_SERVICE_CALL_FUNCTION,
				array (
					'class'		=> $class,
					'method'	=> $method
				)
			);
		}

		$this->list_variable[0] = call_user_func_array($callback, $this->list_argument);

		$this->result();
	}

	/*
		handle_store								�����õĺ�������ֵ���浽��������

		@ int	$var
	*/
	public function handle_store() {
		$var = $this->pop_variable(0, false);

		$this->list_variable[$var] = $this->list_variable[0];
	}

	/*
		handle_store_int							�����õĺ�������ֵ��Ϊ��������

		# int
	*/
	public function handle_store_int() {
		$data = $this->list_variable[0];

		$this->result($data);
	}

	/*
		handle_store_float							�����õĺ�������ֵ��Ϊ����������

		# float
	*/
	public function handle_store_float() {
		$data = @pack('f', (float) $this->list_variable[0]);

		$this->result($data);
	}

	/*
		handle_store_string							�����õĺ�������ֵ��Ϊ�ַ�������

		# str
	*/
	public function handle_store_string() {
		$string		= (string) $this->list_variable[0];
		$address	= $this->ldr->memory->store_string($string);

		$this->result($address);
	}

	/*
		handle_instant								������ʵ��

		@ int	$var
		@ str	$class
	*/
	public function handle_instant() {
		$var	= $this->pop_variable	(0, false);
		$class	= $this->pop_string		(1);

		if(class_exists($class)) {
			try {
				$object		= new ReflectionClass ($class);
				$instant	= $object->newInstanceArgs($this->list_argument);

				$this->list_variable[$var] = $instant;

				return;
			} catch (Exception $ex) {
				/* do nothing */
			}
		}

		$this->exception (
			PLATO_EX_SERVICE_CALL_CLASS,
			array ('class' => $class)
		);
	}

	/*
		handle_register								ע�ắ��

		@ str	$function
		@ int	$address
		@ int	$return
	*/
	public function handle_register() {
		$function	= $this->pop_string	(0);
		$address	= $this->pop_int	();
		$return		= $this->pop_int	();

		$this->ldr->register($function, $address, $return);
	}

	/*
		handle_var									�ڱ������д�������

		@ int	$var
	*/
	public function handle_var() {
		$var = $this->pop_variable(0, false);

		$this->list_variable[$var] = false;
	}

	/*
		handle_var_in_int							�������д������

		@ int	$var
		@ int	$data
	*/
	public function handle_var_in_int() {
		$var	= $this->pop_variable	(0, false);
		$data	= $this->pop_int		();

		$this->list_variable[$var] = $data;
	}

	/*
		handle_var_in_float							�������д�븡����

		@ int	$var
		@ float	$data
	*/
	public function handle_var_in_float() {
		$var	= $this->pop_variable	(0, false);
		$data	= $this->pop_float		(1);

		$this->list_variable[$var] = $data;
	}

	/*
		handle_var_in_string						�������д���ַ���

		@ int	$var
		@ str	$data
	*/
	public function handle_var_in_string() {
		$var	= $this->pop_variable	(0, false);
		$data	= $this->pop_string		(1, true);

		$this->list_variable[$var] = $data;
	}

	/*
		handle_var_out_int							�ӱ����ж�ȡ����

		@ int	$var

		# int
	*/
	public function handle_var_out_int() {
		$var	= $this->pop_variable(0);
		$data	= $this->list_variable[$var];

		$this->result($data);
	}

	/*
		handle_var_out_float						�ӱ����ж�ȡ������

		@ int	$var

		# float
	*/
	public function handle_var_out_float() {
		$var	= $this->pop_variable(0);
		$data	= @pack('f', (float) $this->list_variable[$var]);

		$this->result($data);	
	}

	/*
		handle_var_out_string						�ӱ����ж�ȡ�ַ���

		@ int	$var

		# str
	*/
	public function handle_var_out_string() {
		$var		= $this->pop_variable(0);
		$string		= (string) $this->list_variable[$var];

		$address	= $this->ldr->memory->store_string($string);

		$this->result($address);
	}

	/*
		handle_var_is_null							is_null

		@ int	$var

		# bool
	*/
	public function handle_var_is_null() {
		$var	= $this->pop_variable(0);
		$check	= is_null($this->list_variable[$var]);

		$this->result($check);
	}

	/*
		handle_var_is_object						is_object

		@ int	$var

		# bool
	*/
	public function handle_var_is_object() {
		$var	= $this->pop_variable(0);
		$check	= is_object($this->list_variable[$var]);

		$this->result($check);
	}

	/*
		handle_var_is_resource						is_resource

		@ int	$var

		# bool
	*/
	public function handle_var_is_resource() {
		$var	= $this->pop_variable(0);
		$check	= is_resource($this->list_variable[$var]);

		$this->result($check);
	}

	/*
		handle_var_is_array							is_array

		@ int	$var

		# bool
	*/
	public function handle_var_is_array() {
		$var	= $this->pop_variable(0);
		$check	= is_array($this->list_variable[$var]);

		$this->result($check);
	}

	/*
		handle_var_is_bool							is_bool

		@ int	$var

		# bool
	*/
	public function handle_var_is_bool() {
		$var	= $this->pop_variable(0);
		$check	= is_bool($this->list_variable[$var]);

		$this->result($check);
	}

	/*
		handle_var_is_int							is_int

		@ int	$var

		# bool
	*/
	public function handle_var_is_int() {
		$var	= $this->pop_variable(0);
		$check	= is_int($this->list_variable[$var]);

		$this->result($check);
	}

	/*
		handle_var_is_float							is_float

		@ int	$var

		# bool
	*/
	public function handle_var_is_float() {
		$var	= $this->pop_variable(0);
		$check	= is_float($this->list_variable[$var]);

		$this->result($check);
	}

	/*
		handle_var_is_string						is_string

		@ int	$var

		# bool
	*/
	public function handle_var_is_string() {
		$var	= $this->pop_variable(0);
		$check	= is_string($this->list_variable[$var]);

		$this->result($check);
	}

	/*
		handle_var_is_callable						is_callable

		@ int	$var

		# bool
	*/
	public function handle_var_is_callable() {
		$var	= $this->pop_variable(0);
		$check	= is_callable($this->list_variable[$var]);

		$this->result($check);
	}

	/*
		handle_var_is_scalar						is_scalar

		@ int	$var

		# bool
	*/
	public function handle_var_is_scalar() {
		$var	= $this->pop_variable(0);
		$check	= is_scalar($this->list_variable[$var]);

		$this->result($check);
	}

	/*
		handle_var_global							��ȫ�ֱ���ӳ�䵽�������еı���

		@ int	$var_dst
		@ str	$var_glb
	*/
	public function handle_var_global() {
		$target = $this->pop_variable	(0, false);
		$source = $this->pop_string		(1);

		if(!isset($GLOBALS[$source])) {
			$GLOBALS[$source] = false;
		}

		$this->list_variable[$target] = & $GLOBALS[$source];
	}

	/*
		handle_var_copy								��������

		@ int	$var_dst
		@ int	$var_src
	*/
	public function handle_var_copy() {
		$target = $this->pop_variable(0, false);
		$source = $this->pop_variable(1);

		$this->list_variable[$target] = $this->list_variable[$source];
	}

	/*
		handle_var_bind								�����õı�������

		@ int	$var_dst
		@ int	$var_src
	*/
	public function handle_var_bind() {
		$target = $this->pop_variable(0, false);
		$source = $this->pop_variable(1);

		$this->list_variable[$target] = & $this->list_variable[$source];
	}

	/*
		handle_var_bind_array						�����õ������������

		@ int	$var_dst
		@ int	$var_src
		@ int	$key
	*/
	public function handle_var_bind_array() {
		$target = $this->pop_variable(0, false);
		$source = $this->pop_variable(1);
		$key	= $this->pop_variable(2, false);

		if(!is_array($this->list_variable[$source]) || !isset($this->list_variable[$source][$key])) {
			$this->exception (PLATO_EX_SERVICE_CALL_ARRAY);
		}

		$this->list_variable[$target] = & $this->list_variable[$source][$key];
	}

	/*
		handle_var_isset							�������Ƿ����

		@ int	$var

		# bool
	*/
	public function handle_var_isset() {
		$var	= $this->pop_variable(0, false);
		$check	= isset($this->list_variable[$var]);

		$this->result($check);
	}

	/*
		handle_var_isset_array						�������������Ƿ����

		@ int	$var
		@ int	$key

		# bool
	*/
	public function handle_var_isset_array() {
		$var	= $this->pop_variable(0);
		$key	= $this->pop_variable(1, false);

		$check	= 0;

		if(is_array($this->list_variable[$var]) && isset($this->list_variable[$var][$key])) {
			$check = 1;
		}

		$this->result($check);
	}

	/*
		handle_var_unset							ɾ������

		@ int	$var
	*/
	public function handle_var_unset() {
		$id = $this->pop_variable(0, false);

		$this->list_variable[$id] = null;

		unset($this->list_variable[$id]);
	}

	/*
		handle_var_unset_array						ɾ�����������

		@ int	$var
		@ int	$key
	*/
	public function handle_var_unset_array() {
		$var	= $this->pop_variable(0, false);
		$key	= $this->pop_variable(1, false);

		if(!isset($this->list_variable[$var]) || !is_array($this->list_variable[$var])) {
			return;
		}

		$this->list_variable[$var][$key] = null;

		unset($this->list_variable[$var][$key]);
	}

	/*
		handle_memory_page_allocate					�����ڴ�ҳ

		@ int	$size

		# int
	*/
	public function handle_memory_page_allocate() {
		$size		= $this->pop_int();
		$address	= $this->ldr->memory->page_allocate($size);

		$this->result($address);
	}

	/*
		handle_memory_page_free						�ͷ��ڴ�ҳ

		@ int	$address
	*/
	public function handle_memory_page_free() {
		$address = $this->pop_int();

		$this->ldr->memory->page_free($address);
	}

	/*
		handle_memory_page_flag						�޸�ҳ����

		@ int	$address
		@ int	$setting

		# int
	*/
	public function handle_memory_page_flag() {
		$address	= $this->pop_int();
		$setting	= $this->pop_int();

		$readable	= -1;
		$writable	= -1;
		$executable	= -1;

		if($setting & 0x01) {
			$readable	= ($setting >> 1) & 0x01;
		}
		if($setting & 0x04) {
			$writable	= ($setting >> 3) & 0x01;
		}
		if($setting & 0x10) {
			$executable	= ($setting >> 5) & 0x01;
		}

		$result = $this->ldr->memory->page_attribute($address, $readable, $writable, $executable);
		$return = 0;

		if($result[0]) {		/* R */
			$return |= 0x01;
		}
		if($result[1]) {		/* W */
			$return |= 0x02;
		}
		if($result[2]) {		/* E */
			$return |= 0x04;
		}

		$this->result($return);
	}

	/*
		handle_memory_heap_allocate					�ڶ��Ϸ����ڴ�

		@ int	$size

		# int
	*/
	public function handle_memory_heap_allocate() {
		$size		= $this->pop_int();
		$address	= $this->ldr->memory->heap_allocate($size);

		$this->result($address);
	}

	/*
		handle_memory_heap_reallocate				�ڶ����ط����ڴ�

		@ int	$address
		@ int	$size

		# int
	*/
	public function handle_memory_heap_reallocate() {
		$address	= $this->pop_int();
		$size		= $this->pop_int();

		$address	= $this->ldr->memory->heap_reallocate($address, $size);

		$this->result($address);
	}

	/*
		handle_memory_heap_free						�ڶ����ͷ��ڴ�

		@ int	$address
	*/
	public function handle_memory_heap_free() {
		$address = $this->pop_int();

		$this->ldr->memory->heap_free($address);
	}

	/*
		handle_memory_heap_zero						���Ӷ��Ϸ�����ڴ���ջ��ʼ��

		@ int	$address
	*/
	public function handle_memory_heap_zero() {
		$address = $this->pop_int();

		$this->ldr->memory->heap_zero($address);
	}

	/*
		handle_php_exit								exit

		@ str	$text
	*/
	public function handle_php_exit() {
		$text = $this->pop_string(0, true);

		exit($text);
	}

	/*
		handle_php_echo								echo

		@ str	$text
	*/
	public function handle_php_echo() {
		$text = $this->pop_string(0, true);

		if($text !== null) {
			echo $text;
		}
	}

	/*
		handle_php_eval								eval

		@ str	$code
	*/
	public function handle_php_eval() {
		$code = $this->pop_string(0, true);

		if($code !== null) {
			eval($code);
		}
	}

	/*
		handle_php_include							include

		@ str	$file

		# bool
	*/
	public function handle_php_include() {
		$file	= $this->pop_string(0);
		$result	= (bool) (@include $file);

		$this->result($result);
	}

	/*
		handle_php_include_once						include_once

		@ str	$file

		# bool
	*/
	public function handle_php_include_once() {
		$file	= $this->pop_string(0);
		$result	= (bool) (@include_once $file);

		$this->result($result);
	}

	/*
		handle_php_function_exists					function_exists

		@ str	$function

		# bool
	*/
	public function handle_php_function_exists() {
		$function	= $this->pop_string(0);
		$check		= function_exists($function);

		$this->result($check);
	}

	/*
		handle_plato_mode							�жϳ����Ƿ������� plato ģ������

		# int
	*/
	public function handle_plato_mode() {
		$this->result(1);
	}

	/*
		handle_plato_version						��ȡ plato ģ�����汾

		# int
	*/
	public function handle_plato_version() {
		$this->result(0xD3);
	}

	/*
		handle_plato_include						plato_include

		@ str	$file
		@ int	$return
	*/
	public function handle_plato_include() {
		$return	= $this->pop_int();
		$file	= $this->pop_string(0);

		$this->list_variable[0] = plato_include($file, $this->list_argument, $return);

		$this->ldr->recover();
	}
}
