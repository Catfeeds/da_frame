<?php
/**
 * chat
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Common\Lib;

class Chat {
    public static function getChatHtml($layout){
        $web_html = '';
        if ($layout != 'layout/msg_layout.php' && $layout != 'layout/store_joinin_layout.php'){
        	
            $avatar = getMemberAvatar($_SESSION['avatar']);
            $store_avatar = getStoreLogo($_SESSION['store_avatar']);
            $shopdamap = getShopdaHash();
            $formhash = securityGetTokenValue();
            $css_url = CHAT_TEMPLATES_URL;
            $app_url = LOGIN_SITE_URL;
            $chat_url = CHAT_SITE_URL;
            $node_url = NODE_SITE_URL;
            $shop_url = SHOP_SITE_URL;
            $goods_id = intval($_GET['goods_id']);
			$controller_name = convert_word_underscore(CONTROLLER_NAME);
			$action_name = ACTION_NAME;
			$c = strtolower(CONTROLLER_NAME);
			$a = strtolower(ACTION_NAME);
			$chatResourceUrl = CHAT_RESOURCE_SITE_URL;

            $web_html = <<<EOT
                    <link href="{$css_url}/css/chat.css" rel="stylesheet" type="text/css">
                    <div style="clear: both;"></div>
                    <div id="web_chat_dialog" style="display: none;float:right;">
                    </div>
                    <a id="chat_login" href="javascript:void(0)" style="display: none;"></a>
                    <script type="text/javascript">
                    var LOGIN_SITE_URL = '{$app_url}';
                    var CHAT_SITE_URL = '{$chat_url}';
                    var SHOP_SITE_URL = '{$shop_url}';
                    var connect_url = "{$node_url}";
					var CHAT_RESOURCE_SITE_URL = "{$chatResourceUrl}";

                    var layout = "{$layout}";
					c = "{$controller_name}";
					a = "{$action_name}";
					
                    var c_a = "{$c}_{$a}";
                    var chat_goods_id = "{$goods_id}";
                    var user = {};

                    user['u_id'] = "{$_SESSION['member_id']}";
                    user['u_name'] = "{$_SESSION['member_name']}";
                    user['s_id'] = "{$_SESSION['store_id']}";
                    user['s_name'] = "{$_SESSION['store_name']}";
                    user['s_avatar'] = "{$store_avatar}";
                    user['avatar'] = "{$avatar}";

                    $("#chat_login").spd_login({
                      shopdamap:'{$shopdamap}',
                      formhash:'{$formhash}'
                    });
                    </script>
EOT;
			if ( MODULE_NAME != 'Shop'){
                $web_html .= '<link href="' . RESOURCE_SITE_URL . '/js/perfect-scrollbar.min.css" rel="stylesheet" type="text/css">';
				$web_html .= '<script type="text/javascript" src="'.RESOURCE_SITE_URL.'/js/perfect-scrollbar.min.js"></script>';
				$web_html .= '<script type="text/javascript" src="'.RESOURCE_SITE_URL.'/js/jquery.mousewheel.js"></script>';
			}
			$web_html .= '<script type="text/javascript" src="'.RESOURCE_SITE_URL.'/js/jquery.charCount.js" charset="utf-8"></script>';
			$web_html .= '<script type="text/javascript" src="'.RESOURCE_SITE_URL.'/js/jquery.smilies.js" charset="utf-8"></script>';
			$web_html .= '<script type="text/javascript" src="'.CHAT_RESOURCE_SITE_URL.'/js/user.js" charset="utf-8"></script>';

		}
		if ($layout == 'layout/seller_layout.php'){
		    $web_html .= '<script type="text/javascript" src="'.CHAT_RESOURCE_SITE_URL.'/js/store.js" charset="utf-8"></script>';
		    $seller_smt_limits = '';
		    if (!empty($_SESSION['seller_smt_limits']) && is_array($_SESSION['seller_smt_limits'])) {
		        $seller_smt_limits = implode(',', $_SESSION['seller_smt_limits']);
		    }
			$web_html .= <<<EOT
					<script type="text/javascript">
					user['seller_id'] = "{$_SESSION['seller_id']}";
					user['seller_name'] = "{$_SESSION['seller_name']}";
					user['seller_is_admin'] = "{$_SESSION['seller_is_admin']}";
					var smt_limits = "{$seller_smt_limits}";
					</script>
EOT;
		}

		return $web_html;
	}
}
