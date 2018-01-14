使用notepad++打开以下文件

1、修改你根目录下的
Application/Common/Conf/config.ini.php
Administrator/Common/Conf/config.ini.php

大概56行，按提示修改，示例如下：
NODE_SITE_URL 		= 'http://shop.shopda.cn:33'; //如果要启用IM，把 http://shop.shopda.cn 修改为您的服务器IP

大概114行，按提示修改，示例如下：
NODE_CHAT = false;//如果要启用IM，把false修改为true

3、从官网（http://nodejs.org/download/）下载二进制包。
打开命令输入以下命令，不要复制#：
#wget http://nodejs.org/dist/v0.10.28/node-v0.10.28-linux-x64.tar.gz
#tar zxf node-v0.10.28-linux-x64.tar.gz
#mv node-v0.10.28-linux-x64 /usr/local/node

4、将im目录下的所有内容(不含im目录本身)覆盖到/usr/local/node下，
然后编辑/usr/local/node/config.js文件，配置示例如下：


var config = {};//数据库帐号设置

config['host']         = 'localhost';//数据库地址
config['port']         = '3306';//数据库端口
config['user']         = 'root';//数据库用户名
config['password']     = 'root';//数据库密码
config['database']     = 'shopda';//mysql数据库名
config['tablepre']     = 'shopda_';//表前缀
config['insecureAuth'] = true;//兼容低版本
config['debug']        = false;//默认false

exports.hostname = 'www.baidu.com';//把网址修改为你安装商城的域名，不要带http://及/
exports.port = 33;//服务器所用端口号,默认33
exports.config = config;

5、启动node进程，命令：
#/usr/local/node/bin/node /usr/local/node/chat.js

如果出现以下内容说明配置成功：

    info  - socket.io started
    mysql connected

表示安装成功（如果不成功，请检查你的防火墙是否添加有33端口，如果不行。换个端口试下），成功后，按Ctrl+c，停止当前node，将node以后台方式启动,输入命令：
#nohup /usr/local/node/bin/node /usr/local/node/chat.js >> /usr/local/node/output.log 2>&1 &

如果你的IM客服图标不出来了，请再输入命令:
#nohup /usr/local/node/bin/node /usr/local/node/chat.js >> /usr/local/node/output.log 2>&1 &