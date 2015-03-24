/*
	file.c

	���������ʾ��ε��� PHP �еĺ���

	������õĺ������� function.h ���Ѿ���װ�˵��ļ�����
	������ĵ��÷���, ����� function.c �ļ�
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
	char *file = "test.txt";

	/* ���� file_exists */
	p_file_exists(file);

	/* �������ú����ķ���ֵ��Ϊ����ʹ��, �ж��ļ��Ƿ���� */
	if(P_RET_INT) {
		p_echo("�ļ��Ѿ����ڡ�");

		return 0;
	}

	/* ���ļ� */
	p_fopen(file, "wb");
	/* ��������浽�������� */
	P_RET(fp);

	/* д������ */
	p_fwrite_str(fp, "��������\n�һ�������");
	/* �رվ�� */
	p_fclose(fp);

	/* ������ */
	p_echo("�ļ������ɹ���");

	return 0;
}
