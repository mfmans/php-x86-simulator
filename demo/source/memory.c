/*
	memory.c

	������ʾ������롢�����ʹ�ö�̬�ڴ�

	����ͨ�ĳ�������, ����ʹ��ջ֮��, �����������л����ԴӶ��Ϸ�������Ҫ�Ķ�̬�ڴ�
	Plato ģ�����ṩ�����롢��������ͷ��ڴ�ĺ���, ��Ӧ C ���п��е� m(c)alloc/realloc/free ����
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
	int i;

	/* �����ڴ� */
	char *p = (char *) P_MALLOC(sizeof(char) * 20);
	// char *p = (char *) P_CALLOC(20, sizeof(char))

	/* д������ */
	for(i = 0; i < 10; i++) {
		p[i] = "TEST TEXT"[i];
	}

	/* ������� */
	p_echo(p);
	p_echo("<br />");

	/* ���·��� */
	p = (char *) P_REALLOC(p, 1024);
	/* ����� */
	p_echo(p);

	/* ����ִ�н�������Զ��ͷ��ڴ� */
	// P_FREE(p);

	return (int) p;
}
