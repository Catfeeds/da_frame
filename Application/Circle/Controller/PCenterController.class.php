<?php
/**
 * Personal Center
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Circle\Controller;
use Circle\Controller\BaseCirclePersonalController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;


class PCenterController extends BaseCirclePersonalController {
    public function __construct(){
        parent::__construct();
        Language::read('p_center');
    }

    /**
     * Personal Center theme list
     */
    public function index(){
        $model = Model();
        $theme_list = $model->table('circle_theme')->where(array('member_id'=>$this->m_id))->page(10)->order('theme_id desc')->select();
        if(!empty($theme_list)){
            $theme_list = array_under_reset($theme_list, 'theme_id');
            $themeid_array = array(); $circleid_array = array();
            foreach ($theme_list as $val){
                $themeid_array[]    = $val['theme_id'];
                $circleid_array[]   = $val['circle_id'];
            }
            $themeid_array = array_unique($themeid_array);
            $circleid_array = array_unique($circleid_array);

            // affix
            $affix_list = $model->table('circle_affix')->where(array('affix_type'=>1, 'member_id'=>$this->m_id, 'theme_id'=>array('in', $themeid_array)))->select();
            $affix_list = array_under_reset($affix_list, 'theme_id', 2);

            // like
            $like_list = $model->table('circle_like')->where(array('theme_id'=>array('in', $themeid_array)))->select();
            $like_list = array_under_reset($like_list, 'theme_id');
            if(!empty($like_list)){
                $lt_id = array_keys($like_list);
                $this->assign('lt_id', $lt_id);
            }
        }

        $this->assign('show_page', $model->showpage('2'));
        $this->assign('theme_list', $theme_list);
        $this->assign('affix_list', $affix_list);

        $this->profile_menu('theme', 'theme');
        $this->render('p_center.theme');
    }

    /**
     * Personal Center likeing theme list
     */
    public function likeing(){
        $model = Model();
        $like_array = $model->table('circle_like')->field('circle_id,theme_id')->where(array('member_id'=>$this->m_id))->order('theme_id desc')->page(10)->select();
        if(!empty($like_array)){
            $theme_list = array_under_reset($like_array, 'theme_id');
            $themeid_array = array(); $circleid_array = array();
            foreach ($theme_list as $val){
                $themeid_array[]    = $val['theme_id'];
                $circleid_array[]   = $val['circle_id'];
            }
            $themeid_array = array_unique($themeid_array);
            $circleid_array = array_unique($circleid_array);
            // theme
            $theme_list = $model->table('circle_theme')->where(array('theme_id'=>array('in', $themeid_array)))->select();
            // affix
            $affix_list = $model->table('circle_affix')->where(array('affix_type'=>1, 'theme_id'=>array('in', $themeid_array)))->select();
            $affix_list = array_under_reset($affix_list, 'theme_id', 2);

            $this->assign('theme_list', $theme_list);
            $this->assign('affix_list', $affix_list);
        }

        $this->profile_menu('theme', 'likeing');
        $this->render('p_center.likeing');
    }

    /**
     * Personal Center my circle group
     */
    public function my_group(){
        $model = Model();
        $circlemember_array = $model->table('circle_member')->where(array('member_id'=>$this->m_id))->select();
        if(!empty($circlemember_array)){
            $circlemember_array = array_under_reset($circlemember_array, 'circle_id');
            $this->assign('cm_array', $circlemember_array);
            $circleid_array = array_keys($circlemember_array);
            $circle_list = $model->table('circle')->where(array('circle_id'=>array('in', $circleid_array)))->select();
            $this->assign('circle_list', $circle_list);
        }
        $this->profile_menu('group', 'group');
        $this->render('p_center.group');
    }

    /**
     * Personal Center my inform
     */
    public function my_inform(){
        // language
        Language::read('manage_inform');
        $model = Model();
        $where = array();
        $where['member_id'] = $_SESSION['member_id'];
        $inform_list = $model->table('circle_inform')->where($where)->page(10)->order('inform_id desc')->select();  // tidy
        if(!empty($inform_list)){
            foreach ($inform_list as $key=>$val){
                $inform_list[$key]['url']   = spellInformUrl($val);
                $inform_list[$key]['title'] = L('circle_theme,spd_quote1').$val['theme_name'].L('spd_quote2');
                $inform_list[$key]['state'] = $this->informStatr(intval($val['inform_state']));
                if($val['reply_id'] != 0)
                    $inform_list[$key]['title'] .= L('circle_inform_reply_title');
            }
        }
        $this->assign('inform_list', $inform_list);
        $this->assign('show_page', $model->showpage(2));

        $this->profile_menu('inform', 'inform');
        $this->render('p_center.inform');
    }

    /**
     * Inform state
     */
    private function informStatr($state){
        switch ($state){
            case 0:
                return L('circle_inform_untreated');
                break;
            case 1:
                return L('circle_inform_treated');
                break;
        }
    }

    /**
     * Delete inform
     */
    public function delinform(){
        $inform_id = explode(',', $_GET['i_id']);
        if(empty($inform_id)){
            echo 'false';exit;
        }
        $where = array();
        $where['member_id'] = $_SESSION['member_id'];
        $where['inform_id'] = array('in', $inform_id);
        Model()->table('circle_inform')->where($where)->delete();
        showDialog(L('spd_common_del_succ'), 'reload', 'succ');
    }

    /**
     * Personal Center my recycled
     */
    public function my_recycled(){
        $model = Model();
        $recycle_list = $model->table('circle_recycle')->where(array('member_id'=>$_SESSION['member_id']))->order('recycle_id desc')->page(10)->select();
        $this->assign('recycle_list', $recycle_list);
        $this->assign('show_page', $model->showpage(2));
        $this->profile_menu('recycled', 'recycled');
        $this->render('p_center.recycled');
    }

    /**
     * Empty the recycle bin
     */
    public function clr_recycled(){
        Model()->table('circle_recycle')->where(array('member_id'=>$_SESSION['member_id']))->delete();
        showDialog(L('spd_common_op_succ'),'reload','succ');
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  types of navigation
     * @param string    $menu_key   key of navigation
     * @return
     */
    private function profile_menu($menu_type, $menu_key){
        $menu_array = array();
        switch ($menu_type){
            case 'theme':
                $menu_array = array(
                    1=>array('menu_key'=>'theme','menu_name'=>L('p_center_published_theme'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=PCenter'),
                    2=>array('menu_key'=>'likeing','menu_name'=>L('p_center_liked_theme'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=PCenter&a=likeing'),
                );
                break;
            case 'group':
                $menu_array = array(
                    1=>array('menu_key'=>'group','menu_name'=>L('p_center_my_circle'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=PCenter&a=my_group'),
                );
                break;
            case 'inform':
                $menu_array = array(
                    1=>array('menu_key'=>'inform','menu_name'=>L('p_center_my_inform'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=PCenter&a=my_inform'),
                );
                break;
            case 'recycled':
                $menu_array = array(
                    1=>array('menu_key'=>'recycled','menu_name'=>L('p_center_my_recycled'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=PCenter&a=my_recycled'),
                );
                break;
        }
        $this->assign('menu_type', $menu_type);
        $this->assign('member_menu', $menu_array);
        $this->assign('menu_key', $menu_key);
    }
}
