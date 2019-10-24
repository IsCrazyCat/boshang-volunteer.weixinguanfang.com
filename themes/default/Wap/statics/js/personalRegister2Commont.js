$(function(){
	

	
	
	//姓名汉字合并userName
	$('#firstnameCn').bind('input propertychange', function() { 
		   var s1 =$(this).val();
		   var s2 =$("#lastnameCn").val();
		   var s3 =s1+s2;
		$('#userName').val(s3);
		
	});
    $('#lastnameCn').bind('input propertychange', function() { 
    	   var s1 =$(this).val();
		   var s2 =$("#firstnameCn").val();
		   var s3 =s2 +s1;
		
		$('#userName').val(s3);
		
	});

    
    //未成年证件照上传
    $('#sfzText2').click(function() {
		$('#fileInput2').click();
	});
     
    $("#fileInput2").on("change", function(){    
    	
    	uploadIdPic2();  
    }); 
    

    //未成年证件照上传
    $('#sfzText3').click(function() {
		$('#fileInput3').click();
	});
     
   $("#fileInput3").on("change", function(){    
    	
    	uploadIdPic3();  
    }); 
   //港澳台上传证件照
   $('#sfzText4').click(function() {
		$('#file2').click();
	});
    
  $("#file2").on("change", function(){    
   	
   	uploadIdPic4();  
   }); 

	
});

//打开领卡网点


function getbankingOut(){
	$('.fullxi').show();//显示遮罩
	
	$('#getbankCar').show();//显示领卡网点
	initGetBank();//初始化化领卡晚点数据
	
}
//领卡网点关闭

function getbankCar_close(){
	  $('.fullxi').hide();//显示遮罩
		
	 $('#getbankCar').hide();//显示领卡网点
}
//领卡网点   初始化 
function  fn1(){
	
	
}
function initGetBank(){
	
	
	   
	 $("#datalistTB").html("");
	
	var province = $("#oidProvince").val();
	console.log($("#oidProvince").val())
	console.log($("#oidCity").val())
	console.log($("#bankName").val())
	console.log($('#bankType').val())
	$.ajax({
		type: "POST",
		url: path+'/bankBranch/getBankBranchMsg',
		
		data: {
			"province":0,
			"city":0,
			"bankName":$("#bankName").val(),
			"bankType":$('#bankType').val(),
			"page": 1,
			 "rows":10
			
			
		},
		dataType: "json",
		success: function(data) {
			var html="";
				html+='<thead>' 
				html+= '<tr>' 
				html+= '<th  style="width:10%;"></th>' 
				html+='<th  style="width:20%;">网点名称</th>'  
				html+='<th  style="width:20%;">地址</th> ' 
				html+='<th style="width:20%;">联系电话</th>'  
			           
				html+=' </tr> '
				html+='</thead>' 
			
			
			for(var i =0;i<data.rows.length;i++){
				   html+=' <tr> '
					   html+='<td><input type="radio" name="GetBank" a="'+data.rows[i].branchName+'"  /></td> ' ;
					    html+='<td>'+data.rows[i].branchName+'</td> ' ;
					    html+='<td>'+data.rows[i].address+'</td> '  ;
					    html+='<td>'+data.rows[i].contact+'</td> '  ;
			           
					    html+=' </tr> '
			}
            $("#datalistTB").append(html);
			
		},
		error: function() {
			alert("请填写归属组织！");
		}
	});
}

//领卡网点地市搜索
function searchBank2(){
	
	
	
	   
	$("#datalistTB").html("");
	var province = $("#idProvince").val();
	var city = $("#idCity").val();
	var bankName = $("#bankName").val();
	var bankType =$('#bankType').val();
	console.log($("#oidProvince").val())
	console.log($("#oidCity").val())
	console.log(bankName)
	console.log(bankType)
	$.ajax({
		type: "POST",
		url: path+'/bankBranch/getBankBranchMsg',
		
		data: {
			"province":$("#oidProvince").val(),
			"city":$("#oidCity").val(),
			"bankName":bankName,
			"bankType":bankType
			
		},
		dataType: "json",
		success: function(data) {
			var html="";
				html+='<thead>' 
				html+= '<tr>' 
				html+= '<th  style="width:10%;"></th>' 
				html+='<th  style="width:20%;">网点名称</th>'  
				html+='<th  style="width:20%;">地址</th> ' 
				html+='<th style="width:20%;">联系电话</th>'  
			           
				html+=' </tr> '
				html+='</thead>' 
			
			
			for(var i =0;i<data.rows.length;i++){
				   html+=' <tr> '
					   html+='<td><input type="radio" name="GetBank" a="'+data.rows[i].branchName+'" b="'+data.rows[i].branchCode+'"     /></td> ' ;
					    html+='<td>'+data.rows[i].branchName+'</td> ' ;
					    html+='<td>'+data.rows[i].address+'</td> '  ;
					    html+='<td>'+data.rows[i].contact+'</td> '  ;
			           
					    html+=' </tr> '
			}
          $("#datalistTB").append(html);
			
		},
		error: function() {
			alert("请填写归属组织！");
		}
	});
	
	
	
	
	
	
}

//未成年人证件照上传函数
function uploadIdPic2(){

	$('#sfzText2').val($('#fileInput2').val());
	if($('#sfzText2').val()==''){
		alert("请上传证件照片！");
		return false;
	}
	var location=$('#fileInput2').val(); 
    var point = location.lastIndexOf("."); 
    var type = location.substr(point); 
     if(type!=".jpg"&&type!=".gif"&&type!=".JPG"&&type!=".GIF"&&type!=".png"&&type!=".PNG"&&type!=".jpeg"&&type!=".JPEG"){ 
    	 alert("上传的文件格式不正确！");
		 return false;      
     } 
	$.ajaxFileUpload({
		url:path4api+'/api/fileUpload/uploadImage.do?filename='+$("#sfzText2").val(),
		secureuri:false,
		fileElementId:'fileInput2',
		dataType: 'json',
		cache:false,
		data:{filename: $("#sfzText2").val()},
		success: function (data, status){
			if(data.status == 'OK'){
				$('#idpicPath').val(data.relative_path);
				$('#child_img').attr('src',data.path).show();
				alert('上传成功');
				
			}else{
				$('#sfzText2').val('');
				alert(data.msg);
			}
		},  
        error: function (data, status, e){
        	alert("上传失败!");
        	$("#sfzText2").val('');
        }  
	});
	$("#fileInput2").replaceWith('<input type="file" class="upload_file" name="papers" id="fileInput2" style="display: none;"/>');    
    $("#fileInput2").on("change", function(){    
    	uploadIdPic2();  
    }); 
}


//未成年人证件照上传函数
function uploadIdPic3(){

	$('#sfzText3').val($('#fileInput3').val());
	if($('#sfzText3').val()==''){
		alert("请上传证件照片！");
		return false;
	}
	var location=$('#fileInput3').val(); 
    var point = location.lastIndexOf("."); 
    var type = location.substr(point); 
     if(type!=".jpg"&&type!=".gif"&&type!=".JPG"&&type!=".GIF"&&type!=".png"&&type!=".PNG"&&type!=".jpeg"&&type!=".JPEG"){ 
    	 alert("上传的文件格式不正确！");
		 return false;      
     } 
	$.ajaxFileUpload({
		url:path4api+'/api/fileUpload/uploadImage.do?filename='+location,
		secureuri:false,
		fileElementId:'fileInput3',
		dataType: 'json',
		cache:false,
		data:{filename: location},
		success: function (data, status){
			if(data.status == 'OK'){
				$('#idpicPath').val(data.relative_path);
				$('#child_img3').attr('src',data.path).show();
				alert('上传成功');
				
			}else{
				$('#sfzText3').val('');
				alert(data.msg);
			}
		},  
        error: function (data, status, e){
        	alert("上传失败!");
        	$("#sfzText3").val('');
        }  
	});
	$("#fileInput3").replaceWith('<input type="file" class="upload_file" name="papers" id="fileInput3" style="display: none;"/>');    
    $("#fileInput3").on("change", function(){    
    	uploadIdPic3();  
    }); 
}
//确定取卡网点

function getbankCarFn(){
	
	$('input[name=GetBank]:checked').attr("a");
	$('input[name=GetBank]:checked').attr("");//获取code
	$('#receiveAddress').val($('input[name=GetBank]:checked').attr("a"));
	$('#bankingOutlets').val($('input[name=GetBank]:checked').attr("b"));
	 $('.fullxi').hide();//显示遮罩
		
	 $('#getbankCar').hide();//显示领卡网点
	
}

//显示组织归属框
//$("#workingAddressDiv").click(function() {
//	$('#gsdw,#fullxi').show();
//	gsdwULItem("40288188119c102f01119cadc42d01d0");//40288188119c102f01119cadc42d01d0--全国；b0dc9771d14211e18718000aebf5352e--聊城市
//});

function workingAddressDivFun(n){
	
	$('#cancelbtn').attr("index",n);//保存index的id值为id
	
	$('#gsdw,#fullxi').show();
	gsdwULItem("40288188119c102f01119cadc42d01d0");//40288188119c102f01119cadc42d01d0--全国；b0dc9771d14211e18718000aebf5352e--聊城市
	
}
function gsdwULItem(districtParentId) {
	
	if (districtParentId == "b0dc9771d14211e18718000aebf5352e") {//40288188119c102f01119cadc42d01d0--全国；b0dc9771d14211e18718000aebf5352e--聊城市
		$("#gsdw #upLayer").hide();
	} else {
		$("#gsdw #upLayer").show();
	}

	var url = path+"/api/wx/district/getOrgListJson.do";
	$.ajax({
		type: "POST",
		url: url,
		data: {
			districtParentId: districtParentId
		},
		dataType: "xml",
		success: function(data) {
			if ($(data).find("item").size() == 0) return false;
			$("#gsdwUL").empty();
			$.each($(data).find("item"),
			function(i) {
				if ($(this).attr("flag") == "yes") {
					if ($(this).attr("districtName") == "地市" || $(this).attr("districtName") == "行业" || $(this).attr("districtName") == "高校" ||
							   $(this).attr("districtLevel")<=2) {
						$("#gsdwUL").append("<li class='tmt-fz_mLiBgImg' val='" + $(this).attr("districtId") + "'><font style='float: left; display: block; width: 100%;overflow: hidden; text-overflow: ellipsis;white-space: nowrap;'>" + $(this).attr("districtName") + "</font></li>");
					} else {
						$("#gsdwUL").append("<li class='tmt-fz_mLiBgImg' val='" + $(this).attr("districtId") + "'><font style='float: left; display: block; width: 80%;overflow: hidden; text-overflow: ellipsis;white-space: nowrap;'>" + $(this).attr("districtName") + "</font><input style='margin-left:10px;border: 1px solid #999999;' name='radioItem' val='" + $(this).attr("districtName") + "' type='radio'/></li>");
					}
					$("#gsdwUL li").eq(i).find("font").click(function() {
						gsdwULItem($(this).parent().attr("val"));
						$("#upLayer").attr("val", $(this).parent().attr("PPid"));
					});
				} else {
					$("#gsdwUL").append("<li val='" + $(this).attr("districtId") + "'><font style='float: left; display: block; width: 80%;overflow: hidden; text-overflow: ellipsis;white-space: nowrap;'>" + $(this).attr("districtName") + "</font><input style='margin-left:10px;border: 1px solid #999999;' name='radioItem' val='" + $(this).attr("districtName") + "' type='radio'/></li>");
				}

			});

			/* var timer = null;
						$("#gsdwUL li").on("click",function() { //单击事件
						   clearTimeout(timer);
						var tempVal = $(this).attr("val");
						   timer = setTimeout(function() { //在单击事件中添加一个setTimeout()函数，设置单击事件触发的时间间隔
						   	gsdwULItem(tempVal);
						   },300);

						});
						$("#gsdwUL li").on("dblclick",function() { //双击事件
						$(this).css("background-color","red");
						   clearTimeout(timer); //在双击事件中，先清除前面click事件的时间处理
						}); */

			/*  var startTime = 0;
						 var endTime = 0;
						 $("#gsdwUL li").on("touchstart", function(event) {
						 	startTime = new Date().getTime();
						 });
						 $("#gsdwUL li").on("touchend", function(event) {
						 	endTime = new Date().getTime() - startTime;
							if(endTime < 1000){
						    	$("#StrTest").html(endTime);
								gsdwULItem($(this).attr("val"));
							}else{
						    	$("#StrTest").html(endTime);
								$(this).css("background-color","red");
							}
						 }); */

		},
		error: function() {
			alert("请填写归属组织！");
		}
	});

	/* function go(thisObj){
			gsdwULItem(thisObj.attr("val"));
		} */
}

