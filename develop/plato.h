/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ plato.h   #D3
*/

#ifndef _PLATO_H_
#define _PLATO_H_


/* unsigned int */
typedef unsigned __int32		plato_size_t;
/* 32-bit pointer */
typedef void *					plato_pointer_t;


/* return type */
typedef signed int				plato_int_t;
typedef float					plato_float_t;
typedef char *					plato_string_t;


/* variable name */
typedef plato_size_t			plato_variable_t;
/* callable object name */
typedef plato_string_t			plato_callable_t;

/* index of array */
typedef plato_size_t			plato_index_t;
/* return type */
typedef plato_size_t			plato_return_t;



/* for API */
#define PLT_CALL				__stdcall
#define PLT_EXPORT				__declspec(dllexport)


/* for service */
#define PLT_NAME(name)						__plato_##name
#define PLT_SERVICE(name, ret, ...)			ret PLT_CALL PLT_NAME(name) (__VA_ARGS__)

/* for variable */
#define PLT_RET					(plato_variable_t) 0
#define PLT_VAR(id)				((plato_variable_t) & ("PLATO_VARIABLE_" # id))



/* # 主入口 */
int PLT_CALL plato_main(int argc, void *args[]);



/* # 扩展定义 */
#include "exception.h"
#include "service.h"
#include "pack.h"
#include "function.h"


#endif   /* _PLATO_H_ */