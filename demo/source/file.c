/*
	file.c

	这里继续演示如何调用 PHP 中的函数

	这里调用的函数都是 function.h 中已经封装了的文件函数
	更具体的调用方法, 请参照 function.c 文件
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
	char *file = "test.txt";

	/* 调用 file_exists */
	p_file_exists(file);

	/* 将最后调用函数的返回值作为整数使用, 判断文件是否存在 */
	if(P_RET_INT) {
		p_echo("文件已经存在。");

		return 0;
	}

	/* 打开文件 */
	p_fopen(file, "wb");
	/* 将句柄保存到变量池中 */
	P_RET(fp);

	/* 写入内容 */
	p_fwrite_str(fp, "我是内容\n我还是内容");
	/* 关闭句柄 */
	p_fclose(fp);

	/* 最后输出 */
	p_echo("文件创建成功。");

	return 0;
}
