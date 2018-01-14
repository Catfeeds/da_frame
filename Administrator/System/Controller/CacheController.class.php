<?php
/**
 * 清理缓存
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace System\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Cache;
use Common\Lib\CacheFile;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Tpl;



class CacheController extends SystemController {
    protected $cacheItems = array(
        'setting',          // 基本缓存
        'seo',              // SEO缓存
        'groupbuy_price',   // 抢购价格区间
        'nav',              // 底部导航缓存
        'express',          // 快递公司
        'store_class',      // 店铺分类
        'store_grade',      // 店铺等级
        'store_msg_tpl',    // 店铺消息
        'member_msg_tpl',   // 用户消息
        'consult_type',     // 咨询类型
        'circle_level',     // 圈子成员等级
        'admin_menu',       // 后台菜单
        'area',              // 地区
        'contractitem'      //消费者保障服务
    );

    public function __construct() {
        parent::__construct();
        Language::read('cache', 'Home');
    }

    public function index() {
        $this->clear();
    }

    /**
     * 清理缓存
     */
    public function clear() {
        if (!chksubmit()) {
			$this->setDirquna('system');
            $this->render('cache.clear');
            return;
        }

        $lang = Language::getLangContent();

        // 清理所有缓存
        if ($_POST['cls_full'] == 1) {
        	
        	$this->clear_template_cache();
        	
            foreach ($this->cacheItems as $i) {
                dkcache($i);
            }

            // 商品分类
            dkcache('gc_class');
            dkcache('all_categories');
            dkcache('goods_class_seo');
            dkcache('class_tag');

            // 广告
            Model('adv')->makeApAllCache();
 
            // 首页及频道
            Model('web_config')->updateWeb(array('web_show'=>1),array('web_html'=>''));
            delCacheFile('index');
            dkcache('channel');
			
			dkcache('index/article');
 
 
        } else {
            $todo = (array) $_POST['cache'];

            foreach ($this->cacheItems as $i) {
                if (in_array($i, $todo)) {
                    dkcache($i);
                }
            }
 
            // 商品分类
            if (in_array('goodsclass', $todo)) {
                dkcache('gc_class');
                dkcache('all_categories');
                dkcache('goods_class_seo');
                dkcache('class_tag');
            }

            // 广告
            if (in_array('adv', $todo)) {
                Model('adv')->makeApAllCache();
            }

            // 首页及频道
            if (in_array('index', $todo)) {
                Model('web_config')->updateWeb(array('web_show'=>1),array('web_html'=>''));
                delCacheFile('index');
                dkcache('channel');

				dkcache('index/article');
 
            }
            
            //模板
            if (in_array("template", $todo)) {
            	$this->clear_template_cache();
            }
        }

        $this->log(L('cache_cls_operate'));
   
        showMessage($lang['cache_cls_ok']);
    }
    
    private function clear_template_cache()
    {
    	$file_arr = array();
    	$runtimePathArr = C('RUNTIME_PATH_LIST');
    	foreach ($runtimePathArr as $runtimePath)
    	{
    		$base_cache_dir = BASE_ROOT_PATH . trim($runtimePath, ".");
    		$base_cache_dir = $base_cache_dir . DIRECTORY_SEPARATOR . "Cache";
    		$base_cache_dir = str_replace(array("/", "\\"), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $base_cache_dir);
    		$base_cache_dir = str_replace(array(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR), array(DIRECTORY_SEPARATOR), $base_cache_dir);

    		$temp_file_list = scan_dir($base_cache_dir);
    		
    		foreach ($temp_file_list as $filename)
    		{
    			$file_arr[] = $filename;
    		}
    	}
    	
    	$ret = true;
    	foreach ($file_arr as $filename) {
    		if (is_file($filename)) {
    			@unlink($filename);
    		}else{
    			$ret = false;
    		}
    	}
    	return $ret;
    }
}
