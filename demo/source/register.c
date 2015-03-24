/*
	register.c

	这里演示如何向 PHP 中注册一个函数

	例如, 当向 PHP 注册了一个叫做 test 的函数后, PHP 里即可使用 test() 调用执行该函数
	函数可以使用零个、多个或不定个参数
*/

#include <stdio.h>
#include "../plato/plato.h"


/* C 入口 */
int main() {
	printf("程序必须在 Plato 模拟器环境中运行。");

	return 0;
}


/* 这是一个没有参数的函数例子 */
int test_with_zero_argument() {
	return 0xAB;
}

/*
	这是一个需要两个参数的函数例子
	在 php 中可直接调用 echo test_with_two_argument(-5, 2);
*/
int test_with_two_argument(int a, int b) {
	/* 加法计算 */
	return a + b;
}

/*
	这是一个不定参数的函数例子, 第一个参数表示共有多少个函数 (不含第一个参数)

	在 php 可直接调用 echo test_with_variable_argument(3, "abc", "xxxxx", "defg");
	以此可以输出最长的字符串, 其中第一个参数是字符串的数目
*/
char * test_with_variable_argument(int count, ...) {
	char *str = NULL;
	char *tmp;

	unsigned int len = 0;
	unsigned int i;

	/* 指向 count, 即第一个参数 */
	char **p = (char **) &count;

	if(count == 0) {
		return NULL;
	}

	/* 判断并返回最长的字符串 */
	while(count--) {
		/* 清空长度 */
		i	= 0;
		/* 增加 p */
		p	= p + 1;

		/* 指向下一个参数 */
		tmp	= *p;

		/* 使用 try-catch-final, 防止内存非法访问, 具体用法请参阅 try_catch_final.c */
		P_TRY(my_ex) {
			while(*(tmp++)) {
				i++;
			}
		}
		/* 非法访问 */
		P_CATCH(my_ex) {
			return str;
		}
		P_FINAL(my_ex);

		/* 比较两次的长度 */
		if((len == 0) || (len < i)) {
			len = i;
			str = *p;
		}
	}

	return str;
}


/* main */
int PLT_CALL plato_main(int argc, void *args[]) {
	/* 注册函数 test_with_zero_argument(), 返回类型是 PLATO_TYPE_INTEGER (0) */
	__plato_register("test_with_zero_argument",		&test_with_zero_argument,		/* PLATO_TYPE_INTEGER=0 */	0);

	/* 注册 test_with_two_argument() */
	__plato_register("test_with_two_argument",		&test_with_two_argument,		/* PLATO_TYPE_INTEGER=0 */	0);

	/* 注册 test_with_variable_argument, 返回类型是 PLATO_TYPE_STRING (4) */
	__plato_register("test_with_variable_argument",	&test_with_variable_argument,	/* PLATO_TYPE_STRING=4 */	4);

	/*
		__plato_register 需要 3 个参数

		第一个参数是函数名, 在 PHP 中调用此函数时使用这个名称
		第二个参数是函数入口地址
		第三个函数是返回类型, 具体值在 int.const.php 中定义, 可选用 PLATO_TYPE_* 常量
	*/

	return 0;
}
