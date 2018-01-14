$(function() {
	
	// 侧边导航展示形式切换
    $('#foldSidebar > i').click(function(){
        if ($('.spdsc-layout-left').hasClass('unfold')) {
            $(this).addClass('fa-indent').removeClass('fa-outdent');
            $('.spdsc-layout-left').addClass('fold').removeClass('unfold');
        } else {
            $(this).addClass('fa-outdent').removeClass('fa-indent');
            $('.spdsc-layout-left').addClass('unfold').removeClass('fold');
        }
    });
	
	//焦点调整
	controller = get_query_string("c");	
	controller = convert_word_underscore(controller);
	action = get_query_string("a");	
	if ((controller == 'seller_center') && ((action == 'index') || (!action)))
	
	$(".quick-link-div").show()
	active_li_count = $(".seller_center_left_menu div dd ul li[active='1']").length;
	if (active_li_count)
	{
		$(".seller_center_left_menu .left_nav_div:not(.quick-link-div) dd ul li[active='1']").parent().attr("active", "1")
		.parent().attr("is_active", "1").parent().attr("active", "1")
		.parent().show().attr("active", "1");
	}
	else
	{
		//console.log(controller);
		$(".seller_center_left_menu .left_nav_div dl dd ul li[controller='"+ controller +"']")
		.attr("active", "1")
		.parent().attr("active", "1")
		.parent().attr("active", "1")
		.parent().attr("active", "1")
		.parent().attr("active", "1")
		.show();
		
		//$(".quick-link-div").show();
		
	}
	
	//左侧二级分类点击跳转
	$(".seller_center_left_menu .left_nav_div:not(.quick-link-div) dl dt").click(function () {
		$(this).parent().find("dd ul li:first-child").click();
	});
	

	//左侧边栏高度设置
	right_body_height = $(".spdsc-layout-right").height();
	//console.log(right_body_height);
	if ($("#layoutLeft").height() < right_body_height)
	{
		$("#layoutLeft").css("height", right_body_height);
	}
	
	//footer高度设置
	left_menu_height = $("#layoutLeft").height();
	head_menu_height = $(".spdsc-head-layout").height();
	
	$("#footer").css("top",  left_menu_height + head_menu_height);
	
	//导航栏焦点设置
	active_group_key = $(".seller_center_left_menu div[active='1']").attr("nav-group-key");
	//console.log(active_group_key);
	if (active_group_key) {
		$(".spdsc-nav ul li[key='" + active_group_key + "'] a").css("background", "rgba(255,255,255,0.15)");
	} else {
		$(".spdsc-nav ul li[key='homepage'] a").css("background", "rgba(255,255,255,0.15)");
	}
	
	//快捷菜单
	$('.spdsc-head-layout .icon-sitemap').bind("click",
	function() {
		$(".sitemap-menu").slideDown("fast");
	});

	$('.quick-link-div dl dt a h3').bind("click",
	function() {
		$(".sitemap-menu").slideDown("slow");
	});
	
	$('#closeSitemap').bind("click",
	function() {
		$(".sitemap-menu-arrow").slideUp("fast");
		$(".sitemap-menu").slideUp("fast");
	});
	
	$(".root-nav-ul .root_nav_li").click(function() {
		$(".root-nav-ul li.is-seleced").removeClass("is-seleced");
		$(this).addClass("is-seleced");
		key = $(this).attr("key");
		
		$("#quicklink_list dl").hide();
		$("#quicklink_list dl[group_key='" + key + "']").show();
		
	});
	$(".root-nav-ul .root_nav_li:first-child").click();

	//$('.spdsc-head-layout .icon-sitemap').click();

});

//头部导航下拉菜单相关
$(function() {
		$(".spdsc-nav dl").hover(function() {
		$(this).addClass("hover");
	},
	function() {
		$(this).removeClass("hover");
	});

});

//返回到顶部
$(function() {
    backTop=function (btnId){
	var btn=document.getElementById(btnId);
	var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
	window.onscroll=set;
	btn.onclick=function (){
		btn.style.display="none";
		window.onscroll=null;
		this.timer=setInterval(function(){
		    scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
			scrollTop-=Math.ceil(scrollTop*0.1);
			if(scrollTop==0) clearInterval(btn.timer,window.onscroll=set);
			if (document.documentElement.scrollTop > 0) document.documentElement.scrollTop=scrollTop;
			if (document.body.scrollTop > 0) document.body.scrollTop=scrollTop;
		},10);
	};
	function set(){
	    scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
	    btn.style.display=scrollTop?'block':"none";
	}
	};
	backTop('gotop');
});

(function($) {
//凸显触及图片样式
	$.fn.jfade = function(settings) {

		var defaults = {
			start_opacity: "1",
			high_opacity: "1",
			low_opacity: ".1",
			timing: "500"
		};
		var settings = $.extend(defaults, settings);
		settings.element = $(this);

		//set opacity to start
		$(settings.element).css("opacity", settings.start_opacity);
		//mouse over
		$(settings.element).hover(

		//mouse in
		function() {
			$(this).stop().animate({
				opacity: settings.high_opacity
			},
			settings.timing); //100% opacity for hovered object
			$(this).siblings().stop().animate({
				opacity: settings.low_opacity
			},
			settings.timing); //dimmed opacity for other objects
		},

		//mouse out
		function() {
			$(this).stop().animate({
				opacity: settings.start_opacity
			},
			settings.timing); //return hovered object to start opacity
			$(this).siblings().stop().animate({
				opacity: settings.start_opacity
			},
			settings.timing); // return other objects to start opacity
		});
		return this;
	}
})(jQuery);


function openNav(first_group_key, obj)
{
	if (first_group_key == 'platform_homepage') 
	{
		window.location.href= $(obj).attr("url-data");
		return;
	}
	else
	{
		$(".spdsc-layout-left .sidebar [nav-group-key='" + first_group_key + "'] dl:first-child dd ul li:first-child").click();
		return;
	}
}

function openItem(url_key, obj)
{
	//"store_promotion_combo|index"
	url_key_arr = url_key.split("|");
	c = url_key_arr[0];
	a = url_key_arr[1];
	window.location.href= $(obj).attr("url-data");
	return;
}