/*
	$ Plato x86 Simulator   (C) 2005-2013 MF
	$ function.h   #D3
*/

#ifndef _PLATO_PACK_FUNCTION_H_
#define _PLATO_PACK_FUNCTION_H_


/* error_reporting(level) */
#define p_error_reporting(level)		P_CALL (						\
											"error_reporting",			\
											P_ARG_INT		(level)		\
										);


/* print_r(v) */
#define p_print_r(v)					P_CALL (						\
											"print_r",					\
											P_ARG			(v)			\
										);

/* print_r(v, true) */
#define p_print_r_return(v)				P_CALL (						\
											"print_r",					\
											P_ARG			(v),		\
											P_ARG_INT		(1)			\
										);

/* var_dump(v) */
#define p_var_dump(v)					P_CALL (						\
											"var_dump",					\
											P_ARG			(v)			\
										);


/* explode(str) */
#define p_explode(str)					P_CALL (						\
											"explode",					\
											P_ARG_STRING	(str)		\
										);

/* implode(v) */
#define p_implode						P_CALL (						\
											"implode",					\
											P_ARG			(v)			\
										);


/* substr(str, start, end) */
#define p_substr(str, start, end)		P_CALL (						\
											"substr",					\
											P_ARG_STRING	(str),		\
											P_ARG_INT		(start),	\
											P_ARG_INT		(end)		\
										);

/* str_replace(search, replace, str) */
#define p_str_replace(srch, rplc, str)	P_CALL (						\
											"str_replace",				\
											P_ARG_STRING	(srch),		\
											P_ARG_STRING	(rplc),		\
											P_ARG_STRING	(str)		\
										);


/* preg_match(str, expr) */
#define p_preg_match(str, expr)			P_CALL (						\
											"preg_match",				\
											P_ARG_STRING	(str),		\
											P_ARG_STRING	(expr)		\
										);

/* preg_replace(exp, replace, str) */
#define p_preg_replace(exp, rplc, str)	P_CALL (						\
											"preg_replace",				\
											P_ARG_STRING	(exp),		\
											P_ARG_STRING	(rplc),		\
											P_ARG_STRING	(str)		\
										);


/* iconv(in, out, str) */
#define p_iconv(in, out, str)			P_CALL (						\
											"iconv",					\
											P_ARG_STRING	(in),		\
											P_ARG_STRING	(out),		\
											P_ARG_STRING	(str)		\
										);


/* fopen(file, mode) */
#define p_fopen(file, mode)				P_CALL (						\
											"fopen",					\
											P_ARG_STRING	(file),		\
											P_ARG_STRING	(mode)		\
										);

/* fread(v_fp, size) */
#define p_fread(v, size)				P_CALL (						\
											"fread",					\
											P_ARG			(v),		\
											P_ARG_INT		(size)		\
										);

/* fwrite(v_fp, data) */
#define p_fwrite(v, data)				P_CALL (						\
											"fwrite",					\
											P_ARG			(v),		\
											P_ARG			(data)		\
										);
/* fwrite(v_fp, str) */
#define p_fwrite_str(v, str)			P_CALL (						\
											"fwrite",					\
											P_ARG			(v),		\
											P_ARG_STRING	(str)		\
										);

/* fseek(v_fp, pos, SEEK_SET) */
#define p_fseek(v, pos)					P_CALL (						\
											"fseek",					\
											P_ARG			(v),		\
											P_ARG_INT		(pos)		\
										);
/* fseek(v_fp, pos, SEEK_CUR */
#define p_fseek_cur(v, pos)				P_CALL (						\
											"fseek",					\
											P_ARG			(v),		\
											P_ARG_INT		(pos),		\
											P_ARG_INT		(1)			\
										);

/* fclose(v_fp) */
#define p_fclose(v)						P_CALL (						\
											"fclose",					\
											P_ARG			(v)			\
										);

/* file_exists(file) */
#define p_file_exists(file)				P_CALL (						\
											"file_exists",				\
											P_ARG_STRING	(file)		\
										);

/* unlink(file) */
#define p_unlink(file)					P_CALL (						\
											"unlink",					\
											P_ARG_STRING	(file)		\
										);


#endif   /* _PLATO_PACK_FUNCTION_H_ */