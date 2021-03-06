/*
	@Author: 请叫我马哥
	@Time: 2017-04
	@Tittle: tab
	@Description: 点击对应按钮添加新窗口
*/
var tabFilter,str_menu=[],liIndex,curNav,delMenu;
layui.define(["element","jquery"],function(exports){
	var element = layui.element(),
		$ = layui.jquery,
		layId,
		Tab = function(){
			this.tabConfig = {
				closed : true,
				openTabNum : 30,
				tabFilter : "bodyTab"
			}
		};

	//显示左侧菜单
	if($(".navBar").html() == ''){
		var _this = this;
		$(".navBar").html(navBar(navs)).height($(window).height()-230);
		element.init();  //初始化页面元素
		$(window).resize(function(){
			$(".navBar").height($(window).height()-230);
			//$(".layui-side").height($(window).height());
		});
	}
	
	//参数设置
	Tab.prototype.set = function(option) {
		var _this = this;
		$.extend(true, _this.tabConfig, option);
		return _this;
	};

	//通过title获取lay-id
	Tab.prototype.getLayId = function(title){
		$(".layui-tab-title.top_tab li").each(function(){
			if($(this).find("cite").text() == title){
				layId = $(this).attr("lay-id");
			}
		})
		return layId;
	}
	//通过title判断tab是否存在
	Tab.prototype.hasTab = function(title){
		var tabIndex = -1;
		$(".layui-tab-title.top_tab li").each(function(){
			if($(this).find("cite").text() == title){
				tabIndex = 1;
			}
		})
		return tabIndex;
	}

	//右侧内容tab操作
	var tabIdIndex = 0;
	Tab.prototype.tabAdd = function(_this){
		if(localStorage.getItem("str_menu")){			
			str_menu = JSON.parse(localStorage.getItem("str_menu"));
			//console.log('执行==',str_menu);
		}
		var that = this;
		var closed = that.tabConfig.closed,
			openTabNum = that.tabConfig.openTabNum;
			tabFilter = that.tabConfig.tabFilter;
			var title = '';
		// $(".layui-nav .layui-nav-item a").on("click",function(){
			//console.log('是否001==',window.sessionStorage.menu);
			
			if(_this.find("i.icon_infor,i.layui-icon").attr("data-icon") != undefined){			
				if (_this.find("cite").text() != '') {
					var tit = _this.find("cite").text();
					//console.log('执行==',tit);
				} else {
					var tit = '消息公告';
					//console.log('执行==',tit);
				}
				if(that.hasTab(tit) == -1 && _this.siblings("dl.layui-nav-child").length == 0){
					if($(".layui-tab-title.top_tab li").length == openTabNum){
						layer.msg('只能同时打开'+openTabNum+'个选项卡哦。不然系统会卡的！');
						return;
					}
					tabIdIndex++;
					if(_this.find("i.icon_infor").attr("data-icon") != undefined){
						title += '<i class="icon_infor '+_this.find("i.icon_infor").attr("data-icon")+'"></i>';
					// }else{
					// 	title += '<i class="layui-icon">'+_this.find("i.layui-icon").attr("data-icon")+'</i>';
					}
					
					title += '<cite>'+tit+'</cite>';
					title += '<i class="layui-icon layui-unselect layui-tab-close" data-id="'+tabIdIndex+'">&#x1006;</i>';
					element.tabAdd(tabFilter, {
				        title : title,
				        content :"<iframe src='"+_this.attr("data-url")+"' data-id='"+tabIdIndex+"'></frame>",
				        id : new Date().getTime()
				    })

					//当前窗口内容
					var curmenu = {
						"icon" : _this.find("i.icon_infor").attr("data-icon")!=undefined ? _this.find("i.icon_infor").attr("data-icon") : _this.find("i.layui-icon").attr("data-icon"),
						"title" : tit,
						"href" : _this.attr("data-url"),
						"layId" : new Date().getTime()
					}
					str_menu.push(curmenu);
					localStorage.setItem("str_menu",JSON.stringify(str_menu)); //打开的窗口
					localStorage.setItem("curmenu",JSON.stringify(curmenu));  //当前的窗口
					element.tabChange(tabFilter, that.getLayId(tit));
					//console.log('tabAdd0001==',localStorage.getItem('str_menu'));
				}else{
					//当前窗口内容
					if (_this.find("cite").text() != '') {
						var tit = _this.find("cite").text();
						//console.log('执行==',tit);
					} else {
						var tit = '消息公告';
						//console.log('执行==',tit);
					}
					var curmenu = {
						"icon" : _this.find("i.icon_infor").attr("data-icon")!=undefined ? _this.find("i.icon_infor").attr("data-icon") : _this.find("i.layui-icon").attr("data-icon"),
						"title" : tit,
						"href" : _this.attr("data-url"),
						"layId" : new Date().getTime()
					}					
					localStorage.setItem("curmenu",JSON.stringify(curmenu));  //当前的窗口
					element.tabChange(tabFilter, that.getLayId(tit));
					//	console.log('tabAdd02222==',menu);
				}
			}
		// })
	}
	$("body").on("click",".top_tab li",function(){
		//切换后获取当前窗口的内容
		var curmenu = '';
		var str_menu = JSON.parse(localStorage.getItem("str_menu"));
		//console.log('切换后获取menu==',menu);
		curmenu = str_menu[$(this).index()-1];
		//console.log('curmenu==',curmenu);
		if($(this).index() == 0){
			localStorage.setItem("curmenu",'');
			//console.log('this==',window.sessionStorage.curmenu);
		}else{
			localStorage.setItem("curmenu",JSON.stringify(curmenu));
			//console.log('else==',window.sessionStorage.curmenu);
			if(localStorage.getItem("curmenu") == "undefined"){
				//如果删除的不是当前选中的tab,则将curmenu设置成当前选中的tab
				if(curNav != JSON.stringify(delMenu)){
					localStorage.setItem("curmenu",curNav);
				}else{
					localStorage.setItem("curmenu",JSON.stringify(str_menu[liIndex-1]));
				}
			}
		}
		element.tabChange(tabFilter,$(this).attr("lay-id")).init();
	})

	//删除tab
	$("body").on("click",".top_tab li i.layui-tab-close",function(){
		//删除tab后重置session中的menu和curmenu
		liIndex = $(this).parent("li").index();
		var str_menu = JSON.parse(localStorage.getItem("str_menu"));
		//获取被删除元素
		//console.log('删除liIndex==',liIndex);
		//console.log('删除menu==',menu);
		delMenu = str_menu[liIndex-1];
		var curmenu = localStorage.getItem("curmenu")=="undefined" ? "undefined" : localStorage.getItem("curmenu")=="" ? '' : JSON.parse(localStorage.getItem("curmenu"));
		if(JSON.stringify(curmenu) != JSON.stringify(str_menu[liIndex-1])){  //如果删除的不是当前选中的tab
			// localStorage.setItem("curmenu",JSON.stringify(curmenu));
			curNav = JSON.stringify(curmenu);
		}else{
			//console.log('删除是当前选中==',liIndex);
			if($(this).parent("li").length > liIndex){
				localStorage.setItem("curmenu",curmenu);
				curNav = curmenu;
				
			}else{
				localStorage.setItem("curmenu",JSON.stringify(str_menu[liIndex-1]));
				curNav = JSON.stringify(str_menu[liIndex-1]);
				
			}
		}
		str_menu.splice((liIndex-1), 1);
		localStorage.setItem("str_menu",JSON.stringify(str_menu));
		element.tabDelete("bodyTab",$(this).parent("li").attr("lay-id")).init();
	})

	var bodyTab = new Tab();
	exports("bodyTab",function(option){
		return bodyTab.set(option);
	});
})