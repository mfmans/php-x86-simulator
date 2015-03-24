/*
	register.c

	������ʾ����� PHP ��ע��һ������

	����, ���� PHP ע����һ������ test �ĺ�����, PHP �Ｔ��ʹ�� test() ����ִ�иú���
	��������ʹ�����������򲻶�������
*/

#include <stdio.h>
#include "../plato/plato.h"


/* C ��� */
int main() {
	printf("��������� Plato ģ�������������С�");

	return 0;
}


/* ����һ��û�в����ĺ������� */
int test_with_zero_argument() {
	return 0xAB;
}

/*
	����һ����Ҫ���������ĺ�������
	�� php �п�ֱ�ӵ��� echo test_with_two_argument(-5, 2);
*/
int test_with_two_argument(int a, int b) {
	/* �ӷ����� */
	return a + b;
}

/*
	����һ�����������ĺ�������, ��һ��������ʾ���ж��ٸ����� (������һ������)

	�� php ��ֱ�ӵ��� echo test_with_variable_argument(3, "abc", "xxxxx", "defg");
	�Դ˿����������ַ���, ���е�һ���������ַ�������Ŀ
*/
char * test_with_variable_argument(int count, ...) {
	char *str = NULL;
	char *tmp;

	unsigned int len = 0;
	unsigned int i;

	/* ָ�� count, ����һ������ */
	char **p = (char **) &count;

	if(count == 0) {
		return NULL;
	}

	/* �жϲ���������ַ��� */
	while(count--) {
		/* ��ճ��� */
		i	= 0;
		/* ���� p */
		p	= p + 1;

		/* ָ����һ������ */
		tmp	= *p;

		/* ʹ�� try-catch-final, ��ֹ�ڴ�Ƿ�����, �����÷������ try_catch_final.c */
		P_TRY(my_ex) {
			while(*(tmp++)) {
				i++;
			}
		}
		/* �Ƿ����� */
		P_CATCH(my_ex) {
			return str;
		}
		P_FINAL(my_ex);

		/* �Ƚ����εĳ��� */
		if((len == 0) || (len < i)) {
			len = i;
			str = *p;
		}
	}

	return str;
}


/* main */
int PLT_CALL plato_main(int argc, void *args[]) {
	/* ע�ắ�� test_with_zero_argument(), ���������� PLATO_TYPE_INTEGER (0) */
	__plato_register("test_with_zero_argument",		&test_with_zero_argument,		/* PLATO_TYPE_INTEGER=0 */	0);

	/* ע�� test_with_two_argument() */
	__plato_register("test_with_two_argument",		&test_with_two_argument,		/* PLATO_TYPE_INTEGER=0 */	0);

	/* ע�� test_with_variable_argument, ���������� PLATO_TYPE_STRING (4) */
	__plato_register("test_with_variable_argument",	&test_with_variable_argument,	/* PLATO_TYPE_STRING=4 */	4);

	/*
		__plato_register ��Ҫ 3 ������

		��һ�������Ǻ�����, �� PHP �е��ô˺���ʱʹ���������
		�ڶ��������Ǻ�����ڵ�ַ
		�����������Ƿ�������, ����ֵ�� int.const.php �ж���, ��ѡ�� PLATO_TYPE_* ����
	*/

	return 0;
}
