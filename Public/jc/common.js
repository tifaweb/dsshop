
$(document).ready(function(){
	// 菜单js
	$('.menu li a').hover(function(){
	  $(this).parent('.menu li').find('div').show();
	  $(this).parent('.menu li').addClass('on');
	});
	$('.menu li').mouseleave(function(){
	  $(this).find('div').hide();
	  $(this).removeClass('on');
	});
	
	//top user下拉菜单					   
	$('.menu dl dt').hover(function(){
	  $(this).parent('.menu dl').find("dd").show();
	  $(this).addClass("on");
	});
	$('.menu dl').mouseleave(function(){
	  $(this).find("dd").hide();
	  $(this).find("dt").removeClass("on");
	});
	
	// 标题切换阴影
	$('.tab span').click(function(){
	   $(this).next('b').css('display','block');
	   $(this).siblings().next('b').hide();
	});
	
	// index right code
	$('.code_Android').hover(function(){
       $(this).find('span').show();
	}).mouseleave(function(){
	   $(this).find('span').hide();
	});
	$('.code_IOS').hover(function(){
       $(this).find('span').show();
	}).mouseleave(function(){
	   $(this).find('span').hide();
	});

	
	
	// 底部二维码
	$('.footer_code a').hover(function(){
	   $(this).find('b').show(300);	
	}).mouseleave(function(){
       $(this).find('b').hide(300);	
	});
	
	
	
	// invest列表页 头像 经过
	$('.invest_box_Info div.fleft').hover(function(){
       $(this).find('div.user').show();
	}).mouseleave(function(){
	   $(this).find('div.user').hide();
	});
	
	
	//borrow 页 操作步骤
	$('.borrow_tab a').click(function(){
		$('.borrow_tab span').siblings().css('background-position','-104px -100px');
		$('.borrow_tab span.last').css('background-position','-214px -100px');
		$(this).next('span').css('background-position','-104px 0');
	    $(this).prev('span').css('background-position','-104px -50px');
		
	});
	$('.borrow_tab a:last').click(function(){
		$(this).next('span').css('background-position','-214px -148px');
	});
	 // invest_bao tab效果
	 $('.bao_tab span:first').click(function(){
	   $(this).addClass('on');
	   $(this).siblings().removeClass('on');
	   $('#info_tab1').show(300);
	   $('#info_tab2').hide(300);
	   $('#info_tab3').hide(300);
	 });
	 $('.bao_tab span:eq(1)').click(function(){
	   $(this).addClass('on');
	   $(this).siblings().removeClass('on');
	   $('#info_tab1').hide(300);
	   $('#info_tab2').show(300);
	   $('#info_tab3').hide(300);
	 });
	 $('.bao_tab span:eq(2)').click(function(){
	   $(this).addClass('on');
	   $(this).siblings().removeClass('on');
	   $('#info_tab1').hide(300);
	   $('#info_tab2').hide(300);
	   $('#info_tab3').show(300);
	 });
	
	// 用户中心tips
	$('a.close').click(function(){
	   $(this).parent('.member_tips').hide(300);
	});
	
	//用户中心左边菜单 伸缩
	$('.memberLeft dt').click(function(){
	  $(this).addClass("on");
	  $(this).siblings().removeClass("on");
	  $('.memberLeft dd').hide(200);
	  $(this).next("dd").show(200);
	});
   
   // 论坛 二级菜单
   $('.icon_publish').hover(function(){
     $('div.div').show(300);
   });
   $('.bbs_tt').mouseleave(function(){
     $(this).find('div.div').hide(300);
   });
   
   //member 充值页面
   $('.bank label').click(function(){
	  $(this).addClass('on');
	  $(this).siblings().removeClass('on');
	  $('.zhifu label').siblings().removeClass('on');
    });
	$('.zhifu label').click(function(){
	  $(this).addClass('on');
	  $(this).siblings().removeClass('on');
	  $('.bank label').siblings().removeClass('on');
    });
   
   
   /*  右边code + topic */
	$(".rightIcon span").hover(function(){
		$(this).find('em').show(100);
	}).mouseleave(function(){
		$(".rightIcon span em").hide(100);
	});
	$(".rightIcon span").hover(function(){
		$(this).find('font').show();
	}).mouseleave(function(){
		$(".rightIcon span font").hide();
	});
			
			
	/* goto-top按钮 */
	$(window).scroll(function(){
		if ($(window).scrollTop()>100){
			$("#back-to-top").fadeIn(1000);
			$("rightIcon").css('right','5%');
		}
		else{
		}
	});
	
	$("#back-to-top,#back-to-top span").on('click',function(){
		
	$("#rightIcon").fadeOut(1000);
	});   
	
});



/**************** banner ****************/
$(function(){
	var sw = 1;
	$("#banner .num a").mouseover(function(){
		sw = $(".num a").index(this);
		myShow(sw);
	});
	function myShow(i){
		$("#banner .num a").eq(i).addClass("cur").siblings("a").removeClass("cur");
		$("#banner ul li").eq(i).stop(true,true).fadeIn(600).siblings("li").fadeOut(600);
		$("#banner ol li").eq(i).stop(true,true).fadeIn(600).siblings("li").fadeOut(600);
	}
	//滑入停止动画，滑出开始动画
	$("#banner").hover(function(){
		if(myTime){
		   clearInterval(myTime);
		}
	},function(){
		myTime = setInterval(function(){
		  myShow(sw)
		  sw++;
		  if(sw==5){sw=0;}
		} , 5000);
	});
	//自动开始
	var myTime = setInterval(function(){
	   myShow(sw)
	   sw++;
	   if(sw==5){sw=0;}
	} , 3500);
})



/**************** tab ****************/
function tab(num){
		for(var id = 0;id<=9;id++)
		{
			if(id==num)
			{
				document.getElementById("on"+id).style.display="block";
				document.getElementById("myon"+id).className="on"; 
			}
			else
			{
				document.getElementById("on"+id).style.display="none";
				document.getElementById("myon"+id).className="";
		}
	}
	
}
function index_tab(num){
		for(var id = 0;id<=9;id++)
		{
			if(id==num)
			{
				document.getElementById("non"+id).style.display="block";
				document.getElementById("nmyon"+id).className="on"; 
			}
			else
			{
				document.getElementById("non"+id).style.display="none";
				document.getElementById("nmyon"+id).className="";
		}
	}
	
}


function tab2(num){
		for(var id = 0;id<=9;id++)
		{
			if(id==num)
			{
				document.getElementById("o2n"+id).style.display="block";
				document.getElementById("my2on"+id).className="on"; 
			}
			else
			{
				document.getElementById("o2n"+id).style.display="none";
				document.getElementById("my2on"+id).className="";
		}
	}
	
}

//弹出隐藏层
function ShowDiv(show_div,bg_div){
document.getElementById(show_div).style.display='block';
document.getElementById(bg_div).style.display='block' ;
var bgdiv = document.getElementById(bg_div);
bgdiv.style.width = document.body.scrollWidth;
// bgdiv.style.height = $(document).height();
$("#"+bg_div).height($(document).height());
};
//关闭弹出层
function CloseDiv(show_div,bg_div)
{
document.getElementById(show_div).style.display='none';
document.getElementById(bg_div).style.display='none';
};

