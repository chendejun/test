var limit = 20;     //页面展示条数

//提示框
function tipTopShow(html,timer){
    timer = timer ||'2000';
    var elem = document.getElementById("popUpLayer");
    if(!elem){
        $("<div id='popUpLayer'></div>").appendTo($("body"));
    }
    var popUpLayer = $("#popUpLayer");
	popUpLayer.html(html);
    popUpLayer.css('marginLeft','-'+(popUpLayer.width()/2)+'px');
    popUpLayer.fadeIn(100);
	var timer = setTimeout(function(){
		popUpLayer.fadeOut(100);
		clearTimeout(timer);
	},timer);
}
//提示框隐藏
function tipTopHide(){
	$('#popUpLayer').fadeOut(100);
}


//显示提示
function loading(){
    var load_content_box = $("<div class='preloader-indicator-modal' id='loading_id' ></div>");
    var load_content_e = $("<div class='preloader preloader-white'></div>");
    load_content_box.append(load_content_e);
    this.show = function(){
        $("body").append(load_content_box);
        // /document.body.appendChild(load_content_box);
    },
    this.hide = function(){
        load_content_box.remove();
    }
}


//返回多少天前的日期
function get_date(v){
    var d = new Date();
        d.setDate(d.getDate()-v);
    var y = d.getFullYear(),
        m = (d.getMonth()+1) > 9 ? d.getMonth()+1 : "0"+(d.getMonth()+1),
        day = d.getDate() > 9 ? d.getDate() :  "0"+d.getDate();
    return y.toString() +"-"+ m.toString() +"-"+ day.toString();
}

 //日期时间计算
function getBeforeDate(n) {
        var n = n;
        var d = new Date();
        var year = d.getFullYear();
        var mon = d.getMonth() + 1;
        var day = d.getDate();
         if(day <= n) {
            if(mon > 1) {
                mon = mon - 1;
            } else {
                year = year - 1;
                mon = 12;
            }
        }
        d.setDate(d.getDate() - n);
        year = d.getFullYear();
        mon = d.getMonth() + 1;
        day = d.getDate();
        s = year + "-" + (mon < 10 ? ('0' + mon) : mon) + "-" + (day < 10 ? ('0' + day) : day);
        return s;
}
        
//重置
function resetForm(id){
    $("#"+id)[0].reset();
}

function search_enter(){
    $("#query_submit").click();
    return false; 
}

//日期转换
function changeTime(time){

	//检查月、日、年、分、秒，前面加0
	function check(i){
        if(i < 10){
            i = "0" + i;
        }
        return i;
    }

    //转换时间
    var timeS = parseInt(time),
        date = new Date(timeS),
        year = date.getFullYear(),
        Month = check(date.getMonth() + 1),
        day = check(date.getDate()),
        hours = check(date.getHours()),
        minutes = check(date.getMinutes()),
        seconds = check(date.getSeconds()),
        getDate = year + "-" + Month + "-" + day + " " + hours + ":" + minutes + ":" + seconds;
    return getDate;
}
//校验邮箱
function checkMail(val){
    var flag = /^([a-zA-Z0-9]+[_|\_|\.|\-]+?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.|\-]+?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/.test(val);
    //var flag = /^([a-zA-Z0-9]+[_|\_|\.|\-]+?)*[a-zA-Z0-9]+([a-zA-Z0-9]+[_|\_|\.|\-]+?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/.test(val);
    return flag;
}

//校验手机号码
function checkTel(val){
    var flag = /^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/.test(val);
    return flag;
}
//校验url
function checkUrl(url){
    var flag = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/.test(url);
    return flag;
}
/*
 *  时间计算
 * @param date  时间
 * @param days  间隔天数
 * @param op  运算符 + -
 * @return cdate 计算后的日期
*/
function calcDate (date,days,op){
    var nd = new Date(date);
    nd = nd.valueOf();
    nd = op == '+' ? (nd + days * 24 * 60 * 60 * 1000) : (nd - days * 24 * 60 * 60 * 1000);
    nd = new Date(nd);
    var y = nd.getFullYear();
    var m = nd.getMonth()+1;
    var d = nd.getDate();
    if(m <= 9) m = "0"+m;
    if(d <= 9) d = "0"+d; 
    var cdate = y+"-"+m+"-"+d;
    return cdate;
}

//格式化日期
Date.prototype.format = function(format){ 
	var o = { 
		"M+" : this.getMonth()+1, //month 
		"d+" : this.getDate(), //day 
		"h+" : this.getHours(), //hour 
		"m+" : this.getMinutes(), //minute 
		"s+" : this.getSeconds(), //second 
		"q+" : Math.floor((this.getMonth()+3)/3), //quarter 
		"S" : this.getMilliseconds() //millisecond 
	}

	if(/(y+)/.test(format)) { 
		format = format.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length)); 
	}
	for(var k in o) { 
		if(new RegExp("("+ k +")").test(format)) { 
			format = format.replace(RegExp.$1, RegExp.$1.length==1 ? o[k] : ("00"+ o[k]).substr((""+ o[k]).length)); 
		} 
	} 
	return format; 
}

// 解析url地址，获取参数值
function getUrlParams(name) { 
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"); 
    var r = window.location.search.substr(1).match(reg); 
    if (r != null) 
        return unescape(r[2]); 
    return null; 
}

//获取页面DOM HTML信息
function getshopHTML(url){    
    var html = "";
    $.ajax({
        type:"GET",
        url : url,
        async : false,
        cache:false,
        success : function(data){              
            html = data;            
        }
    });    
    return html;
}

// //分页
// function renderPage(elem,pages,curr,fn){
//     //var pages = Math.ceil(total / limit); //得到总页数
//     //显示分页
//     console.log(curr);
//     laypage({
//         cont: elem, //容器。值支持id名、原生dom对象，jquery对象。
//         pages: pages, //通过后台拿到的总页数
//         skip: true, //是否开启跳页
//         skin: '#5877a4', // 红色: #AF0000
//         groups: 5, //连续显示分页数
//         curr: curr || 1, //当前页
//         jump: function(obj, first){ //触发分页后的回调
//             if(!first){ //点击跳页触发函数自身，并传递当前页：obj.curr
//                 fn(obj.curr);
//             }
//         }
//     });
// }
//canvas环形比例图
function process(canvasid,stylewidth,styleheight,color){
        var canvas = document.getElementById(canvasid);
        var canvasvalue1 = canvas.getAttribute("value1");
        var canvasvalue2 = canvas.getAttribute("value2");
        var canvasvalue3 = canvas.getAttribute("value3");
        var canvasvalue4 = canvas.getAttribute("value4");
        var process1 = canvasvalue1 ? canvasvalue1.substring(0, canvasvalue1.length-1):'0';
        var process2 = canvasvalue2 ? canvasvalue2.substring(0, canvasvalue2.length-1):'0';
        var process3 = canvasvalue3 ? canvasvalue3.substring(0, canvasvalue3.length-1):'0';
        var process4 = canvasvalue4 ? canvasvalue4.substring(0, canvasvalue4.length-1):'0';
        var context = canvas.getContext('2d');
        var scale = window.devicePixelRatio;
        var center = [stylewidth/2*scale,styleheight/2*scale];
        canvas.style.width = stylewidth + "px";
        canvas.style.height = styleheight + "px";
        canvas.width = stylewidth*scale;
        canvas.height = styleheight*scale;
        //开始画一个灰色的圆
        context.beginPath();
        context.moveTo(center[0], center[1]);
        context.arc(center[0], center[1], center[0], 0, Math.PI * 2, false);
        context.closePath();
        context.fillStyle = '#ccc';
        context.fill();

        // 画进度(颜色1)
        if(process1 != 0 && process1 != 100){
            context.beginPath();
            context.moveTo(center[0], center[1]);
            if(process1 <25){
                context.arc(center[0], center[1], center[0], Math.PI*1.5, Math.PI*(1.5+0.5*(process1/25)), false);//设置圆弧的起始于终止点
            }
            else{
                context.arc(center[0], center[1], center[0], Math.PI*1.5, Math.PI*2* ((process1 / 100)-0.25), false);//设置圆弧的起始于终止点
            }
            context.closePath();
            context.fillStyle = color[0];
            context.fill();
        }
        else if(process1 == 100){
            context.beginPath();
            context.moveTo(center[0], center[1]);
            context.arc(center[0], center[1], center[0], 0, Math.PI*2, false);//设置圆弧的起始于终止点
            context.closePath();
            context.fillStyle = color[0];
            context.fill();
        }

        //画进度(颜色2)
        if(process2 != 0 && process2 !=100){
            context.beginPath();
            context.moveTo(center[0], center[1]);
            context.arc(center[0], center[1], center[0], Math.PI*1.5,Math.PI*(1.5-0.5*(process2/25)), true);//设置圆弧的起始于终止点
            context.closePath();
            context.fillStyle = color[1];
            context.fill();
        }
        else if(process2 == 100){
            context.beginPath();
            context.moveTo(center[0], center[1]);
            context.arc(center[0], center[1], center[0], 0,Math.PI*2, true);//设置圆弧的起始于终止点
            context.closePath();
            context.fillStyle = color[1];
            context.fill();
        }
        //画进度(颜色3)
        if(process3 != 0 && process3 !=100){
            context.beginPath();
            context.moveTo(center[0], center[1]);
            context.arc(center[0], center[1], center[0], Math.PI*(1.5-0.5*(process2/25)),Math.PI*(1.5-0.5*(process2/25)-0.5*(process3/25)), true);//设置圆弧的起始于终止点
            context.closePath();
            context.fillStyle = color[2];
            context.fill();
        }
        else if(process3 == 100){
            context.beginPath();
            context.moveTo(center[0], center[1]);
            context.arc(center[0], center[1], center[0], 0,Math.PI*2, true);//设置圆弧的起始于终止点
            context.closePath();
            context.fillStyle = color[2];
            context.fill();
        }
        //画进度(颜色4)
        if(process4 != 0 && process4 !=100){
            context.beginPath();
            context.moveTo(center[0], center[1]);
            context.arc(center[0], center[1], center[0],Math.PI*(1.5-0.5*(process2/25)-0.5*(process3/25)),Math.PI*(1.5-0.5*(process2/25)-0.5*(process3/25)-0.5*(process4/25)), true);//设置圆弧的起始于终止点
            context.closePath();
            context.fillStyle = color[3];
            context.fill();
        }
        else if(process4 == 100){
            context.beginPath();
            context.moveTo(center[0], center[1]);
            context.arc(center[0], center[1], center[0], 0,Math.PI*2, true);//设置圆弧的起始于终止点
            context.closePath();
            context.fillStyle = color[3];
            context.fill();
        }
        // 画内部空白
        context.beginPath();
        context.moveTo(center[0], center[1]);
        context.arc(center[0], center[1], 40*scale, 0, Math.PI * 2, true);
        context.closePath();
        context.fillStyle = 'rgba(255,255,255,1)';
        context.fill();
    }