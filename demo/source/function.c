/*
	function.c

	������ʾ����ε��� PHP �еĺ���

	ͨ��ʹ�� P_CALL/P_CALL_INSTANT/P_CALL_STATIC, ���Ե��� PHP �����еĺ�����ʵ����ķ����;�̬��ķ���
	�� function.h �з�װ��һЩ���õĺ���
*/

#include <stdio.h>
#include "../plato/plato.h"


/* C ��� */
int main() {
	printf("��������� Plato ģ�������������С�");

	return 0;
}


/* main */
int PLT_CALL plato_main(int argc, void *args[]) {
	/* ֱ�ӵ����Ѿ���װ�õĺ��� */
	p_echo("���� echo<br />");

	/* ���� str_replace */
	p_str_replace("src", "dst", "my-src-dst");

	/* ���һ�ε��ú�����, ������ P_RET_* ������ֵ */
	/* P_RET_STRING ����ֱ�Ӱѷ���ֵ��Ϊһ���ַ���ʹ�� */
	p_echo(P_RET_STRING);
	/* ���� P_RET() ���Խ�����ֵѹ������� */
	P_RET(my_var);

	/* ʹ�� P_CALL ���� printf
	   ��������൱�ڵ����� printf */
	P_CALL (
		/* ��һ�������Ǻ����� */
		"printf",
		/*
			�����ǲ���
			����ʹ�� P_ARG ���������е�һ��������Ϊ����ѹ��
			����ʹ�� P_ARG_STRING ѹ��һ���ַ�����Ϊ����
			����ʹ�� P_ARG_INT ѹ��һ������ (Ҳ������Ϊ bool ����) ��Ϊ����
		*/
		P_ARG_STRING	("<br />���� printf(): %08X, %s"),
		P_ARG_INT		(0x1234ABCD),
		P_ARG			(my_var)
	);

	return 0;
}
