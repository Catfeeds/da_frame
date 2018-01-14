<?php
/**
 * 微信管理
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Chat;
use Common\Lib\Db;
use Common\Lib\Model;
use Common\Lib\Page;

class SellerWechatKeywordController extends BaseSellerController {

    /**
     * 构造方法
     *
     */
    public function __construct() {
        parent::__construct();
    }
	
	/**
     * 关键词自动回复
     *
    */
    function keyword_index()
    {
	    $account_id = intval($_SESSION['member_id']);
		$model_wechat = Model('wechat');
	    //$s_account_info = es_session::get("account_info");
	    //$account_id = intval($s_account_info['id']);
		$find_data = $model_wechat->where(array('user_id'=>$account_id))->find();
	    //$find_data = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."wechat WHERE user_id = ".$account_id);
		if(empty($find_data))
		{
		    @header("Location: {$GLOBALS['_PAGE_URL']}&c=SellerWechat&a=index");
			return;
		    //app_redirect(url("biz","wechat"));return;
		    //$this->show_warning('请先设置微信接口！','马上去设置','app=wechat');return ;
		}
		
		/*$userid=$this->visitor->get('user_id');
    	$model_wechat=& m('wechat');
    	$find_data = $model_wechat->get("user_id =".$userid);
    	$model_keyword=& m('keyword');
    	if(empty($find_data))
    	{
        $this->show_warning('请先设置微信接口！','马上去设置','app=wechat');return ;
    	}*/
		
    	    $model_keyword = Model('keyword');
    		$op = $_GET['opt'];
    		if(!empty($op)){
    			$keyword = array();
    			$keyword['kename']    = trim($_POST['kename']);
    			$keyword['kyword']    = trim($_POST['keword']);
    			$keyword['type']      = intval($_POST['ketype']);
    		
    			$linkinfo=explode(",",substr($_POST['linkinfo'], 0, -1));
    			$titles=explode(",",substr($_POST['titles'], 0, -1));
    			$imageinfo=explode(",",substr($_POST['imageinfo'], 0, -1));
    			
    			if($op == 'add') {
    				if($keyword['type']==1){
    					$keyword['kecontent'] = trim($_POST['kecontent']);
    					$keyword['linkinfo']  = '';
    					$keyword['titles']    = '';
    					$keyword['imageinfo'] = '';
    				}else{
    					$keyword['kecontent'] ='';
    					$keyword['linkinfo']  = serialize($linkinfo);
    					$keyword['titles']    = serialize($titles);
    					$keyword['imageinfo'] = serialize($imageinfo);
    				}
    				 $keyword['iskey'] = 1;
				     $keyword['user_id'] = $account_id;	
				     $keyword['token'] = $find_data['token'];
					 $model_keyword->insert($keyword);
					 $insert_id = $model_keyword->getLastID();
					 //$GLOBALS['db']->autoExecute(DB_PREFIX."keyword",$keyword);
				     //$insert_id = intval($GLOBALS['db']->insert_id());
					if($insert_id > 0)
					{
						echo "1";
					}else 
					{
						echo "0";
					}
    			}

    			if($op=='update'){
    				$kid = trim($_POST['kid']);
    				if($keyword['type']==1){
    					$keyword['kecontent'] = trim($_POST['kecontent']);
    				}else{
    					$keyword['linkinfo']  = serialize($linkinfo);
    					$keyword['titles']    = serialize($titles);
    					$keyword['imageinfo'] = serialize($imageinfo);
    				}
					
					$edit_id = $model_keyword->where(array('user_id'=>$account_id, 'iskey'=>1,'kid'=>$kid))->update($keyword);
					//$edit_id = $GLOBALS['db']->autoExecute(DB_PREFIX."keyword",$keyword,"UPDATE","kid=".$kid." AND iskey =1 AND user_id=".$account_id);
    				//if($model_keyword->edit("kid=".$kid." AND iskey =1 AND user_id=".$userid,$keyword))
					if($edit_id > 0)
    				{
    					echo "1";
    				}else 
    				{
    					echo "0";
    				}
    			}
				
    			if($op=='del'){
    				$kid = trim($_POST['kid']);
					//if($model_keyword->drop("kid=".$kid." AND user_id=".$userid." AND iskey=1",$kid))
					if($model_keyword->where(array('kid' =>$kid,'user_id'=>$account_id,'iskey'=>1))->delete())
					//if($GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."keyword WHERE kid=".$kid." AND user_id=".$account_id." AND iskey=1"))
    				{
    					echo "1";
    				}
					else 
    				{
    					echo "0";
    				}
    			}
    		}
			else
    		{  	
			    $model_keyword = Model('keyword');
                $keyinfo = $model_keyword->where(array('user_id'=>$account_id, 'iskey'=>1))->select();
	            //$keyinfo = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."keyword WHERE user_id = ".$account_id." AND iskey=1 ORDER BY kid desc");
				
				$this->assign('keyinfo',$keyinfo);
				$this->assign('page_title', '关键词自动回复');
				$this->profile_menu('wechat_keyword');
				$this->render('seller_wechat_keyword_index');
    		}
	}
	

	
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key = '') {
        $menu_array = array();
        $menu_array[] = array(
            'menu_key' => 'wechat_keyword',
            'menu_name' => '关键词自动回复设置',
            'menu_url' => urlShop('seller_wechat_follow', 'keyword_index')
        );
        $this->assign('member_menu', $menu_array);
        $this->assign('menu_key', $menu_key);
    }
}
