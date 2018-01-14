var config = {};//数据库帐号设置

config['host']         = 'localhost';//数据库地址
config['port']         = '3306';//数据库端口
config['user']         = 'root';//数据库用户名
config['password']     = 'admin888';//数据库密码
config['database']     = 'v5_5_shopnc';//mysql数据库名
config['tablepre']     = 'shopda_';//表前缀
config['insecureAuth'] = true;//兼容低版本
config['debug']        = false;//默认false

exports.hostname = 'shop.shopda.cn';//把网址修改为你安装商城的域名，不要带http://及/
exports.port = 33;//服务器所用端口号,默认33
exports.config = config;
