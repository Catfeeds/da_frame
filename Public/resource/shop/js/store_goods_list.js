$(function(){
    // ajax获取商品列表
    $('i[datype="ajaxGoodsList"]').toggle(
        function(){
            $(this).removeClass('icon-plus-sign').addClass('icon-minus-sign');
            var _parenttr = $(this).parents('tr');
            var _commonid = $(this).attr('data-comminid');
            var _div = _parenttr.next().find('.spdsc-spdgoods-sku');
            if (_div.html() == '') {
                $.getJSON(_PAGE_URL + '&c=StoreGoodsOnline&a=get_goods_list_ajax' , {commonid : _commonid}, function(date){
                    if (date != 'false') {
                        var _ul = $('<ul class="spdsc-spdgoods-sku-list"></ul>');
                        $.each(date, function(i, o){
                            var _li = $('<li></li>');
                            $('<div class="spdgoods-thumb" title="商家货号：' + o.goods_serial + '"><a href="' + o.url + '" target="_blank"><image src="' + o.goods_image + '" ></a></div>').appendTo(_li);
                            $('<div class="spdgoods-sku">SKU：<em>' + o.goods_id + '</em></div>').appendTo(_li);
                            $(o.goods_spec).appendTo(_li);
                            $('<div class="spdgoods-price">价格：<em title="&yen;' + o.goods_price + '">&yen;' + o.goods_price + '</em></div>').appendTo(_li);
                            $('<div class="spdgoods-storage" ' + o.alarm + '>库存：<em title="' + o.goods_storage + '" ' + o.alarm + '>' + o.goods_storage + '</em></div>').appendTo(_li);
							if (o.is_distribution != '0') {
							$('<div class="spdgoods-price">佣金：<em title="&yen;' + o.invite_rate + '">&yen;' + o.invite_rate + '</em></div>').appendTo(_li);}
                            $('<div><a href="' + o.url + '" target="_blank" class="spdbtn-mini">查看商品详情</a></div>').appendTo(_li);
                            $('<div><a href="javascript:;" class="spdbtn-mini spdbtn-lavander mt5" onclick="ajax_form(\'ajax_jingle\', \'编辑独立描述\', \''+ _PAGE_URL + '&c=StoreGoodsOnline&a=edit_iframe_ajax&goods_id=' + o.goods_id + '\', \'915\')">编辑独立描述</a></div>').appendTo(_li);
							if (o.is_distribution != '0') {$('<div><a href="javascript:;" class="spdbtn-mini spdbtn-aqua mt5" onclick="ajax_form(\'ajax_jingle\', \'编辑分销佣金\', \''+ _PAGE_URL + '&c=StoreGoodsOnline&a=edit_invite_price&goods_id=' + o.goods_id + '\', \'650\')">编辑分销佣金</a></div>').appendTo(_li);}
                            _li.appendTo(_ul);
                        });
                        _ul.appendTo(_div);
                        _parenttr.next().show();
                        _div.perfectScrollbar();
                    }
                });
            } else {
            	_parenttr.next().show()
            }
        },
        function(){
            $(this).removeClass('icon-minus-sign').addClass('icon-plus-sign');
            $(this).parents('tr').next().hide();
        }
    );
});