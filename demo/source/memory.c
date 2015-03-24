/*
	memory.c

	这里演示如何申请、管理和使用动态内存

	与普通的程序类似, 除了使用栈之外, 程序在运行中还可以从堆上分配所需要的动态内存
	Plato 模拟器提供了申请、重申请和释放内存的函数, 对应 C 运行库中的 m(c)alloc/realloc/free 函数
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
	int i;

	/* 申请内存 */
	char *p = (char *) P_MALLOC(sizeof(char) * 20);
	// char *p = (char *) P_CALLOC(20, sizeof(char))

	/* 写入数据 */
	for(i = 0; i < 10; i++) {
		p[i] = "TEST TEXT"[i];
	}

	/* 输出数据 */
	p_echo(p);
	p_echo("<br />");

	/* 重新分配 */
	p = (char *) P_REALLOC(p, 1024);
	/* 再输出 */
	p_echo(p);

	/* 程序执行结束后会自动释放内存 */
	// P_FREE(p);

	return (int) p;
}
