//获取省市列表
    var locateBox = $('.wx_locate_box'),
        lcbox1 = $('#wx_locate_box1'),
        lcbox2 = $('#wx_locate_box2'),
        modTotal = $('.wx_locate_total'),
        wxAreaIdArr = wxAreaId ? wxAreaId.split(','):[],
        wxAreaValArr = wxAreaVal ? wxAreaVal.split(','):[],//储存原有人群ID数组wxAreaId,储存原有人群值数组wxAreaVal
        wxAreaInfo = wxAreaIdArr.length;
    //地区列表请求
    function getWxArea(id, isChecked,def) {
        var html = '';
        var post_data = {'type':'DISTRICT','region_id':id};
            $.ajax({
                type: "POST",
                url: '/v1.2/Materiald/gettargetregions',
                dataType:'json',
                data : post_data,
                async : false,
                beforeSend: function(){
                    tipTopShow('加载中...');
                },
                success: function(rs){
                    if(rs.api_code == 0){
                        tipTopHide();
                        $.each(rs.data.list,function(k,v){
                            var checked = false;
                            if(wxAreaInfo){
                                for(var j = 0; j < wxAreaIdArr.length; j++) {
                                    if(wxAreaIdArr[j] == v.id) {
                                        checked = true;
                                        wxAreaIdArr.splice(j, 1);
                                        wxAreaValArr.splice(j, 1);
                                        break;
                                    }
                                }
                            }
                            html +='<div class="wx_lists" id="b'+def+'_'+v.id+'" type="'+def+'" p_id="'+v.parent_id+'">'+v.name+'<em level="'+v.city_level+'" class="checkbox ' + (isChecked ? 'chose' : '') +(checked ? ' chose' : '') + '"></em></div>';
                            if(checked || isChecked){
                                setWxAreaVal(v.id,v.city_level,v.name,def);
                            }
                        })
                    }else{
                        tipTopShow('获取失败！');
                    }
                },
                error:function(rs){
                    tipTopShow("操作失败请重试！");
                }
            });
        return html;
    }
    //城市列表请求省、市
    function getWxCityTotal(){
        var data = '';
        var post_data = {'type':'REGION'};
        $.ajax({
            type: "POST",
            url: '/v1.2/Materiald/getfriendtargetregions',
            dataType:'json',
            data : post_data,
            async : false,
            success: function(rs){
                if(rs.api_code == 0){
                    tipTopHide();
                    data = rs.data;
                }
            },
            error:function(rs){
                tipTopShow("操作失败请重试！");
            }
        });
        return data;
    }
    //城市列表请求
    function getWxCity(type,cityData,def) {
        var html = '';
        $.each(cityData[type],function(k,v){
            var checked = false;
            if(wxAreaInfo){
                for(var j = 0; j < wxAreaIdArr.length; j++) {
                    if(wxAreaIdArr[j] == v.id) {
                        checked = true;
                        wxAreaIdArr.splice(j, 1);
                        wxAreaValArr.splice(j, 1);
                        break;
                    }
                }
            }
            html +='<div class="child_box"><div class="wx_clist wx_lists" id="b'+def+'_'+v.id+'" type="'+def+'"><i></i>'+v.name+'<em level="'+ type +'" class="checkbox ' + (checked ? ' chose' : '') + '"></em></div></div>';
           /* if(checked){
                setWxAreaVal(v.id,type,v.name,def);
            }*/
        })
        return html;
    }
    //获取省市列表
    function getWxRegion() {
        var cityData = getWxCityTotal();
        var regionType = {'1':['CITY_LEVEL_CORE','核心城市'],'2':['CITY_LEVEL_IMPORTANT','重点城市'],'3':['CITY_LEVEL_OTHER','其他城市']};
        var html1 = '',html2 = '';
        for(var i in regionType){
            var data1 = getWxCity(regionType[i][0],cityData,1);
            var data2 = getWxCity(regionType[i][0],cityData,2);
            html1 +='<div class="list_box"><div class="wx_plist wx_lists" type="'+regionType[i][0]+'"><i></i>'+regionType[i][1]+'</div><div class="child_list" pid="' + regionType[i][0] + '" style="display:none">'+ data1 +'</div></div>';
            html2 +='<div class="list_box"><div class="wx_plist wx_lists" type="'+regionType[i][0]+'"><i></i>'+regionType[i][1]+'</div><div class="child_list" pid="' + regionType[i][0] + '" style="display:none">'+ data2 +'</div></div>';
        }
        lcbox1.html(html1);
        lcbox2.html(html2);
        //locateBox.html(html);
    }
    getWxRegion();

    //选择省市标签
    locateBox.on('click','.wx_plist', function(e){ //点击省份获取城市列表
        var $th = $(this),
            tagId = $th.attr('id'),
            type = $th.attr('type'),
            isChecked = $th.find('.chose').length > 0,
            childList = $th.parent().find('.child_list');
       if($(this).hasClass('cur')){
            $(this).removeClass('cur');
            childList.hide();
        }else{
            $(this).addClass('cur').parent().siblings('div').find('.wx_plist').removeClass('cur');
            $(this).addClass('cur').parent().siblings('div').find('.child_list').hide();
            childList.show();
        }
    }).on('click','.wx_clist', function(e){ //点击城市获取区列表
        var tagId = $(this).attr('id').split('_')[1],
            tagTy = $(this).attr('type'),
            child = $(this).parent().find('.wx_alist'),
            isChecked = $(this).find('.chose').length > 0;
        if(child.length > 0) {
            child.show();
        }else {
            var data = getWxArea(tagId,isChecked,tagTy);
            $(this).parent().append('<div class="wx_alist" pid="' + tagId + '">'+ data +'</div>');
            var childBox3 = $('.child_list').find('[pid="' + tagId + '"]').find('.wx_lists'), //改变城市的选择状态
                childChose3 = childBox3.find('.chose');
            if(!isChecked && childChose3.length>0){
                if(childBox3.length == childChose3.length){
                    $(this).find('.checkbox').addClass('chose');
                }else{
                    $(this).find('.checkbox').addClass('childchose');
                }
            }
        }
        if($(this).hasClass('cur')){
            $(this).removeClass('cur');
            child.hide();
        }else{
            $(this).addClass('cur').parent().siblings('div').find('.wx_clist').removeClass('cur');
            $(this).addClass('cur').parent().siblings('div').find('.wx_alist').hide();
            child.show();
        }
    }).on('click', '.wx_alist .checkbox', function(e) { //选中或取消区
        e.stopPropagation();
        var t = $(this),
            defType = t.closest('.wx_region').attr('dataType'),
            level = t.attr('level'),
            alist = t.closest('.wx_lists'),
            id = alist.attr('id').split('_')[1];
            cid = alist.parent().attr('pid');
            pid = alist.parents('.child_list').attr('pid'),
            clist = alist.parents('.child_box').find('.wx_clist'),
            aSize = alist.parent().find('.wx_lists').length,
            clistAll = locateBox.find('.child_list[pid="' + pid + '"]').find('.wx_clist'),
            cSize = clistAll.length,
            cCheckbox = clist.children('.checkbox'),
            pCheckbox = alist.parents('.list_box').find('#b'+defType+'_'+ pid).children('.checkbox');
        if(t.hasClass('chose')) {
            t.removeClass('chose');
            cCheckbox.removeClass('chose');
            if(alist.parent().find('.chose').length == 0) {
                cCheckbox.removeClass('childchose');
            }else {
                cCheckbox.addClass('childchose');
            }
            pCheckbox.removeClass('chose');

            if(clistAll.find('.chose').length == 0) {
                if(clistAll.find('.childchose').length == 0) {
                    pCheckbox.removeClass('childchose');
                }else {
                    pCheckbox.addClass('childchose');
                }
            }else {
                pCheckbox.addClass('childchose');
            }
            alist.parent().find('.wx_lists').each(function(){
                var alistId = $(this).attr('id').split('_')[1];
                var hsChose = $('#b'+defType+'_'+alistId).children('.checkbox');
                if(hsChose.hasClass('chose')){
                    setWxAreaVal(alistId,level,'',defType);
                }
            })
            alist.parents('.child_list').find('.wx_clist').each(function(){
                var clistId = $(this).attr('id').split('_')[1];
                var hsChose = $('#b'+defType+'_'+clistId).children('.checkbox');
                if(hsChose.hasClass('chose')){
                    setWxAreaVal(clistId,level,'',defType);
                }
            })
            removeWxAreaVal(pid,defType);
            removeWxAreaVal(cid,defType);
            removeWxAreaVal(id,defType);
        }else {
            t.addClass('chose');
            if(alist.parent().find('.chose').length == aSize) {
                cCheckbox.addClass('chose').removeClass('childchose');
                alist.parent().find('.wx_lists').each(function(){
                    var alistId = $(this).attr('id').split('_')[1];
                    removeWxAreaVal(alistId,defType);
                })
                setWxAreaVal(cid,level,'',defType);
            }else {
                cCheckbox.addClass('childchose');
                setWxAreaVal(id,level,'',defType);
            }
        }
    }).on('click', '.wx_clist .checkbox', function(e) { //选中或取消城市
        e.stopPropagation();
        var t = $(this),
            defType = t.closest('.wx_region').attr('dataType'),
            level = t.attr('level'),
            clist = t.closest('.wx_clist'),//当前城市
            cid = clist.attr('id').split('_')[1],
            clistClub = clist.parents('.child_list').find('.wx_clist'),//所有城市
            cSize = clistClub.length,//所有城市列表
            pid = clist.parents('.child_list').attr('pid'),//取一级城市类别
            aCheckbox = clist.parent().find('.wx_alist').find('.checkbox'), //区选择框
            pCheckbox = clist.parents('.list_box').find('#b'+defType+'_'+ pid).children('.checkbox');//一级城市选择框
        if(t.hasClass('chose')) {
            t.removeClass('chose childchose');
            aCheckbox.removeClass('chose');
            $('.wx_alist .wx_lists[p_id="' + cid + '"]').each(function() { //区值移除
                var alistId = $(this).attr('id').split('_')[1];
                removeWxAreaVal(alistId,defType);
            })
            clistClub.each(function(){
                var clistId = $(this).attr('id').split('_')[1];
                var hsChose = $('#b'+defType+'_'+clistId).children('.checkbox');
                if(hsChose.hasClass('chose')){
                    setWxAreaVal(clistId,level,'',defType);
                }
            })
            removeWxAreaVal(cid,defType);
        }else {
            t.removeClass('childchose').addClass('chose');
            aCheckbox.addClass('chose');
            $('.wx_alist .wx_lists[p_id="' + cid + '"]').each(function() { //区值移除
                var alistId = $(this).attr('id').split('_')[1];
                removeWxAreaVal(alistId,defType);
            })
            setWxAreaVal(cid,level,'',defType);
        }
    })

    //设置区域值
    function setWxAreaVal(id,level,nm,def){
        var liId = $('#b'+def+'_'+id);
        var showBox = liId.closest('.wx_region').find('.wx_area');
        var idDom = showBox.find('.label'+id);
        var totalDom = $('.wx_region[dataType='+def+']').find('.wx_locate_total');
        var levelLgh = totalDom.find('.wx_area span[level="'+level+'"]').length;
        $('#region_level'+def).val(level);
        if(idDom.length==0){
            if(levelLgh==0){
                cancelWxAllArea(def);
            }
            totalDom.show();
            if(nm){
                var html ='<span class="label'+ id+'" _id="'+ id+'" level="'+level+'">'+ nm +'<i class="delAreaVal" _id="'+ id+'"></i></span>';
            }else{
                var name = liId.text()
                var html ='<span class="label'+ id+'" _id="'+ id+'" level="'+level+'">'+ name +'<i class="delAreaVal" _id="'+ id+'"></i></span>';
            }
            totalDom.find('.wx_area').append(html);
        }
    }
     //删除区域值
    function removeWxAreaVal(id,def){
        var liId = $('#b'+def+'_'+id);
        var showBox = liId.closest('.wx_region').find('.wx_area');
        var totalDom = $('.wx_region[dataType='+def+']').find('.wx_locate_total');
        showBox.find('.label'+id).remove();
        if(showBox.children().length === 0){
            totalDom.hide();
        }else{
            totalDom.show();
        }
    }
    //清除所有区域值
    function cancelWxAllArea(def){
        $('.wx_region[dataType='+def+']').find('.wx_area span').each(function(){
            var id = $(this).attr('_id');
            var liId = $('#b'+def+'_'+id);
            var val = $(this).text();
            var showBox = $(this).closest('.wx_area');
            $('.label'+ id).remove();
            liId.find('.checkbox').click();
            if(wxAreaInfo){
                for(var j = 0; j < wxAreaIdArr.length; j++) {
                    if(wxAreaIdArr[j] == id) {
                        wxAreaIdArr.splice(j, 1);
                        wxAreaValArr.splice(j, 1);
                    }
                }
            }
        })
    }
    modTotal.on('click','.delAreaVal',function(){
        var th = $(this),
            id = th.attr('_id'),
            def = th.closest('.wx_region').attr('datatype'),
            liId = $('#b'+def+'_'+id),
            showBox = liId.closest('.wx_region').find('.wx_area'),
            totalDom = th.closest('.wx_locate_total');
        showBox.find('.label'+ id).remove();
        liId.find('.checkbox').click();
        if(wxAreaInfo){
            for(var j = 0; j < wxAreaIdArr.length; j++) {
                if(wxAreaIdArr[j] == id) {
                    wxAreaIdArr.splice(j, 1);
                    wxAreaValArr.splice(j, 1);
                }
            }
        }
        if(showBox.children().length === 0){
            totalDom.hide();
        }else{
            totalDom.show();
        }
    })
    //获取微信朋友圈所选择地区
    function getWxResult(type){
        var areaId = [],areaVal = [],areaArr=[];
        $('.wx_region[dataType='+type+']').find('.wx_area span').each(function() {
            var id = $(this).attr('_id');
            var name = $(this).text();
            areaId.push(id);
            areaVal.push(name);
        })
        areaArr[0] = areaId;
        areaArr[1] = areaVal;
        return areaArr;
    }
