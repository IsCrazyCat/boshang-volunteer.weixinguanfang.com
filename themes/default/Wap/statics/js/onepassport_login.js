 /**
  *  一号通写入accessToken时调用
  */
 function __hqweb_accessTokenInit(accesstoken){
 	  //写cookie
 	  document.cookie="__accessToken="+accesstoken+";path=/";
 	 //alert('一号通调用了!');
 	 $.ajax({
			type: "POST",
	        url:path4api+"/api/login/checkOnepassLogin",
	        data:{accessToken:accesstoken},
	        dataType:"json",
	        error: function() {
	            console.log("检查一号通登录出错！");
	        },
	        success: function(data) {
	        	if(data.code == "2"){
	        		window.location.href = data.url;
	        	}
	        	if(data.refresh == "true"){
	        		alert("一号通账号已退出！")
	        		window.location.reload();
	        	}
	        	if('true'==loginFlag){
	        		window.location.href = path+'/';
	        	}
	        }
	    });
 }