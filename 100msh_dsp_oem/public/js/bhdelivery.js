var pList = $('#pro_list'),//步骤2
    cList = $('#city_list'),
    crowdClub = $('#crowd_club');
var crowdDataArr = []; //城市数组初始化
var crowdValArr = []; //城市数组值初始化
if(crowdDataArr.length!=0){
    crowdClub.show(); //显示选中城市
    for(var i = 0; i < crowdDataArr.length; i++) {
        setAreaVal(crowdDataArr[i],0,crowdValArr[i]);
    }
}

//获取省市列表
function getBhCity(cityId,isChecked){
    var post_data = {'pid':cityId};
    $.ajax({
        type: "POST",
        url: '/v1.2/BhDelivery/getBhCity',
        dataType:'json',
        data : post_data,
        async : false,
        success: function(rs){
            if(rs.api_code == 1){
                var html = '';
                var data = rs.data;
                /*if(data.length){
                    pList.html('');
                    cList.hide().html('');
                    crowdClub.hide();
                    $('.label_list').children().remove()
                }*/
                var reVal = ['北京','天津','上海','重庆','台湾','香港','澳门'];//直辖市 ID重复
                var listId='';
                $.each(data,function(k,v){
                    var checked = false;
                    for(var j = 0; j < crowdDataArr.length; j++) {
                         if(crowdDataArr[j] == v.id) {
                            checked = true;
                            crowdDataArr.splice(j, 1);
                            crowdValArr.splice(j, 1);
                            break;
                         }
                    }
                    listId = v.bh_id;
                    for(var k = 0;k < reVal.length;k++){
                        if((reVal[k] == v.name) && cityId!=0){
                            listId = 'reid_'+v.bh_id;
                        }
                    }
                    html +='<li class="lists" id="'+listId+'"><em class="checkbox ' + (isChecked ? 'chose' : '') +(checked ? ' chose' : '') + '"></em>'+v.name+'</li>';
                    if(cityId==0){
                        pList.show().html('<ul class="plist">'+ html +'</ul>');
                    }
                })
                if(cityId!=0){
                    cList.append('<ul class="plist" pid="' + cityId + '">'+ html +'</ul>');
                }
            }else{
                tipTopShow('获取失败！');
            }
        },
        error:function(rs){
            tipTopShow("操作失败请重试！");
        }
    });
}
//设置区域值
function setAreaVal(id,pid,nm){
    var showBox = crowdClub.find('.label_list');
    var idDom = showBox.find('#label'+id);
    var clearBtn = '<a href="javascript:;" class="clear_btn" onclick="clearArea()">清空</a>'
    if(idDom.length==0){
        crowdClub.show();
        if(nm){
            var html ='<span id="label'+ id+'" _id="'+id+'" _pid="'+pid+'">'+ nm +'<i class="delAreaVal" _id="'+ id+'"></i></span>';
        }else{
            var name = $('.crowd_area').find('#'+id).text()
            var html ='<span id="label'+ id+'" _id="'+id+'" _pid="'+pid+'">'+ name +'<i class="delAreaVal" _id="'+ id+'"></i></span>';
        }
        if($('.clear_btn').length !=0){
            $('.clear_btn').remove();
        }
        crowdClub.find('.label_list').append(html).append(clearBtn);
    }
}
//删除区域值
function removeAreaVal(id){
    var showBox = crowdClub.find('.label_list');
    showBox.find('#label'+id).remove();
    if(showBox.find('span').length === 0){
        crowdClub.hide();
        $('.clear_btn').remove();
    }else{
        crowdClub.show();
    }
}
//清空区域值选项
function clearArea(){
    var clearId = getRegion()[0];
    for(var i=0; i<clearId.length;i++){
        removeAreaVal(clearId[i]);
        $('#'+ clearId[i]).find('.checkbox').click();
    }
}

//区域值清除
crowdClub.on('click','.delAreaVal',function(){
    var id = $(this).attr('_id');
    var showBox = crowdClub.find('.label_list');
    $('#label'+ id).remove();
    $('#'+ id).find('.checkbox').click();
    if(crowdDataArr.length!=0){
        for(var j = 0; j < crowdDataArr.length; j++) {
            if(crowdDataArr[j] == id) {
                crowdDataArr.splice(j, 1);
                crowdValArr.splice(j, 1);
            }
        }
    }
    if(showBox.children().length === 0){
        crowdClub.hide();
    }else{
        crowdClub.show();
    }
})

//选择省市标签
$('body').on('click','#pro_list li', function(e){ //点击省份获取城市列表
    var tagId = $(this).attr('id'),
        ulDom = cList.find('ul[pid="' + tagId + '"]'),
        isChecked = $(this).find('.chose').length > 0;
    cList.show();
    $(this).addClass('cur').siblings('li').removeClass('cur');
    if(ulDom.length > 0) {
        ulDom.show().siblings('ul').hide();
    }else {
        cList.find('ul').hide();
        getBhCity(tagId,isChecked);
    }
}).on('click', '#city_list .checkbox', function(e) { //选中或取消城市
    e.stopPropagation();
    var t = $(this),
        id = t.parent().attr('id'),
        plist = t.closest('.plist'),
        pSize = plist.children().length,
        pid = plist.attr('pid'),
        pCheckbox = pList.find('#' + pid).children('.checkbox');
    if(t.hasClass('chose')) {
        t.removeClass('chose childchose');
        pCheckbox.removeClass('chose');
        if(plist.find('.chose').length == 0) {
            if(plist.find('.childchose').length == 0) {
                pCheckbox.removeClass('childchose');
            }else {
                pCheckbox.addClass('childchose');
            }
        }else {
            pCheckbox.addClass('childchose');
        }
        plist.find('.lists').each(function(){
            var clistId = $(this).attr('id');
            var hsChose = $('#'+clistId).children('.checkbox');
            if(hsChose.hasClass('chose')){
                setAreaVal(clistId,pid);
            }
        })
        removeAreaVal(id);
        removeAreaVal(pid);
    }else {
        t.removeClass('childchose').addClass('chose');
        if(plist.find('.chose').length == pSize) {
            pCheckbox.addClass('chose').removeClass('childchose');
            plist.find('.lists').each(function(){
                var clistId = $(this).attr('id');
                removeAreaVal(clistId);
            })
            setAreaVal(pid,0);
        }else {
            pCheckbox.addClass('childchose')
            setAreaVal(id,pid);
        }
    }
}).on('click', '#pro_list .checkbox', function(e) { //选中或取消省份
    e.stopPropagation();
    var t = $(this),
        id = t.parent().attr('id'),
        plist = t.closest('.plist'),
        cCheckbox = cList.find('[pid="' + id + '"]').find('.checkbox');

    if(t.hasClass('chose')) {
        t.removeClass('chose childchose');
        cCheckbox.removeClass('chose');
        removeAreaVal(id);
    }else {
        t.removeClass('childchose').addClass('chose');
        cCheckbox.addClass('chose').removeClass('childchose');
        setAreaVal(id,0);
    }
    cList.find('[pid="' + id + '"]').find('.lists').find('.chose').each(function() { //市值移除
        removeAreaVal($(this).parent().attr('id'));
    })
})
//取选中的城市id
function getRegion(){
    var areaId =[],areaVal = [],areaArr=[];
    $('#crowd_club .label_list').find('span').each(function() {
        var id = $(this).attr('_id');
        var name = $(this).text();
        areaId.push(id);
        areaVal.push(name);
    })
    areaArr[0] = areaId.concat(crowdDataArr);
    areaArr[1] = areaVal.concat(crowdValArr);
    return areaArr;
}
//取选中的城市id Json
function getRegionJson(){
    var areaId =[],areaPid =[],areaJson ={},areaVal = [],areaArr=[];
    $('#crowd_club .label_list').find('span').each(function() {
        var id = $(this).attr('_id');
        var pid = $(this).attr('_pid');
        var name = $(this).text();
        if(pid==0){
            areaJson[id] = [];
        }else{
            areaId.push(id);
            areaJson[pid] = areaId;
        }
        areaVal.push(name);
    })
    areaArr = {'areaName':areaVal.join(),'data':areaJson}
    return areaArr;
}

/**
 * 商圈选择
 */

 //商圈选择
function getBhZoneList(cityId){
    var postData ={'type':'2','cityId':cityId};
    $.ajax({
        type: "POST",
        url: '/v1.2/BhDelivery/getBhCityArea',
        data: postData,
        dataType:'json',
        success: function(rs){
            if(rs.api_code == 1){
                var data= rs.data, html='',
                    ulDiv = $('<div class="zbox zoneId'+cityId+'"></div>');
                $('.zone_mod').show();
                $.each(data,function(k,v){
                    html +='<li class="lists" id="'+v.bh_area_id+'"><em class="checkbox"></em>'+v.name+'</li>';
                    var areaStr='';
                    $.each(v.area_data,function(n,m){
                        areaStr += '<li class="item" id="'+m.bh_area_id+'" cityId="'+m.cityId+'" radius="'+m.radius+'" latitude="'+m.latitude+'" longitude="'+m.longitude+'"><em class="checkbox"></em>'+m.name+'</li>';
                    })
                    ulDiv.append('<ul pid="' + v.bh_area_id + '" cityId="' + cityId + '">'+ areaStr +'</ul>');
                })
                $('#first_list').show().append('<ul class="zbox zoneId'+cityId+'">'+ html +'</ul>');
                $('#second_list').append(ulDiv);
                setTimeout(function(){
                    $('#first_list li').eq(0).click();
                },100)
            }
        },
        error:function(rs){
            layui.layer.msg("查询信息失败，请重试...");
        }
    });
}

//选择商圈标签
$('body').on('click','#first_list li', function(e){ //点击省份获取城市列表
    var fistId = $(this).attr('id'),
        secDom = $('#second_list'),
        secUl = secDom.find('ul[pid="' + fistId + '"]');
    secDom.show();
    $(this).addClass('cur').siblings('li').removeClass('cur');
    secUl.show().siblings('ul').hide()
}).on('click', '#second_list .checkbox', function(e) { //选中或取消城市
    e.stopPropagation();
    var t = $(this),
        id = t.parent().attr('id'),
        labelLeg = $('#sq_club span').length,
        latitude = t.parent().attr('latitude'),
        longitude = t.parent().attr('longitude'),
        radius = t.parent().attr('radius'),
        pid = t.parent().parent().attr('pid'),
        cityId = t.parent().parent().attr('cityId');
    if(t.hasClass('chose')) {
        t.removeClass('chose');
        removeZoneVal(id);
    }else {
        if(parseInt(labelLeg)>9){
            tipTopShow("最多只能选择10个！");
            return;
        }
        t.addClass('chose');
        setZoneVal(id,pid,cityId,latitude,longitude,radius);
    }
})

//设置区域值
function setZoneVal(id,pid,cityId,latitude,longitude,radius){
    var sqClub = $('#sq_club'),
        showBox = sqClub.find('.label_list'),
        idDom = showBox.find('#label'+id);
    if(idDom.length==0){
        sqClub.show();
        var name = $('#second_list').find('#'+id).text()
        var html ='<span id="label'+ id+'" _id="'+id+'" _pid="'+pid+'" cityId="'+cityId+'" latitude="'+latitude+'" longitude="'+longitude+'" radius="'+radius+'">'+ name +'<i class="delZoneVal" _id="'+ id+'"></i></span>';
        sqClub.find('.label_list').append(html);
    }
}
//删除区域值
function removeZoneVal(id){
    var sqClub = $('#sq_club'),
        showBox = sqClub.find('.label_list');
    showBox.find('#label'+id).remove();
    if(showBox.find('span').length === 0){
        sqClub.hide();
    }else{
        sqClub.show();
    }
}
//区域值清除
$('#sq_club').on('click','.delZoneVal',function(){
    var sqClub = $('#sq_club');
    var id = $(this).attr('_id');
    var showBox = sqClub.find('.label_list');
    $('#label'+ id).remove();
    $('#'+ id).find('.checkbox').click();
})

//取选中的商圈id
function getZone(){
    var zoneId =[],zoneVal = [],zoneArr=[];
    $('#sq_club .label_list').find('span').each(function() {
        var id = $(this).attr('_id');
        var name = $(this).text();
        zoneId.push(id);
        zoneVal.push(name);
    })
    zoneArr[0] = zoneId;
    zoneArr[1] = zoneVal;
    return zoneArr;
}
//取选中的商圈id json
function getZoneJson(){
    var cityJson={};
    $('#sq_club .label_list').find('span').each(function(k,v) {
        var th = $(this),
            id = th.attr('_id'),
            pid = th.attr('_pid'),
            name = th.text(),
            cityId = th.attr('cityId'),
            latitude = th.attr('latitude'),
            longitude = th.attr('longitude'),
            radius = th.attr('radius');

        if (cityJson[cityId] === undefined) {
          cityJson[cityId] = {};
        }

        if (cityJson[cityId][pid] === undefined) {
          cityJson[cityId][pid] = [];
        }
        cityJson[cityId][pid].push({locationId: id, name: name, latitude: latitude, longitude:longitude,radius:radius})
    })
    return cityJson;
}