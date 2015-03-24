/*
	try_catch_final.c

	������ʾ�� Plato �ṩ�� try-catch-final �쳣����ṹ

	Plato �ṩ���쳣����ṹ��һ����ջά��, ÿһ��ʹ�� try �ṹ�Դ�������й�ʱ, ��ѹ�� catch �ṹ�д������
	��, ���쳣����ʱ, EIP �Ĵ�����ֵ���ᱻ�޸�ָ�� catch �ṹ, ��ʼִ���쳣������, ���, ���ص� final �ṹ
	����ִ�г���
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
	/* ���ڱ����쳣���� */
	int id;

	/* id_1 ��һ����ǩ, ��ͬһ�������б�ǩ�����ظ�ʹ�� */
	P_TRY (id_1) {
		/* �Ƿ�д���ڴ�, �������쳣�׳� */
		__asm {
			xor		eax, eax
			mov		[eax], eax
		}
	}
	/* ͬ���� try-catch-final �ṹ����ʹ����ͬ�ı�ǩ */
	P_CATCH (id_1) {
		/* ���һ����ʾ */
		p_echo("id_1 �쳣<br />");

		/* �˴����Խ���Ƕ�� */
		P_TRY (id_2) {
			/* �����쳣 */
			__asm {
				xor		eax, eax
				div		eax
			}
		}
		/* ʹ�� P_CATCH_V �ṹ���Ի���쳣���� (������ exception.h ��) */
		P_CATCH_V (id_2, id) {
			/* printf */
			P_CALL (
				"printf",
				P_ARG_STRING	("id_2 �쳣, ����Ϊ %08X<br />"),
				P_ARG_INT		(id)
			);
		}
		P_FINAL (id_2) {
			p_echo("id_2 �������<br />");
		}
	}
	/* final �ṹ���Բ���������, ֱ��ʹ�÷ֺŽ��� */
	P_FINAL (id_1);

	p_echo("<br />�˳��������쳣���� ...");

	return 0;
}
