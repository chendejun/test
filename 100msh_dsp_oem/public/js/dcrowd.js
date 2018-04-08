var infoId = getUrlParams('crowd_id') ? getUrlParams('crowd_id') : null; //人群信息id
var crowdArr = {region:[],age:[],education:[],industry:[],house:[],car:[],home:[],digital:[],service:[],hobby:[],music:[],game:[],dress:[],health:[],beauty:[],train:[],tourism:[],baby:[],sports:[],shopping:[],sex:[]},//ID数组初始化
    region = []; //城市数组初始化
var pList = $('#province_list'),cList = $('#city_list'),aList = $('#area_list');
const OPEN = 'cur',CHOSE = 'chose',CHILDCHOSE = 'childchose';
var backType = getUrlParams('type') ? getUrlParams('type'):'';//url参数来源于新增投放
getData();//获取可选项列表

//取详情数据
function getInfo(infoId){
    $.ajax({
        type: "GET",
        url: '/dcrowd/detail/crowd_id/'+infoId,
        dataType:'json',
        success: function(rs){
            tipTopHide();       //隐藏提示框
            if(rs.state == 1){         //如果查询成功
                var data = rs.data;     //返回数据赋值
                $('#name').val(data.crowd_name);
                var labelArr =JSON.parse(data.label_info);
                for(var i in labelArr){
                    var vlabel =labelArr[i];
                    if(i =='sex'){
                        $('#'+i).find('span[_id="'+labelArr[i][0]+'"]').addClass('cur').siblings().removeClass('cur');
                    }else{
                        $('#'+i).find('.crowd_nav span').eq(1).addClass('cur').prev().removeClass('cur');
                        $('#'+i).find('.crowd_box').show();
                        for( var k in vlabel){
                            $('#'+vlabel[k]).find('em').addClass('chose');
                        }
                    }
                    if(i == 'region'){
                        region = labelArr.region;
                        var data = getAreas(0);
                        pList.show().html('<ul class="plist">'+ data +'</ul>');
                        continue;
                    }
                    for( var j in crowdArr){
                        if(i === j){
                           crowdArr[j] = labelArr[i];
                           break;
                        }
                    }
                }
            }else{
                tipTopShow(rs.msg);
            }
         },
        error:function(rs){
            tipTopShow("查询信息失败，请重试...");    //显示提示框
        }
    });
}

//查看设置的值是否已经存在
function find(arr, callback) {
  var i = 0, len = arr.length;
  while(i < len) {
    var item = arr[i];
    if(callback.call(callback, item, i)) {
      return [item, i];
    }
    i++;
  }
  return undefined;
}
//设置值
function setValue(obj, name, value, add) {
  var arr = obj[name] || [];
  var existed = find(arr, function(item) { return item === value });
  if(existed === undefined) {
    add && arr.push(value)
  }else {
    !add && arr.splice(existed[1], 1)
  }
}

//点击提交
var commitBtn = $('#commit_btn');
commitBtn.click(function(){
    crowdArr.region = getRegion();
    submit(crowdArr,infoId);
})
//提交操作
function submit(data, id){
    var name = $.trim($('#name').val()),
        hasKey = false,
        url = '/dcrowd/apply';
    for(var key in data) {
        var item = data[key];
        var choseType = $('#'+key).find('.crowd_nav .cur').attr('type');
        if(item.length > 0 && choseType!=0) {
            hasKey = true;
        }
    }
    if(!hasKey) {
        tipTopShow('请选择定向人群!');
        return;
    }

    if(!name) {
        tipTopShow('人群定向名称不能为空！');
        return;
    }

    var postData = {};
    for(var i in data){
        var choseType = $('#'+i).find('.crowd_nav .cur').attr('type');
        if(choseType == 0){
            postData[i] = [].join(',')
        }else{
            postData[i] = data[i].join(',')
        }
    }

    postData.crowd_name = name;

    if(!(id === undefined || id === null)){
        postData.crowd_id = id;
        url = '/dcrowd/reapply';
    }
    $.ajax({
        type : 'POST',
        url : url,
        data : postData,
        dataType : 'json',
        beforeSend : function() {
            //禁用按钮防止重复提交
            commitBtn.attr('disabled','disabled');
            tipTopShow('正在创建人群包，请稍候...',8000);
        },
        success : function(rs) {
            tipTopHide();
            if (rs.state == 1) {
                tipTopShow('人群新增成功');
                if(backType){
                    location.href = backUrl;
                }else{
                    location.href ='/dcrowd/index';
                }
            }else{
                tipTopShow(rs.msg);
            }
            commitBtn.removeAttr('disabled');
        },
        error : function(rs) {
            tipTopHide();
            commitBtn.removeAttr('disabled');
            tipTopShow('操作失败请重试！');
        }
    });
}

//获取各分类标签列表数据
function getData(){
    $.ajax({
        type: "GET",
        url: '/dcrowd/getLabel',
        dataType:'json',
        success: function(rs){
            tipTopHide();       //隐藏提示框
            if(rs.state == 1){         //如果查询成功
                var data = rs.data;     //返回数据赋值
                renderData(data);
                if (infoId){
                    getInfo(infoId);
                }
            }else{
                tipTopShow(rs.msg);
            }
         },
        error:function(rs){
            tipTopShow("查询信息失败，请重试...");    //显示提示框
        }
    });
}

//渲染数据
function renderData(data){
    for(var i in data){
        var html='',vdata = data[i];
        if(i == 'sex'){
            for(var j in vdata){
                html +='<span _id="'+vdata[j].c_tag_id+'" onclick="setSex('+vdata[j].c_tag_id+')">'+vdata[j].tag_name+'</span>';
            }
            $('#sex .crowd_nav').append(html);
        }else{
            for(var j in vdata){
                html +='<li class="lists items" id="'+vdata[j].c_tag_id+'" tag_type="'+vdata[j].tag_type+'"><em class="checkbox"></em>'+vdata[j].tag_name+'</li>';
            }
            $('#'+i).find('.crowd_box ul').html(html);
        }
    }
}

//标签切换
$('body').on('click','.crowd_nav span', function(e){
    var th = $(this);
    var type =  th.attr('type');
    var parentDom =  th.closest('.from-row');
    /*if(th.hasClass('cur')){
        return;
    }*/
    th.closest('.crowd_nav').find('span').removeClass('cur');
    th.addClass('cur');
    parentDom.attr('type', th.attr('type'));
    if(type==21){
        parentDom.attr('_id', th.attr('_id'));
        return;
    }
    if(type==0){ //不限
        parentDom.find('.crowd_box').hide();
    }else if(type==1){ //省市
        var tagId = $(this).attr('_id');
        if(pList.html() == '') {
            var data = getAreas(tagId);
            pList.show().html('<ul class="plist">'+ data +'</ul>');
        }
        parentDom.find('.crowd_box').show();
    }else{//其他
        parentDom.find('.crowd_box').show();
    }
});

 //选中或取消
$('body').on('click', '.items', function(e) {
    e.stopPropagation();
    var t = $(this),
        id = t.attr('id'),
        em = t.find('.checkbox'),
        parentId = t.closest('.from-row').attr('id');
    if(em.hasClass('chose')) {
        setValue(crowdArr, parentId, id, false);
        em.removeClass('chose');
    }else {
        em.addClass('chose');
        setValue(crowdArr, parentId, id, true);
    }
})
//设置性别
function setSex(id){
    crowdArr.sex = id ? [id] : [];
}

//获取城市数据
function getAreas(id, isChecked) {
    var html = '';
    $.ajax({
        type: "GET",
        url: '/dcrowd/getTag/type/1/pid/'+id,
        dataType:'json',
        async : false,
        success: function(rs){
            var data = rs.data;
            if(rs.state == 1){
                $.each(data,function(k,v){
                    var checked = false;
                    for(var j = 0; j < region.length; j++) {
                         if(region[j] == v.c_tag_id) {
                            checked = true;
                            region.splice(j, 1);
                            break;
                         }
                    }
                    html +='<li class="lists" id="'+v.c_tag_id+'"><em class="checkbox ' + (isChecked ? 'chose' : '') +(checked ? ' chose' : '') + '"></em>'+v.tag_name+'</li>';
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
//选择省市标签
$('body').on('click','#province_list li', function(e){ //点击省份获取城市列表
    var tagId = $(this).attr('id'),
    ul = cList.find('[pid="' + tagId + '"]'),
    isChecked = $(this).find('.chose').length > 0;
    aList.hide();
    cList.show();
    $(this).addClass('cur').siblings('li').removeClass('cur');
    if(ul.length > 0) {
        ul.show().siblings('ul').hide()
    }else {
        var data = getAreas(tagId, isChecked);
        cList.children('ul').hide()
        cList.append('<ul class="plist" pid="' + tagId + '">'+ data +'</ul>');
    }
}).on('click','#city_list li', function(e){ //点击城市获取区列表
    var tagId = $(this).attr('id'),
        pId = $(this).parent().attr('pid'),
      child = aList.children('[pid="' + pId + '"]'),
      ul = child.children('[pid="' + tagId + '"]'),
      isChecked = $(this).find('.chose').length > 0;
    aList.show();
    $(this).addClass('cur').siblings('li').removeClass('cur').parent().siblings('ul').find('li').removeClass('cur');
    if(child.length > 0) {
        child.show().siblings('div').hide()
        if(ul.length > 0) {
            ul.show().siblings('ul').hide()
        }else {
            var data = getAreas(tagId, isChecked);
            child.children('ul').hide()
            child.append('<ul class="plist" pid="' + tagId + '">'+ data +'</ul>');
        }
    }else {
        var data = getAreas(tagId, isChecked);
        aList.children('div').hide()
        aList.append('<div pid="' + pId + '"><ul class="plist" pid="' + tagId + '">'+ data +'</ul></div>');
    }
}).on('click', '#area_list .checkbox', function(e) { //选中或取消区
    e.stopPropagation()
    var t = $(this),
        plist = t.closest('.plist'),
        pSize = plist.children().length,
        pid = plist.attr('pid'),
        ppid = t.closest('div').attr('pid'),
        pplist = cList.children('[pid="' + ppid + '"]'),
        ppSize = pplist.children().length,
        cCheckbox = cList.find('#' + pid).children('.checkbox'),
        pCheckbox = pList.find('#' + ppid).children('.checkbox');

    if(t.hasClass('chose')) {
        t.removeClass('chose');
        cCheckbox.removeClass('chose');
        if(plist.find('.chose').length == 0) {
            cCheckbox.removeClass('childchose');
        }else {
            cCheckbox.addClass('childchose');
        }

        pCheckbox.removeClass('chose');
        if(pplist.find('.chose').length == 0) {
            if(pplist.find('.childchose').length == 0) {
                pCheckbox.removeClass('childchose');
            }else {
                pCheckbox.addClass('childchose');
            }
        }else {
            pCheckbox.addClass('childchose');
        }
    }else {
        t.addClass('chose');
        if(plist.find('.chose').length == pSize) {
            cCheckbox.addClass('chose').removeClass('childchose');
        }else {
            cCheckbox.addClass('childchose');
        }

        if(pplist.find('.chose').length == ppSize) {
            pCheckbox.addClass('chose').removeClass('childchose');
        }else {
            pCheckbox.addClass('childchose');
        }
    }
}).on('click', '#city_list .checkbox', function(e) { //选中或取消城市
    e.stopPropagation();
    var t = $(this),
        id = t.parent().attr('id'),
        plist = t.closest('.plist'),
        pSize = plist.children().length,
        pid = plist.attr('pid'),
        aCheckbox = aList.find('[pid="' + id + '"]').find('.checkbox'),
        pCheckbox = pList.find('#' + pid).children('.checkbox');

    if(t.hasClass('chose')) {
        t.removeClass('chose childchose');
        aCheckbox.removeClass('chose');

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
    }else {
        t.removeClass('childchose').addClass('chose');
        aCheckbox.addClass('chose');

        if(plist.find('.chose').length == pSize) {
            pCheckbox.addClass('chose').removeClass('childchose')
        }else {
            pCheckbox.addClass('childchose')
        }
    }
}).on('click', '#province_list .checkbox', function(e) { //选中或取消省份
    e.stopPropagation();
    var t = $(this),
        id = t.parent().attr('id'),
        cCheckbox = cList.find('[pid="' + id + '"]').find('.checkbox'),
        aCheckbox = aList.children('[pid="' + id + '"]').find('.checkbox');

    if(t.hasClass('chose')) {
        t.removeClass('chose childchose');
        cCheckbox.removeClass('chose');
        aCheckbox.removeClass('chose');

    }else {
        t.removeClass('childchose').addClass('chose');
        cCheckbox.addClass('chose').removeClass('childchose');
        aCheckbox.addClass('chose');
    }
})

//取选中的城市id
function getRegion(){
    var regionId = $('#region'),
        areaId =[];
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
    return areaId.concat(region);
}
