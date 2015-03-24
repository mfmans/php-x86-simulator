/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ service.h   #D3
*/

#ifndef _PLATO_SERVICE_H_
#define _PLATO_SERVICE_H_


#if defined(__cplusplus)
	extern "C" {
#endif


/* ���ô����쳣�й� */
PLT_SERVICE (exception_start,			void,				plato_pointer_t		addr);
/* �ر��쳣�����й� */
PLT_SERVICE (exception_end,				void);


/* ��ʼ���������� */
PLT_SERVICE (call,						void);

/* �ӱ�������ѡ��һ������ѹ������� */
PLT_SERVICE (argument,					void,				plato_variable_t	v);
/* �������ѹ��һ������ */
PLT_SERVICE (argument_int,				void,				plato_int_t			d);
/* �������ѹ��һ�������� */
PLT_SERVICE (argument_float,			void,				plato_float_t		d);
/* �������ѹ��һ���ַ��� */
PLT_SERVICE (argument_string,			void,				plato_string_t		d);

/* ���ú��� */
PLT_SERVICE (invoke,					plato_variable_t,	plato_callable_t	f);
/* ������ʵ������ */
PLT_SERVICE (invoke_instant,			plato_variable_t,	plato_variable_t	v,		plato_callable_t	m);
/* �����ྲ̬���� */
PLT_SERVICE (invoke_static,				plato_variable_t,	plato_callable_t	c,		plato_callable_t	m);

/* �����õĺ�������ֵ���浽�������� */
PLT_SERVICE (store,						void,				plato_variable_t	v);
/* �����õĺ�������ֵ��Ϊ�������� */
PLT_SERVICE (store_int,					plato_int_t);
/* �����õĺ�������ֵ��Ϊ���������� */
PLT_SERVICE (store_float,				plato_float_t);
/* �����õĺ�������ֵ��Ϊ�ַ������� */
PLT_SERVICE (store_string,				plato_string_t);


/* ������ʵ�� */
PLT_SERVICE (instant,					void,				plato_variable_t	v,		plato_callable_t	c);

/* ע�ắ�� */
PLT_SERVICE (register,					void,				plato_callable_t	f,		plato_pointer_t		addr,	plato_return_t		r);


/* �ڱ������д������� */
PLT_SERVICE (var,						void,				plato_variable_t	v);

/* �������д������ */
PLT_SERVICE (var_in_int,				void,				plato_variable_t	v,		plato_int_t			d);
/* �������д�븡���� */
PLT_SERVICE (var_in_float,				void,				plato_variable_t	v,		plato_float_t		d);
/* �������д���ַ��� */
PLT_SERVICE (var_in_string,				void,				plato_variable_t	v,		plato_string_t		d);

/* �ӱ����ж�ȡ���� */
PLT_SERVICE (var_out_int,				plato_int_t,		plato_variable_t	v);
/* �ӱ����ж�ȡ������ */
PLT_SERVICE (var_out_float,				plato_float_t,		plato_variable_t	v);
/* �ӱ����ж�ȡ�ַ��� */
PLT_SERVICE (var_out_string,			plato_string_t,		plato_variable_t	v);

/* �жϱ������� */
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

/* ��ȫ�ֱ���ӳ�䵽�������еı��� */
PLT_SERVICE (var_global,				void,				plato_variable_t	dst,	plato_string_t		src);

/* �������� */
PLT_SERVICE (var_copy,					void,				plato_variable_t	dst,	plato_variable_t	src);
/* �����õı������� */
PLT_SERVICE (var_bind,					void,				plato_variable_t	dst,	plato_variable_t	src);
/* �����õ������������ */
PLT_SERVICE (var_bind_array,			int,				plato_variable_t	dst,	plato_variable_t	src,	plato_variable_t	k);

/* �������Ƿ���� */
PLT_SERVICE (var_isset,					int,				plato_variable_t	v);
/* �������������Ƿ���� */
PLT_SERVICE (var_isset_array,			int,				plato_variable_t	v,		plato_variable_t	k);

/* ɾ������ */
PLT_SERVICE (var_unset,					void,				plato_variable_t	v);
/* ɾ����������� */
PLT_SERVICE (var_unset_array,			void,				plato_variable_t	v,		plato_variable_t	k);


/* �����ڴ�ҳ */
PLT_SERVICE (memory_page_allocate,		plato_pointer_t,	plato_size_t		size);
/* �ͷ��ڴ�ҳ */
PLT_SERVICE (memory_page_free,			void,				plato_pointer_t		p);
/* �޸�ҳ���� */
PLT_SERVICE (memory_page_flag,			plato_size_t,		plato_pointer_t		p,		plato_size_t		flag);

/* �ڶ��Ϸ����ڴ� */
PLT_SERVICE (memory_heap_allocate,		plato_pointer_t,	plato_size_t		size);
/* �ڶ����ط����ڴ� */
PLT_SERVICE (memory_heap_reallocate,	plato_pointer_t,	plato_pointer_t		p,		plato_size_t		size);
/* �ڶ����ͷ��ڴ� */
PLT_SERVICE (memory_heap_free,			void,				plato_pointer_t		p);
/* ���Ӷ��Ϸ�����ڴ���ջ��ʼ�� */
PLT_SERVICE (memory_heap_zero,			void,				plato_pointer_t		p);


/* PHP ���������Խṹ */
PLT_SERVICE (php_exit,					void,				plato_string_t		s);
PLT_SERVICE (php_echo,					void,				plato_string_t		s);
PLT_SERVICE (php_eval,					void,				plato_string_t		s);
PLT_SERVICE (php_include,				int,				plato_string_t		f);
PLT_SERVICE (php_include_once,			int,				plato_string_t		f);
PLT_SERVICE (php_function_exists,		int,				plato_string_t		f);


/* �жϳ����Ƿ������� plato ģ������ */
PLT_SERVICE (plato_mode,				int);
/* ��ȡ plato ģ�����汾 */
PLT_SERVICE (plato_version,				int);
/* plato_include */
PLT_SERVICE (plato_include,				plato_variable_t,	plato_string_t		f,		plato_return_t		r);


#if defined(__cplusplus)
	}
#endif

#endif   /* _PLATO_SERVICE_H_ */