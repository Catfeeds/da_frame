$(function() {

	$('#admincp-container-left').perfectScrollbar({wheelPropagation:true});

	//使用title内容作为tooltip提示文字
    $(document).tooltip({
        track: true
    });

	trace_show();
	
    if ($.cookie('bgColorSelectorPosition') != null) {
        $('body').css('background-color', bgColorSelectorColors[$.cookie('bgColorSelectorPosition')].c);
    } else {
        $('body').css('background-color', bgColorSelectorColors[47].c);
    } 
	
	// 侧边导航三级级菜单点击
	$('.sub-menu ul li').click(function() {
		openItem($(this).find("a").attr('data-param'), this);
	});
	
	
	// 顶部各个模块切换
    $('.spd-module-menu').find('a').click(function(){
        
			
		//滚动条调整
		$('#admincp-container-left').scrollTop(0);
		$('#admincp-container-left').perfectScrollbar("update");
	
		
		//展开边栏
		if ($('.admincp-container').hasClass('fold')) {
            $('.admincp-container').removeClass('fold').addClass('unfold');
        }
		
        $('.spd-module-menu').find('li').removeClass('active');
        _modules = $(this).parent().addClass('active').attr('data-param');

		$('div[id^="admincpNavTabs_"]').hide();
		
		$('#admincpNavTabs_' + _modules).show()
		.find("dd:first li:first")
		.click();

	});
	
	// 待办事项
	setInterval("update_pending_matters()", 1000);
	
	//默认选择菜单
    if ($.cookie('workspaceParam') == null) {
        $('.nav-tabs:first dl dd ul li:first').click();
    } else {
        openItem($.cookie('workspaceParam'), $(".sub-menu li a[data-param='" + 
		$.cookie('workspaceParam') + 
		"']").parent());
    }

    // 导航菜单  显示
    $('a[datype="map_on"],a[class="add-menu"]').click(function(){
        $('div[datype="map_nav"]').show();
    });
    
	// 导航菜单 隐藏
    $('a[datype="map_off"]').click(function(){
        $('div[datype="map_nav"]').hide();
    });
	
    // 导航菜单切换
    $('a[data-param^="map-"]').click(function(){
        $(this).parent().addClass('selected').siblings().removeClass('selected');
        $('div[data-param^="map-"]').hide();
        $('div[data-param="' + $(this).attr('data-param') + '"]').show();
    });
	
	//导航菜单点击
    $('div[data-param^="map-"]').find('i').click(function(){
        var $this = $(this);
        var _value = $this.prev().attr('data-param');
		var root_nav_key = $this.parent().attr("root-nav-key");
        if ($this.parent().hasClass('selected')) {
            $.getJSON(ADMIN_SITE_URL + '?m=Home&c=Common&a=common_operations', 
			{type : 'del', value : _value}, function(data){
				if (data) {
                    $this.parent().removeClass('selected');
                    $('ul[datype="quick_link"] li[quick_link_index="' + _value + '"]').remove();
                }
            });
        } else {
            var _name = $this.prev().html();
            $.getJSON(ADMIN_SITE_URL + '?m=Home&c=Common&a=common_operations', {type : 'add', value : _value}, function(data){
                if (data) {
                    $this.parent().addClass('selected');
                    $('ul[datype="quick_link"]').append('<li ' + "quick_link_index='" + _value + "'" +
					' root-nav-key="' + root_nav_key  + '"' +
					' onclick="openItem(\'' + 
					_value + 
					'\', this)" ><a href="javascript:void(0);">' + 
					_name + 
					'</a></li>');
					
                } else {
					$(".ui-draggable-handle h5").fadeOut().fadeIn().fadeOut().fadeIn();
				}
            });
        }
    });
	
	//快捷菜单点击
	$('div[data-param^="map-"] dl dd a').click(function(){
		var data_param = $(this).attr('data-param');
        openItem(data_param, this);
    });
	
    // 导航菜单默认值显示第一组菜单
    $('div[data-param^="map-"]:first').nextAll().hide();
	$('A[data-param^="map-"]:first').parent().addClass('selected');
    
	
	//左侧菜单收起与展示
    $('#foldSidebar > i').click(function(){
        if ($('.admincp-container').hasClass('unfold')) {
            $(this).addClass('fa-indent').removeClass('fa-outdent');
            $('.admincp-container').addClass('fold').removeClass('unfold');
        } else {
            $(this).addClass('fa-outdent').removeClass('fa-indent');
            $('.admincp-container').addClass('unfold').removeClass('fold');
        }
    });

});

//更新代办事项
function update_pending_matters()
{
	$.ajax({
		url : ADMIN_SITE_URL + '?m=Home&c=Common&a=ajax_pending_matters',
		success : function(data){
					_commonPendingMatters = parseInt(data);
					$.cookie('commonPendingMatters', _commonPendingMatters, {expires : 1000});   // 一小时更新一次
				
					
					//console.log(_commonPendingMatters);
					if (_commonPendingMatters > 0) {
						$('li[datype="pending_matters"]').show().find('em').show();
						$('li[datype="pending_matters"]').show().find('em').html($.cookie('commonPendingMatters'));
					}
				
				}
				
	});
	

}


//下划线转驼峰
function convert_2_camel_case(str){
	str = ucFirst(str);
    var re=/_(\w)/g;
    ret = str.replace(re,function ($0,$1){
        return $1.toUpperCase();
    });
    return ret;
}

//ucFirst
function ucFirst(str) {
	var str = str.toLowerCase();
	str = str.replace(/\b\w+\b/g, function(word){
	  return word.substring(0,1).toUpperCase()+word.substring(1);
	});
	return str; 
}

// 点击菜单，iframe页面跳转
function openItem(param, obj) {
	//关闭所有浮层
	child_page_click();
	
	$(".dialog_close_button").trigger("click");
    
	$('.sub-menu').find('li').removeClass('active');

	var root_nav_key;
	if (obj)
	{
		root_nav_key = $(obj).attr("root-nav-key");
	}
	else
	{
		root_nav_key = JSON.parse($(".admincp-container-left").attr("menu-hash"))[param];
	}
	var data_str = param.split('|');
	
	$('.sub-menu ul li').removeClass("active");
	$(".sub-menu ul li a[data-param='" + param + "']").parent().addClass("active");
	
	
	$('#workspace').attr('src', ADMIN_SITE_URL + '?m=' + ucFirst(data_str[0]) + '&c=' + convert_2_camel_case(data_str[1]));
    $.cookie('workspaceParam', data_str[0] + '|' + data_str[1], { expires: 1 ,path:"/"});

	//切换顶部NAVTAB 用于直接点击快捷导航的情况
	$(".spd-module-menu ul li").removeClass("active");
	$(".spd-module-menu ul li[data-param='" +  root_nav_key  +"']").addClass("active");

	//侧边栏显示分组
	_modules = root_nav_key;
	$('div[id^="admincpNavTabs_"]').hide();
	$('#admincpNavTabs_' + _modules).show();
	
	$('a[datype="map_off"]').click();
}

function trace_show()
{
	//布局换色设置
    bgColorSelectorColors = [{ c: '#981767', cName: '' }, { c: '#AD116B', cName: '' }, { c: '#B61944', cName: '' }, { c: '#AA1815', cName: '' }, { c: '#C4182D', cName: '' }, { c: '#D74641', cName: '' }, { c: '#ED6E4D', cName: '' }, { c: '#D78A67', cName: '' }, { c: '#F5A675', cName: '' }, { c: '#F8C888', cName: '' }, { c: '#F9D39B', cName: '' }, { c: '#F8DB87', cName: '' }, { c: '#FFD839', cName: '' }, { c: '#F9D12C', cName: '' }, { c: '#FABB3D', cName: '' }, { c: '#F8CB3C', cName: '' }, { c: '#F4E47E', cName: '' }, { c: '#F4ED87', cName: '' }, { c: '#DFE05E', cName: '' }, { c: '#CDCA5B', cName: '' }, { c: '#A8C03D', cName: '' }, { c: '#73A833', cName: '' }, { c: '#468E33', cName: '' }, { c: '#5CB147', cName: '' }, { c: '#6BB979', cName: '' }, { c: '#8EC89C', cName: '' }, { c: '#9AD0B9', cName: '' }, { c: '#97D3E3', cName: '' }, { c: '#7CCCEE', cName: '' }, { c: '#5AC3EC', cName: '' }, { c: '#16B8D8', cName: '' }, { c: '#49B4D6', cName: '' }, { c: '#6DB4E4', cName: '' }, { c: '#8DC2EA', cName: '' }, { c: '#BDB8DC', cName: '' }, { c: '#8381BD', cName: '' }, { c: '#7B6FB0', cName: '' }, { c: '#AA86BC', cName: '' }, { c: '#AA7AB3', cName: '' }, { c: '#935EA2', cName: '' }, { c: '#9D559C', cName: '' }, { c: '#C95C9D', cName: '' }, { c: '#DC75AB', cName: '' }, { c: '#EE7DAE', cName: '' }, { c: '#E6A5CA', cName: '' }, { c: '#EA94BE', cName: '' }, { c: '#D63F7D', cName: '' }, { c: '#C1374A', cName: '' }, { c: '#AB3255', cName: '' }, { c: '#A51263', cName: '' }, { c: '#7F285D', cName: ''}];
    $(".trace_show").click(function(){
        $("div.bgSelector").toggle(300, function() {
            if ($(this).html() == '') {
                $(this).sColor({
                    colors: bgColorSelectorColors,  // 必填，所有颜色 c:色号（必填） cName:颜色名称（可空）
                    colorsWidth: '50px',  // 必填，颜色的高度
                    colorsHeight: '31px',  // 必填，颜色的高度
                    curTop: '0', // 可选，颜色选择对象高偏移，默认0
                    curImg: ADMIN_STATIC_URL + '/images/cur.png',  //必填，颜色选择对象图片路径
                    form: 'drag', // 可选，切换方式，drag或click，默认drag
                    keyEvent: true,  // 可选，开启键盘控制，默认true
                    prevColor: true, // 可选，开启切换页面后背景色是上一页面所选背景色，如不填则换页后背景色是defaultItem，默认false
                    defaultItem: ($.cookie('bgColorSelectorPosition') != null) ? $.cookie('bgColorSelectorPosition') : 22  // 可选，第几个颜色的索引作为初始颜色，默认第1个颜色
                });
            }
        });//切换显示
    });    
}

function child_page_click()
{  
    //隐藏TIP
	$("div[role=tooltip]").remove();
	
	//关闭快捷管理
	$(".manager-menu").css('display', 'none');
	$("#admin-manager-btn").attr("title","显示快捷管理");
	$("#admin-manager-btn").removeClass().addClass("arrow");

	//隐藏color select
	$("div.bgSelector").hide();
	
	//清除隐藏工具
	$(".manager-menu .hide-handle-ul .hide-handle").remove();
}
