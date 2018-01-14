<?php
/**
 * 系统后台公共方法
 *
 * 包括系统后台父类
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Home\Controller;
use Think\Controller;
use Common\Lib\Language;
use Common\Lib\Cache;
use Common\Lib\Log;
use Common\Lib\Model;



class SystemController extends AdminController {

    /**
     * 管理员资料 name id group
     */
    protected $admin_info;

    /**
     * 权限内容
     */
    protected $permission;

    /**
     * 菜单
     */
    protected $menu;

    /**
     * 常用菜单
     */
    protected $quick_link;
    public function __construct() {
    	parent::__construct();
    	
        Language::read('common,layout', 'Home');
        /**
         * 验证用户是否登录
         * $admin_info 管理员资料 name id
         */
        $this->admin_info = $this->systemLogin();
        if ($this->admin_info['id'] != 1){
            // 验证权限
            $this->checkPermission();
        }
        //转码  防止GBK下用ajax调用时传汉字数据出现乱码
        if (($_GET['branch']!='' || $_GET['a']=='ajax') && strtoupper(CHARSET) == 'GBK'){
            $_GET = Language::getGBK($_GET);
        }
    }

    /**
     * 取得当前管理员信息
     *
     * @param
     * @return 数组类型的返回结果
     */
    protected final function getAdminInfo() {
        return $this->admin_info;
    }

    /**
     * 系统后台登录验证
     *
     * @param
     * @return array 数组类型的返回结果
     */
    protected final function systemLogin() {
        //取得cookie内容，解密，和系统匹配
        $user = unserialize(decrypt(cookie('sys_key'),MD5_KEY));
        if (!key_exists('gid',(array)$user) || !isset($user['sp']) || (empty($user['name']) || empty($user['id']))){
            @header('Location: '. ADMIN_SITE_URL .'?m=Home&c=Login&a=login');exit;
        }else {
            $this->systemSetKey($user);
        }
        return $user;
    }

    /**
     * 系统后台 会员登录后 将会员验证内容写入对应cookie中
     *
     * @param string $name 用户名
     * @param int $id 用户ID
     * @return bool 布尔类型的返回结果
     */
    protected final function systemSetKey($user, $avatar = '', $avatar_compel = false) {
        setDaCookie('sys_key',encrypt(serialize($user),MD5_KEY),3600,'',null);
        if ($avatar_compel || $avatar != '') {
            setDaCookie('admin_avatar',$avatar,86400 * 365,'',null);
        }
    }

    /**
     * 验证当前管理员权限是否可以进行操作
     *
     * @param string $link_nav
     * @return
     */
    protected final function checkPermission($link_nav = null) {
        if ($this->admin_info['sp'] == 1) return true;

        $c = $_GET['c'] ? $_GET['c'] : $_POST['c'];
        $c = $c == '' ? 'index' : $c;
        
        $permission_list = $this->getPermission();

		
		$permission = array();
		foreach ($permission_list as $key => $grp_list)
		{
			foreach ($grp_list as $item)
			{
				$item_arr = explode("|", $item);
				$m = $item_arr[0];
				$c = $item_arr[1];
				$a = isset($item_arr[2]) ? $item_arr[2] : 'index';
				$permission[] = $c;
			}
		}
        
        if (in_array(MODULE_NAME, array("Home"))) return true;//modules目录外的不需要验证
        
        
        
        if (in_array($c, $permission)){
            return true;
        }
        showMessage(Language::get('spd_assign_right'),'','html','succ',0);
    }

    /**
     * 取得后台菜单的Html形式
     *
     * @param string $permission
     * @return
     */
    protected final function getNav() {
        $_menu = $this->getMenu();

        $menu_group = $this->convertMenuIntoGroup($_menu);
        $this->assign("menu_group", json_encode($menu_group));
        
        $_menu = $this->parseMenu($_menu);
        $quicklink = $this->getQuickLink();

        $top_nav = '';
        $left_nav = '';
        $map_nav = '';
        $map_top = '';
        $quick_array = array();
        foreach ($_menu as $key => $value) {
            $top_nav .= '<li data-param="' . $key . 
              '"><a href="javascript:void(0);">' . 
            $value['name'] . '</a></li>';
            
            $left_nav .= '<div id="admincpNavTabs_'. $key .
            '" class="nav-tabs">';
            
            $map_top .= '<li><a href="javascript:void(0);" data-param="map-' . $key . 
            '">' . $value['name'] . '</a></li>';
            $map_nav .= '<div class="admincp-map-div" data-param="map-' . $key . '">';
            
            foreach ($value['child'] as $ke => $val) {
                if (!empty($val['child'])) {
                    $left_nav .= '<dl><dt><a href="javascript:void(0);"><span class="ico-' . $key .
                     '-' . $ke . '"></span><h3>' . $val['name'] . '</h3></a></dt>';
                    $left_nav .= '<dd class="sub-menu"><ul>';
                    $map_nav .= '<dl><dt>' . $val['name'] . '</dt>';
                    
                    $temp_index = 0;
                    foreach ($val['child'] as $k => $v) {
                    	
                    	if ($temp_index % 2 == 0)
                    	{
                    		$is_even = 1;
                    	}
                    	else
                    	{
                    		$is_even = 0;
                    	}
                    	$index_str = " index-id='" . $temp_index  . "' ";
                    	$index_str .= " index-even='" . $is_even . "' ";
                    	
                    	$grp_key_str = " root-nav-key='" . $key . "' ";
                    	
                    	$k_arr = explode("|", $k);
                    	$m = $k_arr[0];
                    	$c = $k_arr[1];
                    	if (isset($k_arr[2])) {
                    		$a = $k_arr[2];
                    	} else {
                    		$a = 'index';
                    	}
                        $left_nav .= '<li ' . $grp_key_str . 
                          $index_str . 
                          '><a ' . 
                          $grp_key_str . 
                          ' href="javascript:void(0);" data-param="' . 
                        $m . '|' . $c . '">' . $v . '</a></li>';
                        
                        $selected = '';
                        if (in_array($m . '|' . $c , $quicklink)) {
                            $selected = 'selected';
                            $quick_array[$m . '|' . $c] = array("root-nav-key" => $key , "value" => $v);
                        }
                        $map_nav .= '<dd ' . $grp_key_str . ' class="' . $selected . 
                        '"><a ' . $grp_key_str . ' href="javascript:void(0);" data-param="' . $m . 
                        '|' . $c . '">' . $v . '</a><i class="fa fa-check-square-o"></i></dd>';
                    	
                        $temp_index += 1;
                    }
                    
                    $left_nav .= '</ul></dd></dl> <dd class="nav-clear-dd"></dd>';
                    $map_nav .= '</dl>';
                }
                
            }
            $left_nav .= '</div>';
            $map_nav .= '</dl></div>';
        }
        $map_nav = '<ul class="admincp-map-nav">'.$map_top.'</ul>'.$map_nav;
        return array($top_nav, $left_nav, $map_nav, $quick_array);
    }
    
    //MENU分组
    private final function convertMenuIntoGroup($menu)
    {
    	foreach ($menu as $group_key => $group_val)
    	{
    		foreach ($group_val['child'] as $sub_menu)
    		{
    			foreach ($sub_menu['child'] as $menu_key => $menu_name)
    			{
    				$ret[$menu_key] = $group_key;
    			}
    		}
    	}
    	return $ret;
    }
    

    /**
     * 过滤掉无权查看的菜单
     *
     * @param array $menu
     * @return array
     */
    private final function parseMenu($menu) {
        if ($this->admin_info['sp'] == 1) return $menu;
        $permission = $this->getPermission();
        foreach ($menu as $key=>$value) {
            if (!isset($permission[$key])) {
                unset($menu[$key]);
                continue;
            }
            foreach ($value['child'] as $ke=>$val) {
                foreach ($val['child'] as $k=>$v) {
                    if (!in_array($k, $permission[$key])) {
                        unset($menu[$key]['child'][$ke]['child'][$k]);
                    }
                }
            }
        }
        return $menu;
    }

    /**
     * 获取权限内容
     *
     */
    private final function getPermission() {
        if (empty($this->permission)) {
            $gadmin = Model('gadmin')->getby_gid($this->admin_info['gid']);
            $permission = decrypt($gadmin['limits'],MD5_KEY.md5($gadmin['gname']));
            $this->permission = unserialize($permission);
        }
        return $this->permission;
    }

    /**
     * 获取菜单
     */
    protected final function getMenu() {
        if (empty($this->menu)) {
            $this->menu  = rkcache('admin_menu', true);
        }
        return $this->menu;
    }

    /**
     * 获取快捷操作
     */
    protected final function getQuickLink() {
        if ($this->admin_info['qlink'] != '') {
            return explode(',', $this->admin_info['qlink']);
        } else {
            return array();
        }
    }

    /**
     * 取得顶部小导航
     *
     * @param array $links
     * @param 当前页 $actived
     */
    protected final function sublink($links = array(), $actived = '', $file='') {
        if (empty($file)) {
			$file = $GLOBALS['_PAGE_URL'];
		}

		$linkstr = '';
        foreach ($links as $k=>$v) {
            parse_str($v['url'],$array);
            if (empty($array['a'])) $array['a'] = 'index';
            if (!$this->checkPermission($array)) continue;
            
            //判断是否需要使用外部传入的url
            $url_parser_ret = parse_url($v['url']);
            if(isset($url_parser_ret['host']) && (!empty($url_parser_ret['host'])))
            {
            	$href = ($array['a'] == $actived ? null : "href=\"{$v['url']}\"");
            }
            else
            {
                $href = ($array['a'] == $actived ? null : "href=\"{$file}&{$v['url']}\"");
            }
            
            $class = ($array['a'] == $actived ? "class=\"current\"" : null);
            $lang = $v['text'] ? $v['text'] : L($v['lang']);
            $linkstr .= sprintf('<li><a %s %s><span>%s</span></a></li>',$href,$class,$lang);
        }
        $ret = "<ul class=\"tab-base spd-row\">{$linkstr}</ul>";
        return $ret;
    }

    /**
     * 记录系统日志
     *
     * @param $lang 日志语言包
     * @param $state 1成功0失败null不出现成功失败提示
     * @param $admin_name
     * @param $admin_id
     */
    protected final function log($lang = '', $state = 1, $admin_name = '', $admin_id = 0) {
        if (!C('sys_log') || !is_string($lang)) return;
        if ($admin_name == ''){
            $admin = unserialize(decrypt(cookie('sys_key'),MD5_KEY));
            $admin_name = $admin['name'];
            $admin_id = $admin['id'];
        }
        $data = array();
        if (is_null($state)){
            $state = null;
        }else{
            $state = $state ? '' : L('spd_fail');
        }
        $data['content']    = $lang.$state;
        $data['admin_name'] = $admin_name;
        $data['createtime'] = TIMESTAMP;
        $data['admin_id']   = $admin_id;
        $data['ip']         = getIp();
        $data['url']        = $_REQUEST['c'].'&'.$_REQUEST['a'];
        return Model('admin_log')->insert($data);
    }

    /**
     * 输出JSON
     *
     * @param string $errorMessage 错误信息 为空则表示成功
     */
    protected function jsonOutput($errorMessage = false)
    {
        $data = array();

        if ($errorMessage === false) {
            $data['result'] = true;
        } else {
            $data['result'] = false;
            $data['message'] = $errorMessage;
        }

        $jsonFlag = C('debug') && version_compare(PHP_VERSION, '5.4.0') >= 0
            ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            : 0;

        echo json_encode($data, $jsonFlag);
        exit;
    }
}
