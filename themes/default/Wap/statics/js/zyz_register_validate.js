$(document).ready(function(){

	
	$('#idcardType').change(function(){
		
		var idCardType = $('#idcardType').val();
		var idcardCode = $('#idcardCode').val()
		if(idCardType == 0){
			return;
		}
		if(idCardType != 1){// 非内地居民身份证，展示并要求填写证明人
			
			if(idCardType == 4){
				$('#countryDiv').show();
				$('#nationDiv').hide();
				//隐藏姓氏拼音，名字拼音
				$('.IShideName').hide();
				//修改文字
				$('.firstName_1').attr("placeholder","请输入姓氏");
				$('.firstName_2').attr("placeholder","请输入名字");
				
				
			}else{
				$('#countryDiv').hide();
				
				
				//隐藏姓氏拼音，名字拼音
				$('.IShideName').show();
				//修改文字
				$('.firstName_1').attr("placeholder","请输入姓氏汉字");
				$('.firstName_2').attr("placeholder","请输入名字汉字");
				
			}
			if(idCardType == 4||idCardType == 3){
				$('#nextPage').hide();
			}else{
				$('#nextPage').show();
			}
			$('#showAuthDiv').show();
			
			
			/*****************港澳台 选择  卡片类型-借记卡  银行-中国银行  *********************/
		    $("#bankcard").html("");
    		$("#bankcard").append("<option value=''>请选择</option>"); 
    		$("#bankcard").append("<option value='1'>内含借记卡功能的志愿者证</option>");
    		
    		$("#bankType").html(''); 
			$("#bankType").append("<option value='0' selected='selected'>请选择</option>"); 
			$("#bankType").append("<option value='1'>中国银行</option>"); 
			
			
			
						
			
		}else{// 内地居民身份证
			
			//隐藏姓氏拼音，名字拼音
			$('.IShideName').show();
			
			
			$('#showAuthDiv').hide();
			$('#idPicDiv').hide();
			
			$('#countryDiv').hide();
			$('#nationDiv').show();
			$("#showAuthDiv input").each(function(){
			    $(this).attr("disable","disable");
			});
			
			/*****************港澳台 选择  卡片类型-借记卡  银行-中国银行  *********************/
    		
			  $("#bankcard").html("");
	    	  $("#bankcard").append("<option value=''>选择志愿者证附加功能</option>"); 
	    	  $("#bankcard").append("<option value='1'>内含借记卡功能的志愿者证</option>");
	    	  $("#bankcard").append("<option value='2'>内含信用卡功能的志愿者证</option>");
			
			
    		$("#bankType").html(''); 
			$("#bankType").append("<option value='0' selected='selected'>请选择</option>"); 
			$("#bankType").append("<option value='1'>中国银行</option>"); 
			$("#bankType").append("<option value='2'>光大银行</option>"); 
			$("#bankType").append("<option value='3'>工商银行</option>"); 
			
			
			
			
		}
		if(idCardType != 4){
    		$('#divCensus').show();	
    		
    		
    		
    	}else{
    		$('#divCensus').hide();
    		
    	}
		
		
		
		
		
		
		//checkBirthDay();
	});

	 fullage=0;
	$("#yearInput").change(function(){
		var age;
		var idCardType = $('#idcardType').val();
		//alert($('#yearInput').val());
		age = getAgeByBirthday($('#yearInput').val());
		//alert(age);
		if(age < 18 && idCardType == 1){// 未满18岁的中国大陆居民要展示并填写监护人信息
			$("#linkmanDiv").show();
		}else{
			$("#linkmanDiv").hide();
		}
		
		/*if(age<18){
     		
     		$('.text_test2').show();
     		$('.text_test').hide();
     		fullage =0;
     		
     	}else{
     		fullage =1;
     		$('.text_test2').hide();
     		$('.text_test').show();
     		
     	}*/
		
		
		
	});
	
	//获取省份
	$.ajax({
		url:path4api+"/region/region/getRegionList.do",
		dataType:"json",
		success:function(data){
			$.each(data,function(i){
				$("#censusProvince").append("<option id='"+data[i].regionId+"' value='"+data[i].name+"'>"+data[i].name+"</option>");
				$("#province").append("<option id='"+data[i].regionId+"' value='"+data[i].name+"'>"+data[i].name+"</option>");
				$("#resideProvince").append("<option id='"+data[i].regionId+"' value='"+data[i].name+"'>"+data[i].name+"</option>");
				$("#dutyProvince").append("<option id='"+data[i].regionId+"' value='"+data[i].name+"'>"+data[i].name+"</option>");
				$("#postProvince").append("<option id='"+data[i].regionId+"' value='"+data[i].name+"'>"+data[i].name+"</option>");
				$("#householdProvince").append("<option id='"+data[i].regionId+"' value='"+data[i].name+"'>"+data[i].name+"</option>");
				//$("#domicile").append("<option id='"+data[i].regionId+"' value='"+data[i].name+"'>"+data[i].name+"</option>");
				
				$("#householdProvince1").append("<option id='"+data[i].regionId+"' value='"+data[i].name+"'>"+data[i].name+"</option>");
			});

		},
		error:function(){}
	});
	
	//弹窗--手持身份证模板照片
	$("#showImg").click(function () {
	    $("#img_show").removeClass('display_n');
	    
	    
	    
	});
	$("#img_show .close_btn").click(function () {
	    $("#img_show").addClass('display_n');
	});
	
	//证件照上传
    $('#fileInput').change(function(){
    	uploadIdPic();
	});
    
    //监护人身份证校验
    $("#identityCard").change(function(){
    	var identityCard = $(this).val();
    	if(!validateIdCard(identityCard)){
    		alert("错误的监护人身份证号码，请核对！");
            return;
    	}
    });
   
	
	
	//上一页
	$("#upPage").click(function () {
		$("#onePage").show();
		$("#pageNext").hide();
		$("#upPage").hide();
	    $("#nextPage").show();
	    $('.sendFrom').hide();//隐藏提交按钮
	});
	

		backCardChange();
		checkBirthDay();
});
function backCardChange(){
	var idCardType = $('#backCard').val();
	
	if(idCardType == 1){// 借记卡
		$("#cardDiv").hide();
		$("#debitCard").show();
		
		$(".addressAndisShow1").hide();//借记卡显示此样式
		$(".addressAndisShow2").show();//借记卡显示此样式
		
		
		if($("#idcardType").val()==2||$("#idcardType").val()==3||$("#idcardType").val()==4){
					
			/*$("#bankType").html(''); 
			$("#bankType").append("<option value='0'>请选择</option>"); 
			$("#bankType").append("<option value='1'>中国银行</option>"); 
			$("#bankType").append("<option value='2'>光大银行</option>"); 
			$("#bankType").append("<option value='3'>工商银行</option>");  
			$("#bankType option[value='3']").remove();*/ 
		   
		}else{
			
			 
			/*$("#bankType").html(''); 
			$("#bankType").append("<option value='0'>请选择</option>"); 
			$("#bankType").append("<option value='1'>中国银行</option>"); 
			$("#bankType").append("<option value='2'>光大银行</option>"); 
			$("#bankType").append("<option value='3'>工商银行</option>"); */
			
			
		}
		
		
	}else{
		$("#cardDiv").show();
		$("#debitCard").hide();
		
		$(".addressAndisShow1").show();//借记卡显示此样式
		$(".addressAndisShow2").hide();//借记卡显示此样式
		
		if($("#idcardType").val()==2||$("#idcardType").val()==3||$("#idcardType").val()==4){
			
			   /* $("#bankType").html(''); 
				$("#bankType").append("<option value='0'>请选择</option>"); 
				$("#bankType").append("<option value='1'>中国银行</option>"); 
				$("#bankType").append("<option value='2'>光大银行</option>"); 
				$("#bankType").append("<option value='3'>工商银行</option>"); 
				
				$("#bankType option[value='1']").remove(); 
				$("#bankType option[value='2']").remove(); */
				
			}else{
				
				/*$("#bankType").html(''); 
				$("#bankType").append("<option value='0'>请选择</option>"); 
				$("#bankType").append("<option value='1'>中国银行</option>"); 
				$("#bankType").append("<option value='2'>光大银行</option>"); 
				$("#bankType").append("<option value='3'>工商银行</option>"); */
				
				
			}
		
	}
	
	
	
}
/**
 * 内地居民身份证自动填充出生日期,否则手动填写
 */
function checkBirthDay(){
	var idCardType = $('#idcardType').val();
	var idcardCode = $('#idcardCode').val();
	var birthday;
	var sex;

	if(!idcardCode){
		return false;
	}

	if(idCardType == 1){// 证件类型为身份证
		birthday = getBirthdayByIdcard(idcardCode);
		$('#yearInput').val(birthday).change();
		$('#yearInput').attr("disable","disable");
		$('#yearInput').hide();
		
		$('#yearValue').removeAttr("disable");
		$('#yearValue').val(birthday);
		$('#yearValue').show();
		
		sex = sexByIdCard(idcardCode);
		if(sex == 1){
			$("#radio_male").prop("checked","checked");
			$("#radio_male").attr("disabled",false);
			$("#radio_female").removeAttr("checked");
			$("#radio_female").attr("disabled",true);
		}else{
			$("#radio_female").prop("checked","checked");
			$("#radio_female").attr("disabled",false);
			$("#radio_male").removeAttr("checked");
			$("#radio_male").attr("disabled",true);
		}
		
		
		$('#idPicDiv').hide();// 内地居民身份证不需要上次证件照
		$('#sfzText').val(null);
		$('#imghead').attr('src',null);
	}else{
		$("#radio_female").attr("disabled",false);
		$("#radio_male").attr("disabled",false);
		
		$('#yearValue').attr("disable","disable");
		$('#yearValue').hide();
		
		$('#yearInput').removeAttr("disable");
		$('#yearInput').show();
		
		$('#idPicDiv').show();
		$('#yearInput').removeAttr("disabled").change();
		/*if(!checkIdCardByType(idCardType,idcardCode)){
			alert("请输入正确的证件号码！");
			return;
		}*/
		
	}
	//alert(birthday);
}
/**
 * 根据类型校验证件号
 * @param idCardType
 * @param idcardCode
 */
function checkIdCardByType(idCardType,idcardCode){
	var re;
	var result = true;
	if (idCardType == '2'){ 
		re = /^[HMhm]{1}([0-9]{10}|[0-9]{8})$/; 
		result = re.test(idcardCode); 
	}else if(idCardType == '3'){
		var re1 = /^[0-9]{8}$/; 
		var re2 = /^[0-9]{10}$/;
		result = (re1.test(idcardCode)) || (re2.test(idcardCode));
	}
	
	return result;
}
/**
 * 根据身份证号获取出生日期
 * @param idcardCode
 * @returns
 */
function getBirthdayByIdcard(idcardCode){
    var birthdayno,birthdaytemp;
    if(idcardCode.length==18){
        birthdayno=idcardCode.substring(6,14);
    }else if(idcardCode.length==15){
        birthdaytemp=idcardCode.substring(6,12);
        birthdayno="19"+birthdaytemp;
    }else{
    	/*if(!validateIdCard(idcardCode)){
    		alert("错误的身份证号码，请核对！");
            return;
    	}*/
    }
    var birthday=birthdayno.substring(0,4)+"-"+birthdayno.substring(4,6)+"-"+birthdayno.substring(6,8);
    return birthday;
}
/*根据出生日期算出年龄*/  
function getAgeByBirthday(strBirthday){         
    var returnAge;  
    var strBirthdayArr=strBirthday.split("-");  
    var birthYear = strBirthdayArr[0];  
    var birthMonth = strBirthdayArr[1];  
    var birthDay = strBirthdayArr[2];  
      
    d = new Date();  
    var nowYear = d.getFullYear();  
    var nowMonth = d.getMonth() + 1;  
    var nowDay = d.getDate();  
      
    if(nowYear == birthYear){  
        returnAge = 0;//同年 则为0岁  
    }else{  
        var ageDiff = nowYear - birthYear ; //年之差  
        if(ageDiff > 0){
            if(nowMonth == birthMonth){  
                var dayDiff = nowDay - birthDay;//日之差  
                if(dayDiff < 0){  
                    returnAge = ageDiff - 1;  
                }else{  
                    returnAge = ageDiff ;  
                }  
            }else{  
                var monthDiff = nowMonth - birthMonth;//月之差  
                if(monthDiff < 0){  
                    returnAge = ageDiff - 1;  
                }else{  
                    returnAge = ageDiff ;  
                }  
            }
        }else{  
            returnAge = -1;//返回-1 表示出生日期输入错误 晚于今天  
        }  
    }  
    return returnAge;//返回周岁年龄   
}
/**
 * 上传证件照
 */
function uploadIdPic(){
	$('#sfzText').val($('#fileInput').val());
	if($('#sfzText').val()==''){
		//alert("请上传证件照片！");
		return false;
	}
	var location=$('#fileInput').val(); 
    var point = location.lastIndexOf("."); 
    var type = location.substr(point); 
     if(type!=".jpg"&&type!=".gif"&&type!=".JPG"&&type!=".GIF"&&type!=".png"&&type!=".PNG"&&type!=".jpeg"&&type!=".JPEG"){ 
    	 alert("上传的文件格式不正确！");
		 return false;      
     } 
	$.ajaxFileUpload({
		url:path4api+'/api/fileUpload/uploadImage.do?filename='+$("#sfzText").val(),
		secureuri:false,
		fileElementId:'fileInput',
		dataType: 'json',
		cache:false,
		data:{filename: $("#sfzText").val()},
		success: function (data, status){
			if(data.status == 'OK'){
				$('#sfzText2').val(data.relative_path);
				//$('#idpicShow').attr("src",data.path);
				alert('上传成功');
				
			}else{
				$('#sfzText').val('');
				alert(data.msg);
			}
			
			previewIdpic(data.path);
		},  
        error: function (data, status, e){
        	$("#sfzText").val('');
        }  
	});
	$("#fileInput").replaceWith('<input type="file" class="upload_file" name="papers" id="fileInput" />');    
    $("#fileInput").on("change", function(){    
    	uploadIdPic();  
    }); 
}
/**
 * 身份证图片预览
 */
function previewIdpic(src) {
	
	$('#sfz_model').attr("src",src);
	$('#sfz_model').show();
}

/**
 * 国内居民身份证校验
 */
function validateIdCard(idCard){
	var pass = true;
 //15位和18位身份证号码的正则表达式
 var regIdCard=/^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/;

 //如果通过该验证，说明身份证格式正确，但准确性还需计算
 if(regIdCard.test(idCard)){
  if(idCard.length==18){
   var idCardWi=new Array( 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 ); //将前17位加权因子保存在数组里
   var idCardY=new Array( 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ); //这是除以11后，可能产生的11位余数、验证码，也保存成数组
   var idCardWiSum=0; //用来保存前17位各自乖以加权因子后的总和
   for(var i=0;i<17;i++){
    idCardWiSum+=idCard.substring(i,i+1)*idCardWi[i];
   }

   var idCardMod=idCardWiSum%11;//计算出校验码所在数组的位置
   var idCardLast=idCard.substring(17);//得到最后一位身份证号码

   //如果等于2，则说明校验码是10，身份证号码最后一位应该是X
   if(idCardMod==2){
    if(idCardLast=="X"||idCardLast=="x"){
    	pass =true;
    }else{
    	pass =false;
    }
   }else{
    //用计算出的验证码与最后一位身份证号码匹配，如果一致，说明通过，否则是无效的身份证号码
    if(idCardLast==idCardY[idCardMod]){
    	pass =true;
    }else{
    	pass =false;
    }
   }
  } 
 }else{
	 pass =false
 }
 return pass;
}
//去掉字符串头尾空格   
function trim(str) {  
   return str.replace(/(^\s*)|(\s*$)/g, "");  
}  
/** 
 * 通过身份证判断是男是女 
 * @param idCard 15/18位身份证号码  
 * @return 2-女、1-男 
 */ 
function sexByIdCard(idCard){  
	idCard = trim(idCard.replace(/ /g, ""));// 对身份证号码做处理。包括字符间有空格。  
	if(idCard.length==15){  
	    if(idCard.substring(14,15)%2==0){  
	        return 2;  
	    }else{  
	        return 1;  
	    }  
	}else if(idCard.length ==18){  
	    if(idCard.substring(14,17)%2==0){  
	        return 2;  
	    }else{  
	        return 1;  
	    }  
	}else{  
	    return 0;  
	}
}

function getBkBranch(){
	console.log($("#bankType").val())
	/*-----------------弹出-------------------*/
	var bankType = $("#bankType").val();
	if(bankType == ""){
		alert('请先选择银行');return;
	}
    window.open(path+"/view/card/popup.jsp?bankType="+bankType, 'newwindow', 'height=600, width=780, top=100, left=300, toolbar=no, menubar=no, scrollbars=yes,resizable=no,location=no, status=no') //

}



//首页为空验证
function firset_page_validate(){
	
	
	
	//证件类型     内地居民身份证
	if($("#idcardType").val()=="")	{
		alert("请选择证件类型");
		return false;
	}
		
	
	//证件号码
	if($('#idcardCode').val()==''){
		
		alert("请输入正确证件号码")
		return false;
	}
	//证件号码
	if($("#idcardType").val()==1){
		
		if($('#idcardCode').val().length!=18){
			
			alert("请输入18位的证件号")
			return false;
		}
		
	}
	//姓氏汉字
	   if($('#firstnameCn').val()==''){
			
			alert("请输入真实姓氏")
			return false;
		}
  
	//名字汉字
    if($('#lastnameCn').val()==''){
		
		alert("请输入名字汉字")
		return false;
	}
    if($("#idcardType").val()!=4){
	      //名字拼音
	      if($('#lastPingyin').val()==''){
			
			alert("请输入名字拼音")
			return false;
		  }
	    //姓氏拼音
		   if($('#firstPingyin').val()==''){
				
				alert("请输入名字拼音")
				return false;
			}
		   
	  }
    
	//证件到期日期
    
    	
	if($('#zjdqrq').val()==''){
		
		alert("请输入证件到期日期")
		return false;
	}
 
    //证件签发机关
    if($('#idcardOffice').val()==""){
    	alert("请填写签发机关")
		return false;
    	
    }
    //政治面貌
    if($('#politicalStatus').val()=='0'){
    	alert("请选择政治面貌")
		return false;
    	
    }
   //最高学历
    if($('#education').val()=='0'){
    	alert("请选择最高学历")
		return false;
    	
    }
  //从业情况
    if($('#employment option:selected').val()=='0'){
    	alert("请选择从业情况")
		return false;
    	
    }
  //归属组织团体
    if($('#districtName0').val()==''){
    	alert("请至少选择一个组织和团体")
		return false;
    	
    }
  //民族
    
    if($("#idcardType").val()==1){
    	
    	 if($('#ethnicity').val()==''){
 	    	alert("请选择政民族")
 			return false;
 	    	
 	    }
    	//未成年年监护人信息
    	 if(getAge()<18){
    		 //监护人姓名
    		 if($('#linkman').val()==''){
     	    	alert("请输入监护人姓名")
     			return false;
     	    	
     	      }
    		 //监护人身份号码
    		 if($('#linkidentity').val()==''){
     	    	alert("请输入监护人身份证号码")
     			return false;
     	    	
     	      }
    		 //监护人手机号码
    		 if($('#linkphone').val()==''){
     	    	alert("请输入监护人手机号码")
     			return false;
     	    	
     	      }
    		 //未成年手持证件照
    		 if($("input[value=off2]").is(":checked")	){
    			 
    			 if($('#imghead').attr("src")=="undefined"){
    				 
    				 alert("请上传本人及监护人证件照")
 	     			  return false;
    			 }
    			 
    		 }
        	
        	
         }
    	
    	
    }
    
   
    
    //邀请证明人
    if($("#idcardType").val()!=1){
    	  //证明人证件号
    	 if($('#proveIdcard0').val()==''){
 	    	alert("请输入证明人")
 			return false;
 	    	
 	    }
    	//手持证件照
	    if($('#child_img3').attr("src")==''){
	    	alert("请上传手持证件照")
			return false;
	    }
    	
    }
    
   //国籍
    if($("#idcardType").val()==4){
		   //国籍
		   if($('#censusCountry').val()==''){
		    	alert("请选择国籍")
				return false;
		    }
	  }
  
  //常住地址-地市
    if($('#province').val()==''){
    	alert("请选择常住地址")
		return false;
    	
    }
  //常住地址-区县
    if($('#city').val()==''){
    	alert("请选择常住地址")
		return false;
    	
    }
  //常住地址-街道门牌号
    if($('#address').val()==''){
    	alert("请输入街道门牌号")
		return false;
    	
    }
    //婚姻状况
    if($('#merryType').val()==''){
    	alert("请选择婚姻状况")
		return false;
    	
    }
    //籍贯
    if($("#idcardType").val()==1){
    	
    	 //籍贯 -地市
	    if($('#addressjiguan').val()==''){
	    	alert("请选择籍贯地市")
			return false;
	    	
	    }
	  
    	
    }
    if($("#idcardType").val()==2||$("#idcardType").val()==3){
	 
	  //出生日期
	    if($('#birthday').val()==''){
	    	alert("请选择出生日期")
			return false;	    	
	    }
	  
	   
	  
     }
 
 return true;
	}