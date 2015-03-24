/*
	function.c

	这里演示了如何调用 PHP 中的函数

	通过使用 P_CALL/P_CALL_INSTANT/P_CALL_STATIC, 可以调用 PHP 环境中的函数、实例类的方法和静态类的方法
	在 function.h 中封装了一些常用的函数
*/

#include <stdio.h>
#include "../plato/plato.h"


/* C 入口 */
int main() {
	printf("程序必须在 Plato 模拟器环境中运行。");

	return 0;
}


/* main */
int PLT_CALL plato_main(int argc, void *args[]) {
	/* 直接调用已经封装好的函数 */
	p_echo("调用 echo<br />");

	/* 调用 str_replace */
	p_str_replace("src", "dst", "my-src-dst");

	/* 最后一次调用函数后, 可以用 P_RET_* 处理返回值 */
	/* P_RET_STRING 代表直接把返回值作为一个字符串使用 */
	p_echo(P_RET_STRING);
	/* 又如 P_RET() 可以将返回值压入变量池 */
	P_RET(my_var);

	/* 使用 P_CALL 调用 printf
	   下面代码相当于调用了 printf */
	P_CALL (
		/* 第一个参数是函数名 */
		"printf",
		/*
			后面是参数
			可以使用 P_ARG 将变量池中的一个变量作为参数压入
			可以使用 P_ARG_STRING 压入一个字符串作为参数
			可以使用 P_ARG_INT 压入一个整数 (也可以作为 bool 类型) 作为参数
		*/
		P_ARG_STRING	("<br />这是 printf(): %08X, %s"),
		P_ARG_INT		(0x1234ABCD),
		P_ARG			(my_var)
	);

	return 0;
}
