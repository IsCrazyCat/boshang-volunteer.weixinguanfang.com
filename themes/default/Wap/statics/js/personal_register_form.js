var post_flag = false; //设置一个对象来控制是否进入AJAX过程
function zyzRegisterSubmit(){
	if ($('#isAgree').is(':checked')) {
		if(post_flag){
			return; //如果正在提交则直接返回，停止执行
		}
		post_flag = true;//标记当前状态为正在提交状态
		var value1 = $("#dutyTelephone1").val();
		var value2= $("#dutyTelephone2").val();
		var value3 = $("#dutyTelephone3").val();
		
		if(value3!=null){
			
			var kk = value1+"-"+value2+"-"+value3;
		}else{
			var kk = value1+"-"+value2;
		}
		

		$("#dutyTelephone").val(kk);
		$.ajax({
			type: "POST",
	        url:path4api+"/api/register/zyzRegisterSubmit",
	        data:$('#myform').serialize(),
	        dataType:"json",
	        error: function() {
	            alert("注册失败！");
	            post_flag =false;
	        },
	        success: function(data) {
	        	if(data.code == 1){
	        		alert("注册成功！");
	        		location.href=path4api+"/register/personalRegisterSuccess";
	        	}else{
	        		alert(data.msg);
	        	}
	        	post_flag =false;
	        }
	    });
	}else{
		alert("请勾选 《志愿者注册服务协议》、 《“注册志愿者证”用户协议》、 《“注册志愿者证”服务协议》");
	}
}

/*function getSessionId(){  
    var c_name = 'JSESSIONID';
    if(document.cookie.length>0){  
       c_start=document.cookie.indexOf(c_name + "=")  
       if(c_start!=-1){   
         c_start=c_start + c_name.length+1   
         c_end=document.cookie.indexOf(";",c_start)  
         if(c_end==-1) c_end=document.cookie.length  
         return unescape(document.cookie.substring(c_start,c_end));  
       }else{
    	   return document.cookie;
       }  
    }  
} */
			