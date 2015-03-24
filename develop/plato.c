/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ plato.c   #D3
*/

#include "plato.h"


#define PLT_PACK_3(v1, v2, v3)				__asm _emit v1 __asm _emit v2 __asm _emit v3
#define PLT_PACK_4(v1, v2, v3, v4)			__asm _emit v1 __asm _emit v2 __asm _emit v3 __asm _emit v4
#define PLT_PACK_5(v1, v2, v3, v4, v5)		__asm _emit v1 __asm _emit v2 __asm _emit v3 __asm _emit v4 __asm _emit v5


#define PLT_SERVICE_CODE(name, ret, ...)		\
	__declspec(naked) PLT_SERVICE (name, ret, __VA_ARGS__) { PLT_PACK_5(0xCC, 0xCC, 0xCC, 0xCC, __COUNTER__) }



/* 标志位 */
#define PLT_MAGIC								\
	PLT_PACK_4 (0xCC, 0xCC, 0x90, 0x90)			\
	PLT_PACK_4 (0x90, 0xCC, 0x90, 0xCC)

/* 版本 */
#define PLT_VERSION								\
	PLT_PACK_4 (0xD3, 0x00, 0x00, 0x00)



/* # 服务程序表 */
static void * __service_table[] = {
	& __plato_exception_start,
	& __plato_exception_end,
	& __plato_call,
	& __plato_argument,
	& __plato_argument_int,
	& __plato_argument_float,
	& __plato_argument_string,
	& __plato_invoke,
	& __plato_invoke_instant,
	& __plato_invoke_static,
	& __plato_store,
	& __plato_store_int,
	& __plato_store_float,
	& __plato_store_string,
	& __plato_instant,
	& __plato_register,
	& __plato_var,
	& __plato_var_in_int,
	& __plato_var_in_float,
	& __plato_var_in_string,
	& __plato_var_out_int,
	& __plato_var_out_float,
	& __plato_var_out_string,
	& __plato_var_is_null,
	& __plato_var_is_object,
	& __plato_var_is_resource,
	& __plato_var_is_array,
	& __plato_var_is_bool,
	& __plato_var_is_int,
	& __plato_var_is_float,
	& __plato_var_is_string,
	& __plato_var_is_callable,
	& __plato_var_is_scalar,
	& __plato_var_global,
	& __plato_var_copy,
	& __plato_var_bind,
	& __plato_var_bind_array,
	& __plato_var_isset,
	& __plato_var_isset_array,
	& __plato_var_unset,
	& __plato_var_unset_array,
	& __plato_memory_page_allocate,
	& __plato_memory_page_free,
	& __plato_memory_page_flag,
	& __plato_memory_heap_allocate,
	& __plato_memory_heap_reallocate,
	& __plato_memory_heap_free,
	& __plato_memory_heap_zero,
	& __plato_php_exit,
	& __plato_php_echo,
	& __plato_php_eval,
	& __plato_php_include,
	& __plato_php_include_once,
	& __plato_php_function_exists,
	& __plato_plato_mode,
	& __plato_plato_version,
	& __plato_plato_include,
};


/* # 服务程序表入口 */
PLT_EXPORT __declspec(naked) void __plato_table() {
	PLT_MAGIC
	PLT_VERSION

	/* reserved */
	PLT_PACK_4 (0x90, 0x90, 0x90, 0x90)

	/* align */
	PLT_PACK_3 (0x90, 0x90, 0x90)
	/* service table */
	__asm	mov eax, offset __service_table
}

/* # 主程序入口 */
PLT_EXPORT __declspec(naked) void __plato_main() {
	__asm {
			call	__plato_plato_mode

			test	eax, eax
			jnz		run

			ret

		run:
			mov		eax, offset plato_main
			jmp		eax
	}
}


/* # 运行模式 */
__declspec(naked) PLT_SERVICE (plato_mode, int) {
	/* xor eax, eax */
	/* ret */
	PLT_PACK_4 (0x90, 0x90, 0x90, 0x90)
	PLT_PACK_3 (0x31, 0xC0, 0xC3)
}



PLT_SERVICE_CODE (exception_start,			void,				plato_pointer_t		addr);
PLT_SERVICE_CODE (exception_end,			void);


PLT_SERVICE_CODE (call,						void);

PLT_SERVICE_CODE (argument,					void,				plato_variable_t	v);
PLT_SERVICE_CODE (argument_int,				void,				plato_int_t			d);
PLT_SERVICE_CODE (argument_float,			void,				plato_float_t		d);
PLT_SERVICE_CODE (argument_string,			void,				plato_string_t		d);

PLT_SERVICE_CODE (invoke,					plato_variable_t,	plato_callable_t	f);
PLT_SERVICE_CODE (invoke_instant,			plato_variable_t,	plato_variable_t	v,		plato_callable_t	m);
PLT_SERVICE_CODE (invoke_static,			plato_variable_t,	plato_callable_t	c,		plato_callable_t	m);

PLT_SERVICE_CODE (store,					void,				plato_variable_t	v);
PLT_SERVICE_CODE (store_int,				plato_int_t);
PLT_SERVICE_CODE (store_float,				plato_float_t);
PLT_SERVICE_CODE (store_string,				plato_string_t);


PLT_SERVICE_CODE (instant,					void,				plato_variable_t	v,		plato_callable_t	c);

PLT_SERVICE_CODE (register,					void,				plato_callable_t	f,		plato_pointer_t		addr,	plato_return_t		r);


PLT_SERVICE_CODE (var,						void,				plato_variable_t	v);

PLT_SERVICE_CODE (var_in_int,				void,				plato_variable_t	v,		plato_int_t			d);
PLT_SERVICE_CODE (var_in_float,				void,				plato_variable_t	v,		plato_float_t		d);
PLT_SERVICE_CODE (var_in_string,			void,				plato_variable_t	v,		plato_string_t		d);

PLT_SERVICE_CODE (var_out_int,				plato_int_t,		plato_variable_t	v);
PLT_SERVICE_CODE (var_out_float,			plato_float_t,		plato_variable_t	v);
PLT_SERVICE_CODE (var_out_string,			plato_string_t,		plato_variable_t	v);

PLT_SERVICE_CODE (var_is_null,				int,				plato_variable_t	v);
PLT_SERVICE_CODE (var_is_object,			int,				plato_variable_t	v);
PLT_SERVICE_CODE (var_is_resource,			int,				plato_variable_t	v);
PLT_SERVICE_CODE (var_is_array,				int,				plato_variable_t	v);
PLT_SERVICE_CODE (var_is_bool,				int,				plato_variable_t	v);
PLT_SERVICE_CODE (var_is_int,				int,				plato_variable_t	v);
PLT_SERVICE_CODE (var_is_float,				int,				plato_variable_t	v);
PLT_SERVICE_CODE (var_is_string,			int,				plato_variable_t	v);
PLT_SERVICE_CODE (var_is_callable,			int,				plato_variable_t	v);
PLT_SERVICE_CODE (var_is_scalar,			int,				plato_variable_t	v);

PLT_SERVICE_CODE (var_global,				void,				plato_variable_t	dst,	plato_string_t		src);

PLT_SERVICE_CODE (var_copy,					void,				plato_variable_t	dst,	plato_variable_t	src);
PLT_SERVICE_CODE (var_bind,					void,				plato_variable_t	dst,	plato_variable_t	src);
PLT_SERVICE_CODE (var_bind_array,			int,				plato_variable_t	dst,	plato_variable_t	src,	plato_variable_t	k);

PLT_SERVICE_CODE (var_isset,				int,				plato_variable_t	v);
PLT_SERVICE_CODE (var_isset_array,			int,				plato_variable_t	v,		plato_variable_t	k);

PLT_SERVICE_CODE (var_unset,				void,				plato_variable_t	v);
PLT_SERVICE_CODE (var_unset_array,			void,				plato_variable_t	v,		plato_variable_t	k);


PLT_SERVICE_CODE (memory_page_allocate,		plato_pointer_t,	plato_size_t		size);
PLT_SERVICE_CODE (memory_page_free,			void,				plato_pointer_t		p);
PLT_SERVICE_CODE (memory_page_flag,			plato_size_t,		plato_pointer_t		p,		plato_size_t		flag);

PLT_SERVICE_CODE (memory_heap_allocate,		plato_pointer_t,	plato_size_t		size);
PLT_SERVICE_CODE (memory_heap_reallocate,	plato_pointer_t,	plato_pointer_t		p,		plato_size_t		size);
PLT_SERVICE_CODE (memory_heap_free,			void,				plato_pointer_t		p);
PLT_SERVICE_CODE (memory_heap_zero,			void,				plato_pointer_t		p);

PLT_SERVICE_CODE (php_exit,					void,				plato_string_t		s);
PLT_SERVICE_CODE (php_echo,					void,				plato_string_t		s);
PLT_SERVICE_CODE (php_eval,					void,				plato_string_t		s);
PLT_SERVICE_CODE (php_include,				int,				plato_string_t		f);
PLT_SERVICE_CODE (php_include_once,			int,				plato_string_t		f);
PLT_SERVICE_CODE (php_function_exists,		int,				plato_string_t		f);


PLT_SERVICE_CODE (plato_version,			int);
PLT_SERVICE_CODE (plato_include,			plato_variable_t,	plato_string_t		f,		plato_return_t		r);



/* __plato_calloc__ */
plato_pointer_t __plato_calloc__(plato_size_t n, plato_size_t size) {
	plato_pointer_t address = __plato_memory_heap_allocate(n * size);

	__plato_memory_heap_zero(address);

	return address;
}
