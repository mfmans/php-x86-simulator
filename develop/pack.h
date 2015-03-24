/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ pack.h   #D3
*/

#ifndef _PLATO_PACK_H_
#define _PLATO_PACK_H_


/* 运行模式 */
#define P_MODE				__plato_plato_mode()
/* 模拟器版本 */
#define P_VERSION			__plato_plato_version()



/* 异常处理 */
#define _P_TRY(id)			__asm { push	DWORD PTR offset __plato_exception_label_##id##_handle	}		\
							__asm { call	__plato_exception_start									}		\
							if(1)
#define _P_CATCH(id)		__plato_exception_end();														\
							__asm { mov	eax, __plato_exception_label_##id##_next					}		\
							__asm { jmp	eax															}		\
							__plato_exception_label_##id##_handle:											\
							if(1)
#define _P_CATCH_V(id, v)	__plato_exception_end();														\
							__asm { mov	eax, __plato_exception_label_##id##_next					}		\
							__asm { jmp	eax															}		\
							__plato_exception_label_##id##_handle:											\
							__asm { mov v, eax														}		\
							if(1)
#define _P_FINAL(id)		__plato_exception_label_##id##_next:											\
							if(1)

#define P_TRY				_P_TRY
#define P_CATCH				_P_CATCH
#define P_CATCH_V			_P_CATCH_V
#define P_FINAL				_P_FINAL



/* calloc */
plato_pointer_t __plato_calloc__(plato_size_t n, plato_size_t size);


#define P_MALLOC			__plato_memory_heap_allocate
#define P_CALLOC			__plato_calloc__
#define P_REALLOC			__plato_memory_heap_reallocate
#define P_FREE				__plato_memory_heap_free
#define P_MEMORY_ZERO		__plato_memory_heap_zero



#define P_ARG(v)			__plato_argument(PLT_VAR(v))
#define P_ARG_INT			__plato_argument_int
#define P_ARG_FLOAT			__plato_argument_float
#define P_ARG_STRING		__plato_argument_string

#define P_RET(v)			__plato_store(PLT_VAR(v))
#define P_RET_INT			__plato_store_int()
#define P_RET_FLOAT			__plato_store_float()
#define P_RET_STRING		__plato_store_string()


/* 调用函数 */
#define _P_CALL_(dummy, ...)		\
	__plato_call();					\
	__VA_ARGS__;

#define P_CALL(f, ...)					_P_CALL_(0, __VA_ARGS__); __plato_invoke			(f);
#define P_CALL_CLASS(v, m, ...)			_P_CALL_(0, __VA_ARGS__); __plato_invoke_instant	(PLT_VAR(v), m);
#define P_CALL_STATIC(c, m, ...)		_P_CALL_(0, __VA_ARGS__); __plato_invoke_static		(c, m);


/* 实例化类 */
#define P_NEW(v, cls, ...)				_P_CALL_(__plato_instant,			__VA_ARGS__)(PLT_VAR(v), cls);	if(1);



/* 变量读写 */
#define _P_IN_(t, v, d)					__plato_var_in_##t	(PLT_VAR(v), (plato_##t##_t) d)
#define _P_OUT_(t, v)					__plato_var_out_##t	(PLT_VAR(v))

#define P_VAR(v)						__plato_var(PLT_VAR(v))
#define P_IN_INT(v, d)					_P_IN_	(int,		v, d)
#define P_IN_FLOAT(v, d)				_P_IN_	(float,		v, d)
#define P_IN_STRING(v, d)				_P_IN_	(string,	v, d)
#define P_OUT_INT(v)					_P_OUT_	(int,		v)
#define P_OUT_FLOAT(v)					_P_OUT_	(float,		v)
#define P_OUT_STRING(v)					_P_OUT_	(string,	v)


#define P_IS_NULL(v)					__plato_var_is_null		(PLT_VAR(v))
#define P_IS_OBJECT(v)					__plato_var_is_object	(PLT_VAR(v))
#define P_IS_RESOURCE(v)				__plato_var_is_resource	(PLT_VAR(v))
#define P_IS_ARRAY(v)					__plato_var_is_array	(PLT_VAR(v))
#define P_IS_BOOL(v)					__plato_var_is_bool		(PLT_VAR(v))
#define P_IS_INT(v)						__plato_var_is_int		(PLT_VAR(v))
#define P_IS_FLOAT(v)					__plato_var_is_float	(PLT_VAR(v))
#define P_IS_STRING(v)					__plato_var_is_string	(PLT_VAR(v))
#define P_IS_CALLABLE(v)				__plato_var_is_callable	(PLT_VAR(v))
#define P_IS_SCALAR(v)					__plato_var_is_scalar	(PLT_VAR(v))


/* 将全局变量映射到变量池中的变量 */
#define P_GLOBAL(v, n)					__plato_var_global		(PLT_VAR(v), n)

#define P_COPY(dst, src)				__plato_var_copy		(PLT_VAR(dst), PLT_VAR(src))
#define P_BIND(dst, src)				__plato_var_copy		(PLT_VAR(dst), PLT_VAR(src))
#define P_BIND_ARRAY(dst, src, key)		__plato_var_bind_array	(PLT_VAR(dst), PLT_VAR(src), PLT_VAR(key))
#define P_ISSET(v)						__plato_var_isset		(PLT_VAR(v))
#define P_ISSET_ARRAY(v, key)			__plato_var_isset_array	(PLT_VAR(v), PLT_VAR(key))
#define P_UNSET(v)						__plato_var_unset		(PLT_VAR(v))
#define P_UNSET_ARRAY(v, key)			__plato_var_unset_array	(PLT_VAR(v), PLT_VAR(key))



/*
	以下是函数定义
	更多函数封装在 function.h
*/

#define p_exit					__plato_php_exit
#define p_echo					__plato_php_echo
#define p_eval					__plato_php_eval
#define p_include				__plato_php_include
#define p_include_once			__plato_php_include_once
#define p_function_exists		__plato_php_function_exists
#define p_plato_include			__plato_plato_include



#endif   /* _PLATO_PACK_H_ */