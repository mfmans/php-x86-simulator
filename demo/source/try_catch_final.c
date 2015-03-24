/*
	try_catch_final.c

	这里演示了 Plato 提供的 try-catch-final 异常处理结构

	Plato 提供的异常处理结构由一个堆栈维持, 每一次使用 try 结构对代码进行托管时, 将压入 catch 结构中代码的入
	口, 当异常发生时, EIP 寄存器的值将会被修改指向 catch 结构, 开始执行异常处理器, 最后, 返回到 final 结构
	继续执行程序
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
	/* 用于保存异常代号 */
	int id;

	/* id_1 是一个标签, 在同一个函数中标签不能重复使用 */
	P_TRY (id_1) {
		/* 非法写入内存, 将导致异常抛出 */
		__asm {
			xor		eax, eax
			mov		[eax], eax
		}
	}
	/* 同级的 try-catch-final 结构必须使用相同的标签 */
	P_CATCH (id_1) {
		/* 输出一个提示 */
		p_echo("id_1 异常<br />");

		/* 此处可以进行嵌套 */
		P_TRY (id_2) {
			/* 除零异常 */
			__asm {
				xor		eax, eax
				div		eax
			}
		}
		/* 使用 P_CATCH_V 结构可以获得异常代号 (定义在 exception.h 中) */
		P_CATCH_V (id_2, id) {
			/* printf */
			P_CALL (
				"printf",
				P_ARG_STRING	("id_2 异常, 代号为 %08X<br />"),
				P_ARG_INT		(id)
			);
		}
		P_FINAL (id_2) {
			p_echo("id_2 处理完毕<br />");
		}
	}
	/* final 结构可以不包含代码, 直接使用分号结束 */
	P_FINAL (id_1);

	p_echo("<br />退出了所有异常处理 ...");

	return 0;
}
