ʹ��notepad++�������ļ�

1���޸����Ŀ¼�µ�
Application/Common/Conf/config.ini.php
Administrator/Common/Conf/config.ini.php

���56�У�����ʾ�޸ģ�ʾ�����£�
NODE_SITE_URL 		= 'http://shop.shopda.cn:33'; //���Ҫ����IM���� http://shop.shopda.cn �޸�Ϊ���ķ�����IP

���114�У�����ʾ�޸ģ�ʾ�����£�
NODE_CHAT = false;//���Ҫ����IM����false�޸�Ϊtrue

3���ӹ�����http://nodejs.org/download/�����ض����ư���
�������������������Ҫ����#��
#wget http://nodejs.org/dist/v0.10.28/node-v0.10.28-linux-x64.tar.gz
#tar zxf node-v0.10.28-linux-x64.tar.gz
#mv node-v0.10.28-linux-x64 /usr/local/node

4����imĿ¼�µ���������(����imĿ¼����)���ǵ�/usr/local/node�£�
Ȼ��༭/usr/local/node/config.js�ļ�������ʾ�����£�


var config = {};//���ݿ��ʺ�����

config['host']         = 'localhost';//���ݿ��ַ
config['port']         = '3306';//���ݿ�˿�
config['user']         = 'root';//���ݿ��û���
config['password']     = 'root';//���ݿ�����
config['database']     = 'shopda';//mysql���ݿ���
config['tablepre']     = 'shopda_';//��ǰ׺
config['insecureAuth'] = true;//���ݵͰ汾
config['debug']        = false;//Ĭ��false

exports.hostname = 'www.baidu.com';//����ַ�޸�Ϊ�㰲װ�̳ǵ���������Ҫ��http://��/
exports.port = 33;//���������ö˿ں�,Ĭ��33
exports.config = config;

5������node���̣����
#/usr/local/node/bin/node /usr/local/node/chat.js

���������������˵�����óɹ���

    info  - socket.io started
    mysql connected

��ʾ��װ�ɹ���������ɹ���������ķ���ǽ�Ƿ������33�˿ڣ�������С������˿����£����ɹ��󣬰�Ctrl+c��ֹͣ��ǰnode����node�Ժ�̨��ʽ����,�������
#nohup /usr/local/node/bin/node /usr/local/node/chat.js >> /usr/local/node/output.log 2>&1 &

������IM�ͷ�ͼ�겻�����ˣ�������������:
#nohup /usr/local/node/bin/node /usr/local/node/chat.js >> /usr/local/node/output.log 2>&1 &