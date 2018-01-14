<?php
/**
 * 行业分析
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Cache;
use Common\Lib\Db;
use Common\Lib\Excel;
use Common\Lib\Model;



class StatIndustryController extends SystemController {

    private $search_arr;//处理后的参数
    private $gc_arr;//分类数组
    private $choose_gcid;//选择的分类ID

    public function __construct(){
        parent::__construct();
		
		    $this->links = array(
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=StatIndustry&a=scale','lang'=>'stat_industryscale'),
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=StatIndustry&a=rank','lang'=>'stat_industryrank'),
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=StatIndustry&a=price','lang'=>'stat_industryprice'),
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=StatIndustry&a=general','lang'=>'stat_industrygeneral'),
    );
		
        Language::read('stat', 'Home');
 
        $model = Model('stat');
        //存储参数
        $this->search_arr = $_REQUEST;
        //处理搜索时间
        if (!isset($this->search_arr['a']) || in_array($this->search_arr['a'],array('scale','rank','price'))){
            $this->search_arr = $model->dealwithSearchTime($this->search_arr);
            //获得系统年份
            $year_arr = getSystemYearArr();
            //获得系统月份
            $month_arr = getSystemMonthArr();
            //获得本月的周时间段
            $week_arr = getMonthWeekArr($this->search_arr['week']['current_year'], $this->search_arr['week']['current_month']);
            $this->assign('year_arr', $year_arr);
            $this->assign('month_arr', $month_arr);
            $this->assign('week_arr', $week_arr);
        }
        $this->assign('search_arr', $this->search_arr);
        /**
         * 处理商品分类
         */
        $show_depth = 3;//select需要展示的深度
        if (!isset($this->search_arr['a']) || in_array($this->search_arr['a'],array('scale','general'))){//仅显示前两级分类
            $show_depth = 2;
        }
        $this->choose_gcid = ($t = intval($_REQUEST['choose_gcid']))>0?$t:0;
        $gccache_arr = Model('goods_class')->getGoodsclassCache($this->choose_gcid,$show_depth);
        $this->gc_arr = $gccache_arr['showclass'];
        $this->assign('gc_json',json_encode($gccache_arr['showclass']));
        $this->assign('gc_choose_json',json_encode($gccache_arr['choose_gcid']));
    }
    public function index() {
        $this->scale();
    }
    /**
     * 行业规模
     */
    public function scale(){
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $model = Model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        $this->assign('searchtime',implode('|',$searchtime_arr));
        $this->assign('top_link',$this->sublink($this->links, 'scale'));
		$this->setDirquna('shop');
        $this->render('stat.industry.scale');
    }
    /**
     * 行业规模列表
     */
    public function scale_list(){
        //获得子分类ID
        $gc_childid = $gc_childarr = array();
        if ($this->choose_gcid > 0){//如果进行了分类搜索，则统计该分类下的子分类
            $gc_childdepth = $this->gc_arr[$this->choose_gcid]['depth'] + 1;
            $gc_childid = explode(',',$this->gc_arr[$this->choose_gcid]['child']);
            if ($gc_childid){
                foreach ((array)$this->gc_arr as $k => $v){
                    if (in_array($v['gc_id'],$gc_childid)){
                        $gc_childarr[$v['gc_id']] = $v;
                    }
                }
            }
        } else {//如果没有搜索分类，则默认统计一级分类
            $gc_childdepth = 1;
            foreach ((array)$this->gc_arr as $k => $v){
                if ($v['depth'] == 1){
                    $gc_childarr[$v['gc_id']] = $v;
                }
            }
        }
        if($gc_childarr){
            $model = Model('stat');
            $stat_list = array();
            //构造横轴数据
            foreach($gc_childarr as $k => $v){
                $stat_list[$k]['gc_name'] = $v['gc_name'];
                $stat_list[$k]['y'] = 0;
            }
            $where = array();
            $where['order_isvalid'] = 1;//计入统计的有效订单
            $searchtime_arr_tmp = explode('|',$this->search_arr['t']);
            foreach ((array)$searchtime_arr_tmp as $k => $v){
                $searchtime_arr[] = intval($v);
            }
            $where['order_add_time'] = array('between',$searchtime_arr);
            if ($this->choose_gcid > 0){
                $where['gc_parentid_'.($gc_childdepth-1)] = $this->choose_gcid;
            }
            $field = 'gc_parentid_'.$gc_childdepth.' as statgc_id';
            $_field = 'gc_parentid_'.$gc_childdepth;
            switch ($_GET['stattype']){
               case 'ordernum':
                   $caption = '下单量';
                   $field .= ',COUNT(DISTINCT order_id) as ordernum';
                   $orderby = 'ordernum desc';
                   break;
               case 'goodsnum':
                   $caption = '下单商品数';
                   $field .= ',SUM(goods_num) as goodsnum';
                   $orderby = 'goodsnum desc';
                   break;
               default:
                   $_GET['stattype'] = 'orderamount';
                   $caption = '下单金额';
                   $field .= ',SUM(goods_pay_price) as orderamount';
                   $orderby = 'orderamount desc';
                   break;
            }
            $orderby .= ','.$_field.' asc';

            $goods_list = $model->statByStatordergoods($where, $field, 0, 0, $orderby, $_field);

            foreach ((array)$goods_list as $k => $v){
                $statgc_id = intval($v['statgc_id']);
                if (in_array($statgc_id,array_keys($gc_childarr))){
                    $stat_list[$statgc_id]['gc_name'] = strval($gc_childarr[$v['statgc_id']]['gc_name']);
                } else {
                    $stat_list[$statgc_id]['gc_name'] = '其他';
                }
                switch ($_GET['stattype']){
                   case 'orderamount':
                       $stat_list[$statgc_id]['y'] = floatval($v[$_GET['stattype']]);
                       break;
                   default:
                       $stat_list[$statgc_id]['y'] = intval($v[$_GET['stattype']]);
                       break;
                }
            }
            //构造横轴数据
            foreach($stat_list as $k => $v){
                //数据
                $stat_arr['series'][0]['data'][] = array('name'=>strval($v['gc_name']),'y'=>$v['y']);
                //横轴
                $stat_arr['xAxis']['categories'][] = strval($v['gc_name']);
            }
            //得到统计图数据
            $stat_arr['series'][0]['name'] = $caption;
            $stat_arr['title'] = "行业{$caption}统计";
            $stat_arr['legend']['enabled'] = false;
            $stat_arr['yAxis']['title']['text'] = $caption;
            $stat_arr['yAxis']['title']['align'] = 'high';
            $statjson = getStatData_Basicbar($stat_arr);
            $this->assign('stat_json',$statjson);
            $this->assign('stattype',$_GET['stattype']);
			$this->setDirquna('shop');
            $this->render('stat.linelabels','null_layout');
        }
    }
    /**
     * 行业排行
     */
    public function rank(){
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $model = Model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        $where = array();
        $where['order_isvalid'] = 1;//计入统计的有效订单
        $where['order_add_time'] = array('between',$searchtime_arr);
        $gc_id_depth = $this->gc_arr[$this->choose_gcid]['depth'];
        if ($this->choose_gcid > 0){
            $where['gc_parentid_'.$gc_id_depth] = $this->choose_gcid;
        }
        /**
         * 商品排行
         */
        $goods_stat_arr = array();
        //构造横轴数据
        for($i=1; $i<=50; $i++){
            //数据
            $goods_stat_arr['series'][0]['data'][] = array('name'=>'','y'=>0);
            //横轴
            $goods_stat_arr['xAxis']['categories'][] = "$i";
        }
        $field = 'goods_id,min(goods_name) as goods_name,SUM(goods_num) as goodsnum';
        $goods_list = $model->statByStatordergoods($where, $field, 0, 50, 'goodsnum desc,goods_id asc', 'goods_id');
        foreach ((array)$goods_list as $k => $v){
            $goods_stat_arr['series'][0]['data'][$k] = array('name'=>strval($v['goods_name']),'y'=>floatval($v['goodsnum']));
        }
        //得到统计图数据
        $goods_stat_arr['series'][0]['name'] = '下单商品数';
        $goods_stat_arr['title'] = "行业商品50强";
        $goods_stat_arr['legend']['enabled'] = false;
        $goods_stat_arr['yAxis'] = '下单商品数';
        $goods_statjson = getStatData_Column2D($goods_stat_arr);
        /**
         * 店铺排行
         */
        $store_stat_arr = array();
        //构造横轴数据
        for($i=1; $i<=30; $i++){
            //数据
            $store_stat_arr['series'][0]['data'][] = array('name'=>'','y'=>0);
            //横轴
            $store_stat_arr['xAxis']['categories'][] = "$i";
        }
        $field = 'store_id,min(store_name) as store_name,COUNT(DISTINCT order_id) as ordernum';
        $store_list = $model->statByStatordergoods($where, $field, 0, 30, 'ordernum desc,store_id asc', 'store_id');
        foreach ((array)$store_list as $k => $v){
            $store_stat_arr['series'][0]['data'][$k] = array('name'=>strval($v['store_name']),'y'=>floatval($v['ordernum']));
        }
        //得到统计图数据
        $store_stat_arr['series'][0]['name'] = '下单量';
        $store_stat_arr['title'] = "行业店铺30强";
        $store_stat_arr['legend']['enabled'] = false;
        $store_stat_arr['yAxis'] = '下单量';
        $store_statjson = getStatData_Column2D($store_stat_arr);

        $this->assign('goods_statjson',$goods_statjson);
        $this->assign('goods_list',$goods_list);
        $this->assign('store_statjson',$store_statjson);
        $this->assign('store_list',$store_list);
        $this->assign('top_link',$this->sublink($this->links, 'rank'));
		$this->setDirquna('shop');
        $this->render('stat.industry.rank');
    }
    /**
     * 价格分布
     */
    public function price(){
        if(!$this->search_arr['search_type']){
            $this->search_arr['search_type'] = 'day';
        }
        $model = Model('stat');
        //获得搜索的开始时间和结束时间
        $searchtime_arr = $model->getStarttimeAndEndtime($this->search_arr);
        $where = array();
        $where['order_isvalid'] = 1;//计入统计的有效订单
        $where['order_add_time'] = array('between',$searchtime_arr);
        $gc_id_depth = $this->gc_arr[$this->choose_gcid]['depth'];
        if ($this->choose_gcid > 0){
            $where['gc_parentid_'.$gc_id_depth] = $this->choose_gcid;
        }
        $field = '1';
        $pricerange_arr = ($t = trim(C('stat_pricerange')))?unserialize($t):'';
        if ($pricerange_arr){
            $goodsnum_stat_arr['series'][0]['name'] = '下单商品数';
            $orderamount_stat_arr['series'][0]['name'] = '下单金额';
            //设置价格区间最后一项，最后一项只有开始值没有结束值
            $pricerange_count = count($pricerange_arr);
            if ($pricerange_arr[$pricerange_count-1]['e']){
                $pricerange_arr[$pricerange_count]['s'] = $pricerange_arr[$pricerange_count-1]['e'] + 1;
                $pricerange_arr[$pricerange_count]['e'] = '';
            }
            foreach ((array)$pricerange_arr as $k => $v){
                $v['s'] = intval($v['s']);
                $v['e'] = intval($v['e']);
                //构造查询字段
                if (C('dbdriver') == 'mysqli') {
                    if ($v['e']){
                        $field .= " ,SUM(IF(goods_pay_price/goods_num > {$v['s']} and goods_pay_price/goods_num <= {$v['e']},goods_num,0)) as goodsnum_{$k}";
                        $field .= " ,SUM(IF(goods_pay_price/goods_num > {$v['s']} and goods_pay_price/goods_num <= {$v['e']},goods_pay_price,0)) as orderamount_{$k}";
                    } else {//由于最后一个区间没有结束值，所以需要单独构造sql
                        $field .= " ,SUM(IF(goods_pay_price/goods_num > {$v['s']},goods_num,0)) as goodsnum_{$k}";
                        $field .= " ,SUM(IF(goods_pay_price/goods_num > {$v['s']},goods_pay_price,0)) as orderamount_{$k}";
                    }
                }
            }
            $ordergooods_list = $model->getoneByStatordergoods($where, $field);
            if($ordergooods_list){
                foreach ((array)$pricerange_arr as $k => $v){
                    //横轴
                    if($v['e']){
                        $goodsnum_stat_arr['xAxis']['categories'][] = $v['s'].'-'.$v['e'];
                        $orderamount_stat_arr['xAxis']['categories'][] = $v['s'].'-'.$v['e'];
                    } else {
                        $goodsnum_stat_arr['xAxis']['categories'][] = $v['s'].'以上';
                        $orderamount_stat_arr['xAxis']['categories'][] = $v['s'].'以上';
                    }
                    //统计图数据
                    $goodsnum_stat_arr['series'][0]['data'][$k] = 0;
                    $orderamount_stat_arr['series'][0]['data'][$k] = 0;
                    if ($ordergooods_list['goodsnum_'.$k]){
                        $goodsnum_stat_arr['series'][0]['data'][$k] = intval($ordergooods_list['goodsnum_'.$k]);
                    }
                    if ($ordergooods_list['orderamount_'.$k]){
                        $orderamount_stat_arr['series'][0]['data'][$k] = intval($ordergooods_list['orderamount_'.$k]);
                    }
                }
            }
            //得到统计图数据
            $goodsnum_stat_arr['legend']['enabled'] = false;
            $goodsnum_stat_arr['title'] = '行业价格下单商品数';
            $goodsnum_stat_arr['yAxis'] = '';

            $orderamount_stat_arr['legend']['enabled'] = false;
            $orderamount_stat_arr['title'] = '行业价格下单金额';
            $orderamount_stat_arr['yAxis'] = '';
            $goodsnum_stat_json = getStatData_LineLabels($goodsnum_stat_arr);
            $orderamount_stat_json = getStatData_LineLabels($orderamount_stat_arr);
        } else {
            $goodsnum_stat_json = '';
            $orderamount_stat_json = '';
        }
        $this->assign('goodsnum_stat_json',$goodsnum_stat_json);
        $this->assign('orderamount_stat_json',$orderamount_stat_json);
        $this->assign('top_link',$this->sublink($this->links, 'price'));
		$this->setDirquna('shop');
        $this->render('stat.industry.price');
    }

    /**
     * 销售统计
     */
    public function general(){
        $this->assign('top_link',$this->sublink($this->links, 'general'));
		$this->setDirquna('shop');
        $this->render('stat.industry.general');
    }

    /**
     * 概况总览
     */
    public function general_list(){
        //获得子分类ID
        $gc_childid = $gc_childarr = array();
        if ($this->choose_gcid > 0){//如果进行了分类搜索，则统计该分类下的子分类
            $gc_childdepth = $this->gc_arr[$this->choose_gcid]['depth'] + 1;
            $gc_childid = explode(',',$this->gc_arr[$this->choose_gcid]['child']);
            if ($gc_childid){
                foreach ((array)$this->gc_arr as $k => $v){
                    if (in_array($v['gc_id'],$gc_childid)){
                        $gc_childarr[$v['gc_id']] = $v;
                    }
                }
            }
        } else {//如果没有搜索分类，则默认统计一级分类
            $gc_childdepth = 1;
            foreach ((array)$this->gc_arr as $k => $v){
                if ($v['depth'] == 1){
                    $gc_childarr[$v['gc_id']] = $v;
                }
            }
        }
        if (!empty($_GET['id']) && is_array($_GET['id'])){
            $list = array();
            foreach ($_GET['id'] as $k => $v){
                $list[$v] = $gc_childarr[$v];
            }
            $gc_childarr = $list;
        }

        $statlist = array();

        if (!empty($gc_childarr) && is_array($gc_childarr)){
            $model = Model('stat');
            //查询订单商品信息
            $where = array();
            $where['order_isvalid'] = 1;//计入统计的有效订单
            //计算开始时间和结束时间
            $searchtime_arr[1] = strtotime(date('Y-m-d',time())) - 1;//昨天23:59点
            $searchtime_arr[0] = $searchtime_arr[1] - (86400 * 30) + 1; //从昨天开始30天前
            $where['order_add_time'] = array('between',$searchtime_arr);
            if ($this->choose_gcid > 0){
                $where['gc_parentid_'.($gc_childdepth-1)] = $this->choose_gcid;
            }
            $field = 'gc_parentid_'.$gc_childdepth.' as statgc_id,COUNT(DISTINCT goods_id) as ordergcount,SUM(goods_num) as ordergnum,SUM(goods_pay_price) as orderamount';
            $ordergoods_list_tmp = $model->statByStatordergoods($where, $field, 0, 0, '', 'gc_parentid_'.$gc_childdepth);

            foreach ((array)$ordergoods_list_tmp as $k => $v){
                $ordergoods_list[$v['statgc_id']] = $v;
            }

            //查询商品信息
            $field = 'gc_id_'.$gc_childdepth.' as statgc_id,COUNT(*) as goodscount,AVG(goods_price) as priceavg';
            $goods_list_tmp = $model->statByGoods(array('is_virtual'=>0), $field, 0, 0, '', 'gc_id_'.$gc_childdepth);

            foreach ((array)$goods_list_tmp as $k => $v){
                $goods_list[$v['statgc_id']] = $v;
            }
            //将订单和商品数组合并
            $statlist_tmp = array();
            foreach ($gc_childarr as $k => $v){
                $tmp = array();
                $tmp['statgc_id'] = $v['gc_id'];
                $tmp['gc_name'] = $v['gc_name'];
                $tmp['ordergcount'] = ($t = $ordergoods_list[$v['gc_id']]['ordergcount'])?$t:0;
                $tmp['ordergnum'] = ($t = $ordergoods_list[$v['gc_id']]['ordergnum'])?$t:0;
                $tmp['orderamount'] = ($t = $ordergoods_list[$v['gc_id']]['orderamount'])?$t:0;
                $tmp['goodscount'] = ($t = $goods_list[$v['gc_id']]['goodscount'])?$t:0;
                $tmp['priceavg'] = daPriceFormat(($t = $goods_list[$v['gc_id']]['priceavg'])?$t:0);
                $tmp['unordergcount'] = intval($goods_list[$v['gc_id']]['goodscount']) - intval($ordergoods_list[$v['gc_id']]['ordergcount']);//计算无销量商品数
                $statlist_tmp[]= $tmp;
            }
            $statlist = array();
            //整理排序
            $orderby = trim($this->search_arr['orderby']);
            if (!$orderby){
                $orderby = 'orderamount desc';
            }
            $orderkeys = explode(' ',$orderby);
            $keysvalue = $new_array = array();
            foreach ($statlist_tmp as $k => $v){
                $keysvalue[$k] = $v[$orderkeys[0]];
            }
            if($orderkeys[1] == 'asc'){
                asort($keysvalue);
            }else{
                arsort($keysvalue);
            }
            reset($keysvalue);
            foreach ($keysvalue as $k => $v){
                $statlist[$k] = $statlist_tmp[$k];
            }
            //导出Excel
            if ($this->search_arr['exporttype'] == 'excel'){
                //列表header
                $statheader = array();
                $statheader[] = array('text'=>'类目名称','key'=>'gc_name');
                $statheader[] = array('text'=>'平均价格（元）','key'=>'priceavg','isorder'=>1);
                $statheader[] = array('text'=>'有销量商品数','key'=>'ordergcount','isorder'=>1);
                $statheader[] = array('text'=>'销量','key'=>'ordergnum','isorder'=>1);
                $statheader[] = array('text'=>'销售额（元）','key'=>'orderamount','isorder'=>1);
                $statheader[] = array('text'=>'商品总数','key'=>'goodscount','isorder'=>1);
                $statheader[] = array('text'=>'无销量商品数','key'=>'unordergcount','isorder'=>1);
                //导出Excel
 
                $excel_obj = new Excel();
                $excel_data = array();
                //设置样式
                $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
                //header
                foreach ($statheader as $k => $v){
                    $excel_data[0][] = array('styleid'=>'s_title','data'=>$v['text']);
                }
                //data
                foreach ($statlist as $k => $v){
                    foreach ($statheader as $h_k=>$h_v){
                        $excel_data[$k+1][] = array('data'=>$v[$h_v['key']]);
                    }
                }
                $excel_data = $excel_obj->charset($excel_data,CHARSET);
                $excel_obj->addArray($excel_data);
                $excel_obj->addWorksheet($excel_obj->charset('行业概况总览',CHARSET));
                $excel_obj->generateXML($excel_obj->charset('行业概况总览',CHARSET).date('Y-m-d-H',time()));
                exit();
            }
        }
    }

    /**
     * 输出概况总览XML数据
     */
    public function get_general_xml(){
        //获得子分类ID
        $gc_childid = $gc_childarr = array();
        if ($this->choose_gcid > 0){//如果进行了分类搜索，则统计该分类下的子分类
            $gc_childdepth = $this->gc_arr[$this->choose_gcid]['depth'] + 1;
            $gc_childid = explode(',',$this->gc_arr[$this->choose_gcid]['child']);
            if ($gc_childid){
                foreach ((array)$this->gc_arr as $k => $v){
                    if (in_array($v['gc_id'],$gc_childid)){
                        $gc_childarr[$v['gc_id']] = $v;
                    }
                }
            }
        } else {//如果没有搜索分类，则默认统计一级分类
            $gc_childdepth = 1;
            foreach ((array)$this->gc_arr as $k => $v){
                if ($v['depth'] == 1){
                    $gc_childarr[$v['gc_id']] = $v;
                }
            }
        }

        $statlist = array();

        if ($gc_childarr){
            $model = Model('stat');
            //查询订单商品信息
            $where = array();
            $where['order_isvalid'] = 1;//计入统计的有效订单
            //计算开始时间和结束时间
            $searchtime_arr[1] = strtotime(date('Y-m-d',time())) - 1;//昨天23:59点
            $searchtime_arr[0] = $searchtime_arr[1] - (86400 * 30) + 1; //从昨天开始30天前
            $where['order_add_time'] = array('between',$searchtime_arr);
            if ($this->choose_gcid > 0){
                $where['gc_parentid_'.($gc_childdepth-1)] = $this->choose_gcid;
            }
            $field = 'gc_parentid_'.$gc_childdepth.' as statgc_id,COUNT(DISTINCT goods_id) as ordergcount,SUM(goods_num) as ordergnum,SUM(goods_pay_price) as orderamount';
            $ordergoods_list_tmp = $model->statByStatordergoods($where, $field, 0, 0, '', 'gc_parentid_'.$gc_childdepth);

            foreach ((array)$ordergoods_list_tmp as $k => $v){
                $ordergoods_list[$v['statgc_id']] = $v;
            }

            //查询商品信息
            $field = 'gc_id_'.$gc_childdepth.' as statgc_id,COUNT(*) as goodscount,AVG(goods_price) as priceavg';
            $goods_list_tmp = $model->statByGoods(array('is_virtual'=>0), $field, 0, 0, '', 'gc_id_'.$gc_childdepth);

            foreach ((array)$goods_list_tmp as $k => $v){
                $goods_list[$v['statgc_id']] = $v;
            }
            //将订单和商品数组合并
            $statlist_tmp = array();
            foreach ($gc_childarr as $k => $v){
                $out_array = array();
                $out_array['operation'] = "--";
                $out_array['gc_name'] = $v['gc_name'];
                $out_array['priceavg'] = daPriceFormat(($t = $goods_list[$v['gc_id']]['priceavg'])?$t:0);
                $out_array['ordergcount'] = ($t = $ordergoods_list[$v['gc_id']]['ordergcount'])?$t:0;
                $out_array['ordergnum'] = ($t = $ordergoods_list[$v['gc_id']]['ordergnum'])?$t:0;
                $out_array['orderamount'] = daPriceFormat(($t = $ordergoods_list[$v['gc_id']]['orderamount'])?$t:0);
                $out_array['goodscount'] = ($t = $goods_list[$v['gc_id']]['goodscount'])?$t:0;
                $out_array['unordergcount'] = intval($goods_list[$v['gc_id']]['goodscount']) - intval($ordergoods_list[$v['gc_id']]['ordergcount']);//计算无销量商品数
                $statlist_tmp[$v['gc_id']]= $out_array;
            }
            $statlist = array();
            //整理排序
            $orderby = trim($this->search_arr['sortname']);
            $order_type = array('priceavg','ordergcount','ordergnum','ordergnum','orderamount','goodscount','unordergcount');
            if (!in_array($orderby,$order_type)){
                $orderby = 'orderamount';
            }
            foreach ($statlist_tmp as $k => $v){
                $keysvalue[$k] = $v[$orderby];
            }
            $sortorder = trim($this->search_arr['sortorder']);
            $sort_type = array('asc','desc');
            if (!in_array($sortorder,$sort_type)){
                $sortorder = 'desc';
            }
            if($sortorder == 'asc'){
                asort($keysvalue);
            }else{
                arsort($keysvalue);
            }
            reset($keysvalue);
            foreach ($keysvalue as $k => $v){
                $statlist[$k] = $statlist_tmp[$k];
            }
        }
        $data = array();
        $data['total_num'] = count($statlist);
        $data['list'] = $statlist;
        echo $this->flexigridXML($data);exit();
    }
}
