
var __thinkbox_iframe   = {
    "iframe"    : $.thinkbox.iframe('about:blank', {
        "title"     : ' ',
        "width"     : '100%',
        "height"    : '100%',
        "scrolling" : 'yes',
        "actions"   : ['close'],
        "display"   : false,
        "modal"     : true,
        "modalClose": false,
        "drag"      : true,
        "name"      : '__thinkbox_iframe'
    }),
    "clear"     : function(){
        var frm	= __thinkbox_iframe.iframe.find('iframe')[0];
        if(frm && frm.contentWindow){
            frm.contentWindow.document.write('');
        }
    },
    "post"      : function(url, args){
        __thinkbox_iframe.clear();
        var form = $('<form method="post" target="__thinkbox_iframe" style="display:none"></form>');
        form.attr({"action":url});
        $.each(args.split('&'), function(n, value){
            var param = value.split('=');
            var input = $('<input type="hidden" name="'+unescape(param[0])+'">');
            input.val(unescape(param[1]));
            form.append(input);
        });
        form.appendTo(document.body);
        form.submit();
        document.body.removeChild(form[0]);

        return  __thinkbox_iframe;
    },
    "navigate"  : function(url){
        __thinkbox_iframe.clear();
        __thinkbox_iframe.iframe.find('iframe').attr('src', url);
        return  __thinkbox_iframe;
    },
    "show"      : function(title){
        $('.thinkbox-title > span').empty().text(title?title:' ');
        __thinkbox_iframe.iframe.moveToCenter();
        __thinkbox_iframe.iframe.show();
        return  __thinkbox_iframe;
    }
};

;$(function(){
    //iframe get请求
    $('.iframe-get').click(function(){
        //
        //  attrs:
        //      url             http url
        //      title           iframe caption
        //  classes:
        //      iframe_get      use iframe to display url
        //      confirm         confirm operation
        //
        var target;
        if(!(target = $(this).attr('url')) && !(target = $(this).attr('href'))){
            return  false;
        }

        var that = this;
        if ( $(this).hasClass('confirm') ) {
            if(!confirm('确认要执行该操作吗?')){
                return false;
            }
        }

        __thinkbox_iframe.navigate(target).show($(this).attr('title'));
        return false;
    });

    //iframe post请求
    $('.iframe-post').click(function(){
        //
        //  attrs:
        //      url             http url
        //      title           iframe caption
        //      target-form     items' class name
        //      hide-data       items not required
        //  classes:
        //      iframe_post     use iframe to display posted data
        //      confirm         confirm operation
        //
        var that = this;
        var target;
        if(!(target = $(this).attr('url')) && !(target = $(this).attr('href'))){
            return  false;
        }

        var query;
        var nead_confirm= false;
        var target_form = $(this).attr('target-form');
        var form        = $('.'+target_form);

        if ($(this).attr('hide-data') === 'true'){
            //无数据时也可以使用的功能
            form = $('.hide-data');
            query = form.serialize();
        }else if (form.get(0)==undefined){
            return false;
        }else if ( form.get(0).nodeName=='FORM' ){
            if ( $(this).hasClass('confirm') ) {
                if(!confirm('确认要执行该操作吗?')){
                    return false;
                }
            }
            if($(this).attr('url') !== undefined){
                target = $(this).attr('url');
            }else{
                target = form.get(0).action;
            }
            query = form.serialize();
        }else if( form.get(0).nodeName=='INPUT' || form.get(0).nodeName=='SELECT' || form.get(0).nodeName=='TEXTAREA') {
            form.each(function(k,v){
                if(v.type=='checkbox' && v.checked==true){
                    nead_confirm = true;
                }
            })
            if ( nead_confirm && $(this).hasClass('confirm') ) {
                if(!confirm('确认要执行该操作吗?')){
                    return false;
                }
            }
            query = form.serialize();
        }else{
            if ( $(this).hasClass('confirm') ) {
                if(!confirm('确认要执行该操作吗?')){
                    return false;
                }
            }
            query = form.find('input,select,textarea').serialize();
        }

        if(query.length == 0 && $(this).attr('hide-data') != 'true'){
            updateAlert('请选择要操作的项');
            setTimeout('updateAlert("default")', 1500);
            return  false;
        }

        __thinkbox_iframe.post(target, query).show($(this).attr('title'));
        return  false;
    });
});
