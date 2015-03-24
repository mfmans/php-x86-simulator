/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ service.h   #D3
*/

#ifndef _PLATO_SERVICE_H_
#define _PLATO_SERVICE_H_


#if defined(__cplusplus)
	extern "C" {
#endif


/* 启用代码异常托管 */
PLT_SERVICE (exception_start,			void,				plato_pointer_t		addr);
/* 关闭异常代码托管 */
PLT_SERVICE (exception_end,				void);


/* 初始化函数调用 */
PLT_SERVICE (call,						void);

/* 从变量池中选择一个变量压入参数池 */
PLT_SERVICE (argument,					void,				plato_variable_t	v);
/* 向参数池压入一个整数 */
PLT_SERVICE (argument_int,				void,				plato_int_t			d);
/* 向参数池压入一个浮点数 */
PLT_SERVICE (argument_float,			void,				plato_float_t		d);
/* 向参数池压入一个字符串 */
PLT_SERVICE (argument_string,			void,				plato_string_t		d);

/* 调用函数 */
PLT_SERVICE (invoke,					plato_variable_t,	plato_callable_t	f);
/* 调用类实例方法 */
PLT_SERVICE (invoke_instant,			plato_variable_t,	plato_variable_t	v,		plato_callable_t	m);
/* 调用类静态方法 */
PLT_SERVICE (invoke_static,				plato_variable_t,	plato_callable_t	c,		plato_callable_t	m);

/* 将调用的函数返回值保存到变量池中 */
PLT_SERVICE (store,						void,				plato_variable_t	v);
/* 将调用的函数返回值作为整数返回 */
PLT_SERVICE (store_int,					plato_int_t);
/* 将调用的函数返回值作为浮点数返回 */
PLT_SERVICE (store_float,				plato_float_t);
/* 将调用的函数返回值作为字符串返回 */
PLT_SERVICE (store_string,				plato_string_t);


/* 创建类实例 */
PLT_SERVICE (instant,					void,				plato_variable_t	v,		plato_callable_t	c);

/* 注册函数 */
PLT_SERVICE (register,					void,				plato_callable_t	f,		plato_pointer_t		addr,	plato_return_t		r);


/* 在变量池中创建变量 */
PLT_SERVICE (var,						void,				plato_variable_t	v);

/* 向变量中写入整数 */
PLT_SERVICE (var_in_int,				void,				plato_variable_t	v,		plato_int_t			d);
/* 向变量中写入浮点数 */
PLT_SERVICE (var_in_float,				void,				plato_variable_t	v,		plato_float_t		d);
/* 向变量中写入字符串 */
PLT_SERVICE (var_in_string,				void,				plato_variable_t	v,		plato_string_t		d);

/* 从变量中读取整数 */
PLT_SERVICE (var_out_int,				plato_int_t,		plato_variable_t	v);
/* 从变量中读取浮点数 */
PLT_SERVICE (var_out_float,				plato_float_t,		plato_variable_t	v);
/* 从变量中读取字符串 */
PLT_SERVICE (var_out_string,			plato_string_t,		plato_variable_t	v);

/* 判断变量类型 */
PLT_SERVICE (var_is_null,				int,				plato_variable_t	v);
PLT_SERVICE (var_is_object,				int,				plato_variable_t	v);
PLT_SERVICE (var_is_resource,			int,				plato_variable_t	v);
PLT_SERVICE (var_is_array,				int,				plato_variable_t	v);
PLT_SERVICE (var_is_bool,				int,				plato_variable_t	v);
PLT_SERVICE (var_is_int,				int,				plato_variable_t	v);
PLT_SERVICE (var_is_float,				int,				plato_variable_t	v);
PLT_SERVICE (var_is_string,				int,				plato_variable_t	v);
PLT_SERVICE (var_is_callable,			int,				plato_variable_t	v);
PLT_SERVICE (var_is_scalar,				int,				plato_variable_t	v);

/* 将全局变量映射到变量池中的变量 */
PLT_SERVICE (var_global,				void,				plato_variable_t	dst,	plato_string_t		src);

/* 变量复制 */
PLT_SERVICE (var_copy,					void,				plato_variable_t	dst,	plato_variable_t	src);
/* 带引用的变量复制 */
PLT_SERVICE (var_bind,					void,				plato_variable_t	dst,	plato_variable_t	src);
/* 带引用的数组变量复制 */
PLT_SERVICE (var_bind_array,			int,				plato_variable_t	dst,	plato_variable_t	src,	plato_variable_t	k);

/* 检测变量是否存在 */
PLT_SERVICE (var_isset,					int,				plato_variable_t	v);
/* 检测数组变量项是否存在 */
PLT_SERVICE (var_isset_array,			int,				plato_variable_t	v,		plato_variable_t	k);

/* 删除变量 */
PLT_SERVICE (var_unset,					void,				plato_variable_t	v);
/* 删除数组变量项 */
PLT_SERVICE (var_unset_array,			void,				plato_variable_t	v,		plato_variable_t	k);


/* 分配内存页 */
PLT_SERVICE (memory_page_allocate,		plato_pointer_t,	plato_size_t		size);
/* 释放内存页 */
PLT_SERVICE (memory_page_free,			void,				plato_pointer_t		p);
/* 修改页属性 */
PLT_SERVICE (memory_page_flag,			plato_size_t,		plato_pointer_t		p,		plato_size_t		flag);

/* 在堆上分配内存 */
PLT_SERVICE (memory_heap_allocate,		plato_pointer_t,	plato_size_t		size);
/* 在堆上重分配内存 */
PLT_SERVICE (memory_heap_reallocate,	plato_pointer_t,	plato_pointer_t		p,		plato_size_t		size);
/* 在堆上释放内存 */
PLT_SERVICE (memory_heap_free,			void,				plato_pointer_t		p);
/* 将从堆上分配的内存清空或初始化 */
PLT_SERVICE (memory_heap_zero,			void,				plato_pointer_t		p);


/* PHP 函数或语言结构 */
PLT_SERVICE (php_exit,					void,				plato_string_t		s);
PLT_SERVICE (php_echo,					void,				plato_string_t		s);
PLT_SERVICE (php_eval,					void,				plato_string_t		s);
PLT_SERVICE (php_include,				int,				plato_string_t		f);
PLT_SERVICE (php_include_once,			int,				plato_string_t		f);
PLT_SERVICE (php_function_exists,		int,				plato_string_t		f);


/* 判断程序是否运行在 plato 模拟器中 */
PLT_SERVICE (plato_mode,				int);
/* 获取 plato 模拟器版本 */
PLT_SERVICE (plato_version,				int);
/* plato_include */
PLT_SERVICE (plato_include,				plato_variable_t,	plato_string_t		f,		plato_return_t		r);


#if defined(__cplusplus)
	}
#endif

#endif   /* _PLATO_SERVICE_H_ */