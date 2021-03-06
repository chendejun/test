//进入新增投放页面三种情况：1、直接进入新增、2选择素材新增、3重新新增（查询原有投放数据）,4广告主投放
//进入编辑投放页面情况：1、带原有投放ID

var addSt1 = sessionStorage.getItem('addSt1') ? JSON.parse(sessionStorage.getItem('addSt1')) : '', //取新增投放第一步存储内容
    addSt2 = sessionStorage.getItem('addSt2') ? JSON.parse(sessionStorage.getItem('addSt2')) : '', //取新增投放第二步存储内容
    groupName = sessionStorage.getItem('groupName');//取存储素材类型内容

var idSet = {};
    idSet.adId = sessionStorage.getItem('adInfoId') ? sessionStorage.getItem('adInfoId') :'';
    idSet.matId = sessionStorage.getItem('matId') ? sessionStorage.getItem('matId') :'';
    idSet.compId = sessionStorage.getItem('compId') ? sessionStorage.getItem('compId') :'';
    idSet.compName = sessionStorage.getItem('compName') ? sessionStorage.getItem('compName') :'';//广告主名字
if(pageType == 1){
    var domName = $("#name"),
        domAdUser = $('#ad_user'),
        domMoney = $('#money'),
        domBalance = $("#balance"),
        domBudget = $("#budget"),
        infoData = '';
}
if(pageType == 2){
    var pList = $('#province_list'),
        cList = $('#city_list'),
        aList = $('#area_list'),
        inputMad = $('#matId'),
        inputPerson = $('#personNo'),
        crowdType =$('input[name=crowdtype]'),
        readData ='',
        packId = $('#pack_id'),
        boxCrow1 = $('.box_crow1'),
        boxCrow2 = $('.box_crow2'),
        crowdDataArr,//储存原有人群ID数组
        idArr=[];//人群定义数组添加选中checkbox的id元素
}
if(pageType == 3){
    var addSt3 = sessionStorage.getItem('addSt3') ? JSON.parse(sessionStorage.getItem('addSt3')) : '', //取新增投放第三步存储内容
        domSdText = $("#sd_text"),
        domWay = $("#way"),
        domEdText = $("#ed_text"),
        domWayTip = $('#way_tip'),
        domCharge = $("#charge"),
        domTimeAear1 = $("#timeAear1"),
        domTimeAear2 = $("#timeAear2"),
        tipRow = $(".tip_row"),
        tipSpan = $(".tip_span"),
        inputChioceTime = $('input[name=chioceTime]'),
        inputAdtype = $('input[name=adtype]'),
        ad_type = $("input[name=adtype]:checked").val();
}
$(function(){
    if(idSet.compId && pageType ==1){
        domAdUser.attr('_id',idSet.compId);
        getUseMoney(idSet.compId);
    }
    //获取素材ID新增投放
    if(idSet.matId && pageType ==2){
        inputMad.val(idSet.matId);
    }
    if(addSt1 && pageType ==1){ //存储数据渲染
        readInfo(addSt1);
    }else if(addSt2 && pageType ==2){
        readInfo(addSt2);
    }else if(addSt3 && pageType ==3){
       readInfo(addSt3);
    }else if(idSet.adId){//获取重新投放ID详情
        getDeliveryInfo();
        deliveryInfo = sessionStorage.getItem('deliveryInfo') ? sessionStorage.getItem('deliveryInfo') : ''; //读取详情
        infoData = deliveryInfo ?  JSON.parse(sessionStorage.getItem('deliveryInfo')):'';
    }

    //获取广告主信息
    var getListTimer;
    $(document).on('focus','#ad_user', function(e){
        $('#user_list').show();
    })
    $(document).on('click',function(e){
        if(e.target.id == 'user_list' || e.target.id == 'ad_user'){
            return;
        }
        $('#user_list').hide();
    })
    $('body').on('input click','#ad_user',function(e){
        var keyword = $(this).val() ? $(this).val():'';
        getListTimer && clearTimeout(getListTimer);
        getListTimer = setTimeout(function(){
            getUserList(keyword,function(res){
                var html ='';
                if(!res.list){
                    $('#user_list').html('');
                    return;
                }
                 $.each(res.list,function(k,v){
                    html +='<li class="ulists" _id="'+v.comp_id+'" _amount="'+Number(parseFloat(v.cash_amount)+parseFloat(v.give_amount)+parseFloat(v.virtual_amount)) +'">'+v.comp_name+'</li>'
                });
                $('#user_list').html('<ul>'+html+'</ul>');
            });
        })
    })
    $('body').on('click','.ulists',function(e){
        var th = $(this);
        var amount = th.attr("_amount");
        domAdUser.val(th.text()).attr('_id',th.attr("_id")).attr('_amount',amount);
        domBalance.html(amount);
        $('#user_list').hide();
    })
    //获取广告主列表
    function getUserList (keyword,fn){
        $.ajax({
            type: "GET",
            url: "/v1.2/advertiser/list/name/"+keyword,
            dataType:'json',
            success: function(rs){
                var data = rs.data;
                if(rs.state == 1){
                    fn.call(this,data);
                }else{
                    $('#user_list').html('');
                }
            },
            error:function(rs){
                tipTopShow("操作失败请重试！");
            }
        });
    }
});
//获取人群包信息
var getListTimer,packBox = $('#pack_box');
$(document).on('focus','#pack_id', function(e){
   packBox.show();
})
$(document).on('click',function(e){
    if(e.target.id == 'pack_box' || e.target.id == 'pack_id'){
        return;
    }
   packBox.hide();
})
$('body').on('input click','#pack_id',function(e){
    var keyword = $(this).val() ? $(this).val():'';
    getListTimer && clearTimeout(getListTimer);
    getListTimer = setTimeout(function(){
        getPackList(keyword,function(res){
            var html ='';
            if(!res){
               packBox.html('');
                return;
            }
             $.each(res,function(k,v){
                html +='<li class="pack_item" _id="'+v.crowd_id+'">'+v.crowd_name+'</li>'
            });
           packBox.html('<ul>'+html+'</ul>');
        });
    })
})
$('body').on('click','.pack_item',function(e){
    var th = $(this);
    packId.val(th.text()).attr('_id',th.attr("_id"));
    packBox.hide();
    getPackInfo(th.attr("_id"));
})
//获取包列表
function getPackList (keyword,fn){
    $.ajax({
        type: "GET",
        url: "/delivery/getCrowList/keyword/"+keyword,
        dataType:'json',
        success: function(rs){
            var data = rs.data;
            if(rs.state == 1){
                fn.call(this,data);
            }else{
               packBox.html('');
            }
        },
        error:function(rs){
            tipTopShow("操作失败请重试！");
        }
    });
}
//根据人群包id获取详情
function getPackInfo(id){
    $.ajax({
        type: "GET",
        url: '/v1.2/ncrowd/detail/crowd_id/'+id,
        dataType:'json',
        success: function(rs){
            if(rs.state == 1){
                var data= rs.data,list_e = $("#pack_list tbody"),str="";
                packId.val(data.crowd_name);
                $('#bag_name').html(data.crowd_name);
                $('#bag_time').html(data.add_time);
                if(data.tag_list.length == 0){
                    list_e.html("");
                    return ;
                }
                $.each(data.tag_list,function(k,v){
                    str +='<tr>';
                    str +='<td>'+ parseInt(k+1) +'</td>';
                    str +='<td>'+ v +'</td>';
                    str +='</tr>';
                });
                list_e.html(str);
            }
        }
    });
}
//获取广告详情
function getDeliveryInfo(){
    $.ajax({
        type: "GET",
        url: '/delivery/getDeliveryInfo/id/'+idSet.adId,
        dataType:'json',
        success: function(rs){
            if(rs.state == 1){
                var data = rs.data;
                readInfo(data);
                sessionStorage.setItem('deliveryInfo',JSON.stringify(data));//存储详情
                if(pageType ==1){
                    sessionStorage.setItem('addSt1',JSON.stringify(data));//存储详情
                    sessionStorage.setItem('addSt2',JSON.stringify(data));//存储详情
                    sessionStorage.setItem('groupName',data.adgroup_name);//存储详情
                    if(addType){
                        domMoney.val('');
                        domBudget.val('');
                    }
                }
                if(pageType ==3){
                    sessionStorage.setItem('addSt3',JSON.stringify(data));//存储详情
                    if(addType && data.ad_type!=2 && groupName != '全屏'){
                        domCharge.val('');
                    }
                }
            }
        }
    });
}
//详情渲染
function readInfo(data){
    if(pageType ==1){
        domName.val(data.delivery_name);
        domAdUser.val(data.comp_name);
        domAdUser.attr("_id",data.comp_id);
        domMoney.val(data.z_money);
        domBudget.val(data.day_money);
        getUseMoney(data.comp_id);
    }
    if(pageType ==2){
        inputPerson.val(data.crowd_info);
        inputMad.val(data.material_id);
        inputMad.attr('name',data.adgroup_name);
        crowdData = data.crowd_info;
        packId.attr("_id",data.crowd_id);
        getPackInfo(data.crowd_id);
        if(data.crowd_type==1){
            boxCrow1.show();
            boxCrow2.hide();
            crowdType.eq(0).attr('checked',true).change();
        }
        if(data.crowd_type==2){
            boxCrow1.hide();
            boxCrow2.show();
            crowdType.eq(1).attr('checked',true).change();
        }
    }
    if(pageType ==3){
        var sTimer = data.delivery_start_time,
            eTimer = data.delivery_end_time,
            label_type = data.ad_type;
        domCharge.val(data.bid_money);
        if(groupName == '插屏'){
            changeInAdype(label_type);
        }else if(groupName == '全屏'){
            changeAuAdype();
            label_type = 2;
        }else{
            domWay.val(data.bid_type).attr('disabled',false);
        }
        var title = $("#way option:selected").attr("_title");
        domWayTip.html(title);
        if(sTimer=='00:00:00' && eTimer=='24:00:00'){
            inputChioceTime.eq(0).attr('checked',true);
        }else if(sTimer && eTimer){
            var sTimer = sTimer.split(':')[0]-10<0?'0'+sTimer.split(':')[0]:sTimer.split(':')[0],
                eTimer = eTimer.split(':')[0]-10<0?'0'+eTimer.split(':')[0]:eTimer.split(':')[0];
            inputChioceTime.eq(1).attr('checked',true);
            $('#customTimeBox').show();
            domTimeAear1.find("option[title='"+sTimer+"']").attr("selected",true);
            domTimeAear2.find("option[title='"+eTimer+"']").attr("selected",true);
        }else{
            inputChioceTime.eq(0).attr('checked',true);
        }
        if(label_type==2){
            tipRow.hide();
            tipSpan.show();
            inputAdtype.eq(1).attr('checked',true).change();
            domSdText.bind('focus',function(e){var ed=$dp.$('ed_text');WdatePicker({onpicked:function(){ed.focus();}, minDate:'%y-%M-{%d+2}', dateFmt:'yyyy-MM-dd',startDate:'%y-%M-{%d+2}',alwaysUseStartDate:true})});
            domCharge.attr('disabled',true);
        }else {
            tipRow.show();
            tipSpan.hide();
            inputAdtype.eq(0).attr('checked',true);
            domSdText.bind('focus',function(e){var ed=$dp.$('ed_text');WdatePicker({onpicked:function(){ed.focus();}, minDate:'%y-%M-%d', dateFmt:'yyyy-MM-dd',startDate:'%y-%M-%d',alwaysUseStartDate:true})});
        }
        domSdText.val(data.delivery_start_date);
        domEdText.val(data.delivery_end_date);
    }
}
//插屏 投放类型设置
function changeInAdype(type){
    if(type==2){
        domWay.val('1').attr('disabled',true);
    }else{
        domWay.val('2').attr('disabled',true);
    }
    var title = $("#way option:selected").attr("_title");
    domWayTip.html(title);
    domWay.change();
}

//排期与竞价投放的区别
$('body').on('click','.status_tip',function(){
    layer.tips(this.getAttribute('data'), this,{time:10000});
})
if(pageType == 2){
    //投放类型改变时选开始时间可选范围改变
    crowdType.bind("change",function(){
        if(this.value==1){
            boxCrow1.show();
            boxCrow2.hide();
        } else {
            boxCrow1.hide();
            boxCrow2.show();
        }
    })
}
if(pageType == 3){
    //设置CPM底价 底部(banner)是1，插屏是4.5，全屏是4.5
    var optionCpm = domWay.children('option[value=1]'),
        tipTitle = 'CPM每千人次曝光1元，加价0.01元起',
        tipCost = '1';
    if(groupName == '插屏'){
        tipTitle = 'CPM每千人次曝光4.5元，加价0.01元起';
        tipCost = '4.5';
    }else if(groupName == '全屏'){
        tipTitle = 'CPM每千人次曝光4.5元，加价0.01元起';
        tipCost = '4.5';
    }
    optionCpm.attr('_title',tipTitle).attr('_cost',tipCost);
    domWayTip.html($("#way option:selected").attr("_title"));
    //判断素材类型,1、插屏广告：竞价只能CPC，排期只能CPM； 2、全屏广告：竞价与排期都只能是CPM
    if(groupName == '插屏'){
        changeInAdype(ad_type);
    }
    if(groupName == '全屏'){
        changeAuAdype();
    }

    //初始化自定义时间
    reanderTimeAear();
    //投放开始日期初始化
    domSdText.bind('focus',function(e){var ed=$dp.$('ed_text');WdatePicker({onpicked:function(){ed.focus();}, minDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',startDate:'%y-%M-%d',alwaysUseStartDate:true})});
    domEdText.bind('focus',function(e){WdatePicker({minDate:'#F{$dp.$D(\'sd_text\')}',dateFmt:'yyyy-MM-dd',maxDate:'#F{$dp.$D(\'sd_text\',{M:1,d:-1})}'})});
    //投放时间段切换
    inputChioceTime.bind("change",function(){
        if(this.value == 1){
            $("#customTimeBox").show();
        }else{
            $("#customTimeBox").hide();
        }
    });

    //全屏 投放类型设置
    function changeAuAdype(){
        $("input[name=adtype]").eq(0).parent().hide();
        $("input[name=adtype]").eq(1).attr('checked',true);
        tipRow.hide();
        tipSpan.show();
        domWay.val('1').attr('disabled',true);
        domCharge.val('4.5').attr('disabled',true);
        var title = $("#way option:selected").attr("_title");
        domWayTip.html(title);
    }

    //自定义时间变化
    domTimeAear1.bind("change",function(){
        var k = this.value,str = "",v=domTimeAear1.val();
        for(var i = parseInt(k)+1; i<=24; i++){
            str += '<option value="'+ i +'" title="'+(i>9?i:'0'+i)+'">'+ i +' : 00</option>'
        }
        domTimeAear2.html(str);
        if(k < v){
            domTimeAear2.val(v);
        }
    });
    domTimeAear2.bind("change",function(){
        var k = this.value,str = "",v=domTimeAear1.val();
        for(var i = 0; i<k; i++){
            str += '<option value="'+ i +'" title="'+(i>9?i:'0'+i)+'">'+ i +' : 00</option>'
        }
        domTimeAear1.html(str);
        domTimeAear1.val(v);
    });
    //出价方式选择
    domWay.change(function(){
        var ad_type = $("input[name=adtype]:checked").val(),
            title = $("#way option:selected").attr("_title"),
            cost = $("#way option:selected").attr("_cost");
        domWayTip.html(title);
        if(ad_type==2){
            $('#charge').val(cost);
        }else{
            $('#charge').val();
        }
    })

    //投放类型改变时选开始时间可选范围改变
    $("input[name=adtype]").bind("change",function(){
        domSdText.val("");
        domEdText.val("");
        domSdText.unbind('focus');
        domWay.attr('disabled',false);
        if(this.value==1){ //竞价投放
            tipRow.show();
            tipSpan.hide();
            domSdText.bind('focus',function(e){var ed=$dp.$('ed_text');WdatePicker({onpicked:function(){ed.focus();}, minDate:'%y-%M-%d', dateFmt:'yyyy-MM-dd',startDate:'%y-%M-%d',alwaysUseStartDate:true})});
            $('#charge').val('').removeAttr('disabled',false);
            if(groupName == '插屏'){
                domWay.val('2').attr('disabled',true);
            }
            if(groupName == '全屏'){
                domWay.val('1').attr('disabled',true);
            }
        } else { //排期投放
            tipRow.hide();
            tipSpan.show();
            domSdText.bind('focus',function(e){var ed=$dp.$('ed_text');WdatePicker({onpicked:function(){ed.focus();}, minDate:'%y-%M-{%d+2}', dateFmt:'yyyy-MM-dd',startDate:'%y-%M-{%d+2}',alwaysUseStartDate:true})});
            if(groupName == '插屏'){
                domWay.val('1').attr('disabled',true);
            }
            if(groupName == '全屏'){
                domWay.val('1').attr('disabled',true);
            }
            $('#charge').val($("#way option:selected").attr("_cost")).attr('disabled',true);
        }
        domWayTip.html($("#way option:selected").attr("_title"));
    })
}

//渲染自定义时间
function reanderTimeAear(){
    var str1 = "",str2="";
    for(var i=0; i<=24; i++){
        str1 += i !=24 && '<option value="'+ i +'" title="'+(i>9?i:'0'+i)+'">'+ i +' : 00</option>'
        str2 += i !=0 && '<option value="'+ i +'" title="'+(i>9?i:'0'+i)+'">'+ i +' : 00</option>'
    }
    domTimeAear1.html(str1);
    domTimeAear2.html(str2);
}

//根据广告主id获取剩余金额
function getUseMoney(id){
    $.ajax({
        type: "GET",
        url: '/v1.2/delivery/getUseMoney/comp_id/'+id,
        dataType:'json',
        success: function(rs){
            if(rs.state == 1){
               domBalance.html(rs.usemoney);
               domAdUser.val(rs.name);
            }
        }
    });
}
//余额全部填入
function allIn(){
    var money = $("#balance").text();
    domMoney.val(money);
}
 //下一步
function next(type){
    if(pageType ==1){
        var rs = checkDataSt1();
        if(!rs.success){
            tipTopShow(rs.msg);
            return;
        }
        sessionStorage.setItem('addSt1',JSON.stringify(rs.data));//存储投放第一步人群与素材内容
        if(type == 2){
            location.href = "editst2";
        }else{
            location.href = "addst2";
        }
    }
    if(pageType == 2){
        var rs = checkDataSt2();
        if(!rs.success){
            tipTopShow(rs.msg);
            return;
        }
        sessionStorage.setItem('addSt2',JSON.stringify(rs.data));//存储投放第二步人群与素材内容
        if(type == 2){
            location.href = "editst3";
        }else{
            location.href = "addst3";
        }
    }
}
//上一步
function preStep(type){
    if(pageType ==2){
        if(type == 2){
            location.href = "edit";
        }else{
            location.href = "add";
        }
    }
    if(pageType ==3){
        var data = preDataSt3();
        sessionStorage.setItem('addSt3',JSON.stringify(data));//存储新增投放第三步表单内容
        if(type == 2){
            location.href = "editst2";
        }else{
            location.href = "addst2";
        }
    }
}
//校验数据
function checkDataSt1(){
    var name = domName.val(),
        ad = domAdUser.val(),
        comp_id = domAdUser.attr("_id"),
        money = domMoney.val(),
        balance = domBalance.html(),
        budget = domBudget.val();
    if(!name){
        return {"success":false,msg:"请输入广告名称"};
    }
    if(!ad){
        return {"success":false,msg:"请选择广告主"};
    }
    if(!comp_id){
        return {"success":false,msg:"请选择正确广告主"};
    }
    if(!money){
        return {"success":false,msg:"请输入总预算"};
    }
    if(!(/^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/.test(money))){
        return {"success":false,msg:"请输入正确总预算金额！"};
    }
    if(parseFloat(money)<50){
        return {"success":false,msg:"总预算应大于等于50元！"};
    }
    if(parseFloat(balance)<50){
        return {"success":false,msg:"剩余金额不足50元！"};
    }
    if(parseFloat(money) > parseFloat(balance)){
        return {"success":false,msg:"总预算不能大于剩余金额！"};
    }
    if(!budget){
        return {"success":false,msg:"请输入日预算"};
    }
    if(!(/^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/.test(budget))){
        return {"success":false,msg:"请输入正确日预算金额！"};
    }
    if(parseFloat(budget)<50){
        return {"success":false,msg:"日预算应大于等于50元！"};
    }
    if(parseFloat(budget) > parseFloat(money)){
        return {"success":false,msg:"日预算不能大于总预算!"};
    }
    return {"success":true,"data":{"delivery_name":name,"z_money":money,"day_money":budget,"comp_id":comp_id,"comp_name":ad}};
}
function checkDataSt2(){
    var crowd_name = packId.val(),
        crowd_id = packId.attr("_id"),
        matId = inputMad.val(),
        crowd_type = $("input[name=crowdtype]:checked").val(),
        hasMat = $('#mod_waterfall').find('.item.cur').length;
    var areaSet=  $('#area_set').val(), //0选择省市，1 不选择
        areaId = [],result=0;
    var areaDf = $('#area_default').val();//0不限区，1限区
    if(crowd_type==1){
        if(areaSet==0){//选择省市
            pList.find('.chose').each(function() {
                var id = $(this).parent().attr('id');
                areaId.push(id);
            })
            pList.find('.childchose').each(function() {
                var pid = $(this).parent().attr('id');
                var cListChose = cList.find('[pid="' + pid + '"]').find('.chose');

                cListChose.each(function() {
                    var id = $(this).parent().attr('id');
                    areaId.push(id);
                })
            })
            cList.find('.childchose').each(function() {
                var pid = $(this).parent().attr('id');
                var aListChose = aList.find('[pid="' + pid + '"]').find('.chose');

                aListChose.each(function() {
                    var id = $(this).parent().attr('id');
                    areaId.push(id);
                })
            })
            result = areaId.concat(crowdDataArr).join(',');
            if(result==''){
                return {"success":false,msg:"人群标签不能为空！"};
            }
        }else{//不限
            /*if(areaDf==1){ //限区
                return {"success":false,msg:"请重新选择区域"};
            }*/
            result ='0';
        }
    }else{
        /*if(areaDf==1){ //限区
            return {"success":false,msg:"请重新选择区域"};
        }*/
        if(!crowd_name){
            return {"success":false,msg:"请选择人群包"};
        }
        if(!crowd_id){
            return {"success":false,msg:"请选择正确人群包"};
        }
    }
    if(!matId){
        return {"success":false,msg:"请选择投放素材"};
    }
    if(hasMat == 0){
        return {"success":false,msg:"请重新选择有效的素材"};
    }
    return {"success":true,"data":{"material_id":matId,'crowd_info':result,'crowd_type':crowd_type,'crowd_id':crowd_id}};
}
function preDataSt2(){
    var crowd_name = packId.val(),
        crowd_id = packId.attr("_id"),
        matId = inputMad.val(),
        crowd_type = $("input[name=crowdtype]:checked").val(),
        hasMat = $('#mod_waterfall').find('.item.cur').length;
    var areaSet=  $('#area_set').val(),
        areaId = [],result=0;
    if(areaSet==0){//选择省市
        pList.find('.chose').each(function() {
            var id = $(this).parent().attr('id');
            areaId.push(id);
        })
        pList.find('.childchose').each(function() {
            var pid = $(this).parent().attr('id');
            var cListChose = cList.find('[pid="' + pid + '"]').find('.chose');

            cListChose.each(function() {
                var id = $(this).parent().attr('id');
                areaId.push(id);
            })
        })
        cList.find('.childchose').each(function() {
            var pid = $(this).parent().attr('id');
            var aListChose = aList.find('[pid="' + pid + '"]').find('.chose');

            aListChose.each(function() {
                var id = $(this).parent().attr('id');
                areaId.push(id);
            })
        })
        result = areaId.concat(crowdDataArr).join(',');
    }else{//不限
        result ='0';
    }
    return {"material_id":matId,'crowd_info':result,'crowd_type':crowd_type,'crowd_id':crowd_id};
}
function checkDataSt3(){
    var way = domWay.val(),
        charge = domCharge.val(),
        startTime = domSdText.val(),
        endTime = domEdText.val(),
        cost = $("#way option:selected").attr("_cost"),
        ad_type = $("input[name=adtype]:checked").val(),
        timeSlot = $("input[name=chioceTime]:checked").val();
    if(timeSlot ==0){
        var delivery_start_time ="00:00:00";
        var delivery_end_time ="24:00:00";
    }else{
        var delivery_start_time = domTimeAear1.val()+':00:00';
        var delivery_end_time = domTimeAear2.val()+':00:00';
    }
    if(!way){
        return {"success":false,msg:"请选择出价方式"};
    }
    if(!charge){
        return {"success":false,msg:"请输入出价"};
    }
    if(!(/^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/.test(charge))){
        return {"success":false,msg:"请输入正确的出价金额！"};
    }
    if(way==1 && parseFloat(charge)< Number(cost)){
        return {"success":false,msg:(groupName?groupName+"广告，":"")+"选择CPM，底价须大于等于"+ cost +"元"};
    }
    if(way==2 && parseFloat(charge)< Number(cost)){
        return {"success":false,msg:"选择CPC，底价须大于等于"+ cost +"元"};
    }
    if(!startTime || !endTime){
        return {"success":false,msg:"请选择投放开始日期或结束日期"};
    }
    if(startTime < get_date(0)){
        return {"success":false,msg:"投放开始日期不能小于当前日期"};
    }
    return {"success":true,"data":{"bid_money":charge,"delivery_start_date":startTime,"delivery_end_date":endTime,"delivery_start_time":delivery_start_time,"delivery_end_time":delivery_end_time,"bid_type":way,"ad_type":ad_type}};
}
function preDataSt3(){
    var way = domWay.val(),
        charge = domCharge.val(),
        startTime = domSdText.val(),
        endTime = domEdText.val(),
        ad_type = $("input[name=adtype]:checked").val(),
        timeSlot = $("input[name=chioceTime]:checked").val();
        if(timeSlot ==0){
            var delivery_start_time ="00:00:00";
            var delivery_end_time ="24:00:00";
        }else{
            var delivery_start_time = domTimeAear1.val()+':00:00';
            var delivery_end_time = domTimeAear2.val()+':00:00';
        }
    return {"bid_money":charge,"delivery_start_date":startTime,"delivery_end_date":endTime,"delivery_start_time":delivery_start_time,"delivery_end_time":delivery_end_time,"bid_type":way,"ad_type":ad_type};
}

//新建素材
function newMat(type){
    var data = preDataSt2();
    sessionStorage.setItem('addSt2',JSON.stringify(data));//存储投放第二步表单内容
    location.href = "/v1.2/material/add?type="+type;
}
//新建人群包
function newPack(type){
    var data = preDataSt2();
    sessionStorage.setItem('addSt2',JSON.stringify(data));//存储投放第二步表单内容
    location.href = "/v1.2/ncrowd/apply?type="+type;
}

//提交
function commit(type){
    var rs = checkDataSt3(),
        url = '/v1.2/delivery/add';
    if(!rs.success){
        tipTopShow(rs.msg);
        return;
    }
    var post_data= $.extend(rs.data, addSt1, addSt2);

    if(type=='2'){
        url = '/v1.2/delivery/edit';
        post_data.delivery_id = idSet.adId;
    }
     $.ajax({
        type : "POST",
        url : url,
        data : post_data,
        dataType : 'json',
        beforeSend : function() {
            //禁用按钮防止重复提交
            $('.commit-btn').attr('disabled','disabled');
        },
        success : function(rs) {
            tipTopHide();
            if (rs.state == 1) {
                tipTopShow("申请提交成功");
                location.href ='/v1.2/delivery/index';
            }else{
                tipTopShow(rs.msg);
            }
            $('.commit-btn').removeAttr('disabled');
        },
        error : function(rs) {
            $('.commit-btn').removeAttr('disabled');
            tipTopShow("操作失败请重试！");
        }
    });
}

