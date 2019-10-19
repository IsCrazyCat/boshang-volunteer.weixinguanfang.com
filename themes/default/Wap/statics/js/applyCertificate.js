var sendCode = 0; 
$(document).ready(function(){
    $('.select_box select').each(function(index, el) {
        var a=$('.select_box select option:selected').val();
        if(a==0){
            $('.select_box select').css('color', '#999');
            $('.select_box select option').css('color', '#000');
        }else{
            $('.select_box select').css('color', '#000');
        }
        
    });

    $('.select_box select').change(function(event) {
            var a=$(this).children('option:selected').val();
            if(a==0){
                $(this).css('color', '#999');
                $(this).children('option').css('color', '#000');
            }else{
                $(this).css('color', '#000');
            }
        });

    initBank(1); 
    
    $("#sendcode_AID").click(function(){
    	sendCodeToMobile();
    })
    
    initAge();
    initIdCardImg();
});

function initBank(cardType){
	if(cardType == 0){
		return;
	}else if(cardType == 1){
		$("#makeSure").show();
		$("#submitBox").hide();
		$("#regedit_row2").hide();
		$("#nextPage1").show();
	}else if(cardType == 2){
		$("#makeSure").hide();
		$("#submitBox").show();
		$("#regedit_row2").show();
		$("#nextPage1").hide();
		$(".confirm_block").hide();
	}else{
		$("#makeSure").show();
		$("#submitBox").hide();
		$("#regedit_row2").hide();
		$("#nextPage1").show();		
	}

	$("#cardBox").empty();
	$.ajaxSettings.async = false; 
	$.getJSON(path+"/bankBase/getBankCard?cardType="+cardType,function(data){
		var j=1;
		$.each(data.cardBaseList,function(i, bankBase){	
			if(bankBase.bankType!=3){		
				var template =
					  '<li class="radio_item fl bankli" > \
					   		<label  for="sdf" class="label po_r"><input type="radio" regUrl="${regUrl}" name="card" class="radio" id="${sqe}"  value="${value}"/>\
								<div class="bank_pic_box">\
									<img src="${url}" class="bank_pic" />\
								</div>\
							</label> \
					  </li>';
				template = template.replace(/\$\{sqe\}/,'bank'+j);
				template = template.replace(/\$\{value\}/,bankBase.bankType);
				template = template.replace(/\$\{url\}/,path + "/"+bankBase.logoUrl);
				template = template.replace(/\$\{regUrl\}/,bankBase.regUrl);
				$("#cardBox").append(template);
				j++;
			}
		});
 	});
	$.ajaxSettings.async = true; 
	$('.bankli label').click(function(event) {  //给银行的label添加事件，并不是给单选按钮radio添加事件，被选中后只是背景图片的改变
        if(!$(this).hasClass('cur')){
        	$("#receiveAddress").val("");
        	var value = $(this).find('input[name="card"]').val();
        	var regUrl = $(this).find('input[name="card"]').attr("regUrl");
        	$("#bankType").val(value);
        	$("#regUrl").val(regUrl);
            $(this).addClass('cur').parent().siblings().children('label').removeClass('cur');
        }
    });
}

function getbankingOutlets(){
	/*-----------------弹出-------------------*/
	var bankType = $("#bankType").val();
	if(bankType == ""){
		alert('请先选择银行');return;
	}
    window.open(path+"/view/card/popup.jsp?bankType="+bankType, 'newwindow', 'height=600, width=780, top=100, left=300, toolbar=no, menubar=no, scrollbars=yes,resizable=no,location=no, status=no') //

}
function initIdCardImg(){
	$.getJSON(path+"/bankApplyCard/getCardImage?",function(data){
 		var result = data.result;
 		if(result == 'success'){//系统内存在该用户身份证照片
 			$(".idcardImg").attr("src",path+"/bankApplyCard/showidCardImage");
 			$("#isIdCardMust").val("pass");
 		}else{
 			$("#isIdCardMust").val("notPass");
 		}
 	});
}

function VeMobile(Str){
	var reg = /^[1][3,4,5,6,7,8,9][0-9]{9}$/;
	var b = reg.test(Str);
	$(".mobile").removeClass("hidden");
	if(b){
		$(".warning.mobile").addClass("hidden");
	}else{
		$(".passed.mobile").addClass("hidden");
	}
}

function VeEmail(Str){
	var reg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
	$(".email").removeClass("hidden");
	var b = reg.test(Str);
	if (b){
		$(".warning.email").addClass("hidden");
	}else{
		$(".passed.email").addClass("hidden");
	}
}

function sendCodeToMobile(){
	var imgCode=$('#code');
	if(++sendCode > 5){
		alert('一个手机号码一天只能获取5次验证码!');return;
	}
	var referer = window.location.href;
	var sendFlag = false;
	var mobile=$('#mobile');
	var mobileReg = /^[1][3,4,5,6,7,8,9][0-9]{9}$/;
	if(mobileReg.test(mobile.val())){
		var url = path+"/register/sendCodeToMobile";
		$.ajaxSettings.async = false; 
		$.ajax({
			type:"POST",
			url:url,
			data:{mobile:mobile.val(),imgCode:imgCode.val(),flag:'PCcard',srcRef:referer},
			dataType:"json",
			async:false,
			success:function(data) {
				if(data.success){
					sendFlag = true;
					$("#mobileSendLI").show();
					$("#sendcode_AID").attr("disabled","disabled");
				}else{
					alert(data.message);
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
				alert(XMLHttpRequest.status + "错误信息："
						+ XMLHttpRequest.responseText,"错误提示");
			}
		});
		$.ajaxSettings.async = true; 
		if(sendFlag){
			$('#sendcode_AID').css({'background-color':'#dbd8d8','color':'#333'});
			$("#time").val("120"); 
			$("#sendcode_AID").val("120秒后重新获取");
			$("#sendcode_AID").removeClass("moveon");
			$("#sendcode_AID").attr("disabled","disabled"); 
			tips=setInterval("timeinter()",1000); 
		}
	}else{
		VeMobile()
	}
}

function timeinter(){
		 val=parseInt($("#sendcode_AID").val()); 
		val=val-1; 
		$("#sendcode_AID").val((val).toString()+"秒后重新获取"); 
		if(val==0){ 
			clearInterval(tips);
			$("#sendcode_AID").addClass("moveon");
			$('#sendcode_AID').css({'background-color':'#2185cf','color':'#fff'});
			$("#sendcode_AID").val("请重新发送"); 
			$("#sendcode_AID").removeAttr("disabled");
			$.ajax({
				type:"POST",
				url:path+"/register/cancelVerifyCode",
				dataType:"json",
				success:function(data) {
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$("#sendcode_AID").removeAttr('disabled');
				}
			});
		} 
} 

function checkVerifyCodeIsTrue(){
	var tempinputVerifyCode=$("#checkCode").val();
	$(".checkCode").hide();
	if(tempinputVerifyCode==""){
			$(".checkCode.error").show();
			$(".checkCode.error").html("请输入验证码");
		    return;
		}
    $.ajax({
			type:"POST",
			url:path+"/register/checkCodeInMobile",
			data:{inputVerifyCode:tempinputVerifyCode},
			dataType:"json",
			success:function(data) {
				if(data.success){
					$(".checkCode.right").show();
					$(".checkCode.right").html("验证码正确");
					$("#checkResult").val("pass");
				}else{
					$(".checkCode.error").show();
					$(".checkCode.error").html(data.message);
					$("#checkResult").val("error");
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
				
			}
		});
	 }

function initAge(){	
	var idCardType = $('#idcardType').val();
	if(idCardType==1){
	    if(length != 18 && length != 15){
	    	alert('你的身份证号码有误，不能申请志愿者证！');return;
	    }
	    var birthYear = idCard.substr(6,4);
	    var birthMonth = idCard.substr(10,2)-1;
	    var birthDay = idCard.substr(12,2);
	    var birthDate = new Date(birthYear, birthMonth, birthDay);
	    var currentdate = new Date();
	    var intervalYear = currentdate - birthDate;
	    var age = intervalYear/(1000*60*60*24*365);
	    if(age >= 18){
	    	$("#JHR").hide();
	    	$("#age01").attr("checked","checked");
	    	$("#age02").attr("disabled","disabled");
	    }else{
	    	$("#age02").attr("checked","checked");
	    	$("#age01").attr("disabled","disabled");
	    	$("#JHR").show();
	    }
	}else{
    	$("#JHR").hide();
    	$("#age01").attr("checked","checked");
    	$("#age02").attr("disabled","disabled");
	}
}

function getCity(regionPid, id){
	$.ajaxSettings.async = false;
	$("#"+id+"").empty();
	$.getJSON(path+"/region/region/getRegionList?regionId="+regionPid,function(data){
 		var city = "";
 		city += "<option value='0'>请选择</option>";
		$.each(data,function(){
			city += "<option value='"+this.name+"'>"+this.name+"</option>";
		});
		$("#"+id+"").append(city);
 	});
	$.ajaxSettings.async = true;
}

function hasApplied(){ //判断是否已经申请过
	$.ajaxSettings.async = false;
	var hasApplied = false;
	$.getJSON(path+"/bankApplyCard/checkHasApplied",function(data){
		var b = data.result;
		if(b){
			hasApplied = true;
			alert("您已经申请过志愿者证，请不要重复申请！");
		}
 	});
	$.ajaxSettings.async = true;
	if(hasApplied){ //已经申请过
		return true;
	}
	return false;
}

function formSubmit2(){
	if(!document.getElementById("Confirm2").checked){
		alert('你必须要同意服务协议才能提交，请确认同意!');
		return false;
	}
   
	if(hasApplied()){
		return false;
	}
	
	var cardType = $("#cardType").val();
	if(cardType == 0){
		$("#cardType").focus();
		alert('请选择卡类!');
		return;
	}
	
	var bankType = $("#bankType").val();
	if(bankType == ""){
		alert('请选择银行');
		return;
	}
	
	var domicile = $("#domicile").val();
	if(domicile == null || domicile == 0){
		alert('请选择注册地');
		return;
	}
	
	/*var isIdCardMust = $("#isIdCardMust").val();
	if(isIdCardMust == 'notPass'){
		alert('请上传证件头像');return;
	}*/
	
	var firstName = $("#firstName").val();
	if(firstName == ''){
		$("#firstName").focus();
		alert("请填写姓氏拼音");
		return;
	}
	
	var nameReg = /^[a-zA-Z]+$/;
	var result = nameReg.test(firstName);
	if(!result){
		alert('姓氏拼音只能是字母');
		return;
	}
	
	var lastName = $("#lastName").val();
	if(lastName == ''){
		$("#lastName").focus();
		alert("请填写名字拼音");
		return;
	}
	
	var result = nameReg.test(lastName);
	if(!result){
		alert('名字拼音只能是字母');
		return;
	}
	
	var gender = $("input[name='gender']:checked").val();
	if(gender == undefined || gender== 'on'){
		alert('请选择性别');
		return;
	}
	
	var clothes = $("#clothes").val();
	if(clothes == null || clothes == 0){
		$("#clothes").focus();
		alert('请选择衣服大小');
		return;
	}
	
	var mobile = $("#mobile").val();
	if(mobile == ''){
		$("#mobile").focus();
		alert("请填写手机号码");
		return;
	}
	
	var checkCode = $("#checkCode").val();
	if(checkCode == ''){
		$("#checkCode").focus();
		alert("请填写验证码");
		return;
	}else{
		var result = $("#checkResult").val();
		if(result == 'error'){
			alert('请输入正确的验证码!');
			return;
		}
	}
	
	$.ajax({
	    type: 'post',
	    dataType:'json',
	    url: path + "/bankApplyCard/saveBankApplyCard",
	    data: $("#bankApplyCardForm").serialize(),
	    success: function(data){
	       alert(data.msg);
	       var cardType = $("#cardType").val();
	       if(cardType == 2){
	    	   var regUrl = $("#regUrl").val();
	    	   if(regUrl != 'null'){
	    		   window.location.href = regUrl;
	    	   }
	       }
	    },error:function(){
	       alert("提交失败,请联系管理员!");
	    }
	});
}

function formSubmit1(){
	if(!document.getElementById("Confirm").checked){
		alert('你必须要同意服务协议才能提交，请确认同意!');
		return;
	}
	
	if(hasApplied()){
		return;
	}
	var cardType = $("#cardType").val();
	if(cardType == 0){
		$("#cardType").focus();
		alert('请选择卡类!');
		return;
	}
	
	var bankType = $("#bankType").val();
	if(bankType == ""){
		alert('请选择银行');return;
	}
	
	var domicile = $("#domicile").val();
	if(domicile == null || domicile == 0){
		alert('请选择注册地');return;
	}
	
	var mobile = $("#mobile").val();
	if(mobile == ''){
		$("#mobile").focus();
		alert("请填写手机号码");
		return;
	}
	
	var checkCode = $("#checkCode").val();
	if(checkCode == ''){
		$("#checkCode").focus();
		alert("请填写验证码");
		return;
	}else{
		var result = $("#checkResult").val();
		if(result == 'error'){
			alert('请输入正确的验证码!');return;
		}
	}
	
	/*var isIdCardMust = $("#isIdCardMust").val();
	if(isIdCardMust == 'notPass'){
		alert('请上传证件头像');return;
	}*/
	
	var receiveAddress = $("#receiveAddress").val();
	if(receiveAddress == ''){
		$("#receiveAddress").focus();
		alert("请选择领卡网点");
		return;
	}
	
	var firstName = $("#firstName").val();
	if(firstName == ''){
		$("#firstName").focus();
		alert("请填写姓氏拼音");
		return;
	}
	
	var nameReg = /^[a-zA-Z]+$/;
	var result = nameReg.test(firstName);
	if(!result){
		alert('姓氏拼音只能是字母');return;
	}
	
	var lastName = $("#lastName").val();
	if(lastName == ''){
		$("#lastName").focus();
		alert("请填写名字拼音");
		return;
	}
	
	var result = nameReg.test(lastName);
	if(!result){
		alert('名字拼音只能是字母');return;
	}
	
	var gender = $("input[name='gender']:checked").val();
	if(gender == undefined || gender== 'on'){
		alert('请选择性别');return;
	}
	
	var idcardValid = $("#idcardValid").val();
	if(idcardValid == ''){
		$("#idcardValid").focus();
		alert("请选择证件到期日期");
		return;
	}
	
	var britherday = $("#britherday").val();
	if(britherday == ''){
		$("#britherday").focus();
		alert("请选择出生日期");
		return;
	}
	
	var FZJG = $("#FZJG").val();
	if(FZJG == ''){
		$("#FZJG").focus();
		alert("请填写证件发证机关");
		return;
	}
	
	var age = $("input[name='age']:checked").val();
	if(age == 'lt'){//如果未满18岁，必须填写监护人信息
		var guardianCode = $("#guardianCode").val();
		if(guardianCode == ''){
			alert('请填写监护人证件号');return;
		}
		var guardianName = $("#guardianName").val();
		if(guardianName == ''){
			alert('请填写监护人姓名');return;
		}
	}
	
	
	var nation = $("#nation").val();
	if(nation == null || nation == 0){
		$("#nation").focus();
		alert('请选择民族');return;
	}
	
	var workUnitName = $("#workUnitName").val();
	if(workUnitName == ''){
		$("#workUnitName").focus();
		alert("请填写工作单位");
		return;
	}
	
	var professionCode = $("#professionCode").val();
	if(professionCode == null || professionCode == 0){
		$("#professionCode").focus();
		alert('请选择职业代码');return;
	}
	
	var workState = $("#workState").val();
	if(workState == null || workState == 0){
		$("#workState").focus();
		alert('请选择人员状态');return;
	}
	
	var householdRegister = $("#householdRegister").val();
	if(householdRegister == null || householdRegister == 0){
		$("#householdRegister").focus();
		alert('请选择户口性质');return;
	}
	
	var householdAddress = $("#householdAddress").val();
	if(householdAddress == ''){
		$("#householdAddress").focus();
		alert("请填写户口地址");
		return;
	}
	
	var domicile1 = $("#domicile1").val();
	if(domicile1 == null || domicile1 == 0){
		alert("请选择通讯地址");return;
	}
	
	var postalAddress = $("#postalAddress").val();
	if(postalAddress == ''){
		$("#postalAddress").focus();
		alert("请填写通讯地址");
		return;
	}
	
	var email = $("#email").val();
	if(email == ''){
		alert('请填写电子邮箱');
	}else{
		var reg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
		var b = reg.test(email);
		if (!b){
			alert('请填写正确的电子邮箱');
			$(".email").focus();return;
		}
	}
	
	var clothes = $("#clothes").val();
	if(clothes == null || clothes == 0){
		$("#clothes").focus();
		alert('请选择衣服大小');return;
	}
	
	disp_confirm();
	
	
/*	$.ajax({
	    type: 'post',
	    dataType:'json',
	    url: path + "/bankApplyCard/saveBankApplyCard",
	    data: $("#bankApplyCardForm").serialize(),
	    success: function(data){
	       alert(data.msg);
	       var cardType = $("#cardType").val();
	       if(cardType == 2){
	    	   var regUrl = $("#regUrl").val();
	    	   if(regUrl != 'null'){
	    		   window.location.href = regUrl;
	    	   }
	       }
	    },error:function(){
	       alert("提交失败,请联系管理员!");
	    }
	});*/
	
}

function disp_confirm(){
    var r=confirm("提交资料前请核实信息是否准确无误，一旦提交将无法再进行修改");
    if (r==true) {
        $.ajax({
            type: 'post',
            dataType:'json',
            url: path + "/bankApplyCard/saveBankApplyCard",
            data: $("#bankApplyCardForm").serialize(),
            success: function(data){
                alert(data.msg);
                var cardType = $("#cardType").val();
                if(cardType == 2){
                    var regUrl = $("#regUrl").val();
                    if(regUrl != 'null'){
                        window.location.href = regUrl;
                    }
                }
            },error:function(){
                alert("提交失败,请联系管理员!");
            }
        });
    }
    else {
        return;
    }
}

function fileUpload(){
	var fileName = $("#file1").val();
	if(fileName == ''){
		alert('请选择照片');return;
	}
	if(fileName.indexOf('.jpg') < 0 && fileName.indexOf('.JPG') < 0){
		alert('照片只能是jpg格式');return;
	}
	$("#darkCover").show();
	$.ajaxFileUpload({  
        url:path+"/bankApplyCard/fileUpload",          
        secureuri:false,  
        fileElementId:'file1',                         //文件选择框的id属性  
        dataType: 'json',                            
        success: function (data, status)            
        {   
        	$("#darkCover").hide();
           	var result = data.result;
            if(result == 'success'){
            	$(".idcardImg").attr("src", path + "/bankApplyCard/showidCardImage?time="+new Date().getTime());
            	$("#isIdCardMust").val("pass");
            	$("#imgUrl").val(data.imgUrl);
            }
            alert(data.msg);
        },  
        error: function (data, status, e)             
        {  
        	$("#darkCover").hide();
            alert("上传图片失败!"); 
        }  
      }  
    );  
}

//刷新随机码图片
function refresh(){
  	 var idVerifyCode= document.getElementById("idVerifyCode");
     idVerifyCode.style.display="inline";
     //idVerifyCode.src="captcha.htm";
     idVerifyCode.src=path+'/api/common/randomcode/staticimge4regist?t='+new Date().getTime();
}