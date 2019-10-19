
	$(function() {
		
		function gsdwULItem(districtParentId) {
			if (districtParentId == "b0dc9771d14211e18718000aebf5352e") {//40288188119c102f01119cadc42d01d0--全国；b0dc9771d14211e18718000aebf5352e--广东
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


				},
				error: function() {
					alert("请填写归属组织！");
				}
			});

		}
		
		$("#backbtn").click(function(){
			var currentDisId = $("#gsdwUL li").attr("val");
			$.ajax({
				url : path+"/api/wx/district/findUpperDistrictId.do",
				data : {
					"currentDisId" : currentDisId
				},
				type : "post",
				async : true,
				dataType : "json",
				success : function(json) {
					if (json[0].status == "true") {
						var upperDisId = json[0].upperDisId;
						gsdwULItem(upperDisId);
						//$("#upLayer").attr("val", $("#gsdwUL li").attr("PPid"));
					}else{
						alert(json[0].message);
					}
				},
				error : function() {
					r = false;
				}
			});
		});
		
		$("#searchDis").click(function(){
			var districtName= $("#inputText").val();
			$.ajax({
				url : path+"/api/wx/district/searchDistrictJson.do",
				data : {
					"districtName" : districtName
				},
				type : "post",
				async : true,
				dataType : "json",
				success : function(data) {
					if(data.code=="2"){
						alert(data.msg);
						return false;
					}
					$("#gsdwUL").empty();
					$.each(data.data,function(i){
						var districtId = data.data[i].districtId;
						var districtName = data.data[i].districtName;
						$("#gsdwUL").append("<li val='" + districtId + "'><font style='float: left; display: block; width: 80%;overflow: hidden; text-overflow: ellipsis;white-space: nowrap;'>" + districtName + "</font><input style='margin-left:10px;border: 1px solid #999999;' name='radioItem' val='" + districtName + "' type='radio'/></li>");
					});
				},
				error : function() {
					r = false;
				}
			});
		});



		$('#gsdw #cancelbtn').click(function() {
			
			$('#gsdw,#fullxi').hide();
			//$("#districtName").val($("input[name='radioItem']:checked").attr("val"));
			var id=$(this).attr('index');
			
			$("#districtName"+id).val($("input[name='radioItem']:checked").attr("val"));
			
			
			$("#districtId"+id).val($("input[name='radioItem']:checked").parents("li").attr("val"));
			$("#inputText").val('');
			
		});
//		
		$('#gsdw_close').click(function() {
			$('#gsdw,#fullxi').hide();
			$("#districtName").val('');
			$("#districtId").val('');
			$("#inputText").val('');
		});

		$('#fwxyLink').click(function() {
			$('#fwxywindow,#fullxi').show();
		});

		$("#upLayer").click(function() {
			if ($("#districtPid").val() != "") {
				gsdwULItem($(this).attr("val"));
			}
		});
		
		$('#subnt').click(function(){
			var province = $("#idProvince").is(":hidden")?"":$("#idProvince option:selected").text();
			var city = $("#idCity").is(":hidden")?"":$("#idCity option:selected").text();
			var sarea = $("#idArea").is(":hidden")?"":$("#idArea option:selected").text();
			var addr = $("#addr").val();
			var str = '';
			
			
			if($("#idArea").is(":hidden")){
				$("#hasArea").val("0");
			}else{
				$("#hasArea").val("1");
			}
			
			
			
			if((province+city+sarea+addr).indexOf("请选择")<0){
				str = province+city+sarea+addr;
					hideDiv('popup');
				/*  if($("#addr").val()!=""){
				}else{
					mylayer("请填写完整！", $("#addr"));
				}  */
			}else{
				if(province == "请选择"){
					$("#address").val("");
					hideDiv('popup');
				}else if(city == "请选择"){
					str = province+addr;
					//mylayer("请填写完整！", $("#city"));
				}else if(sarea == "请选择"){
					str = province+city+addr;
					//mylayer("请填写完整！", $("#idArea"));
				}else{
					str = province+city+sarea+addr;
				}
			}
			$("#address").val(str);
			$("#censusAddress").val(str);
			hideDiv('popup');
			return false;
		});
		
	    
	    $('#sfzText').click(function() {
			$('#fileInput').click();
		});
	    $('#fileInput').change(function(){
	    	
	    	uploadIdPic()
			$("#fileInput").replaceWith('<input type="file" class="upload_file" name="papers" id="fileInput" style="display: none"/>');    
		    $("#fileInput").on("change", function(){    
		    	uploadIdPic();  
		    });  
		});
	    
	    $("#address").val("");
		

		

		$("#idcardCode").change(function(){
			$("#gender").val(getSexByIdcard($("#idcardCode").val()));	
		});
		/*证件类型变化后  的显示隐藏变化*/
		$('#idcardType').blur(function(){
			//alert($('#idcardType').val());
			var idCardType = $('#idcardType').val();
			var idcardCode = $('#idcardCode').val()
			
			if(idCardType != 1){// 非内地居民身份证，展示并要求填写证明人
				$('#showAuthDiv').show();
				
				$("#idpicPathDiv").show();
				$('#guoji').hide();//隐藏国籍
				
				$("#jiguan").show();//显示籍贯
				if(idCardType == 4){// 非中国国籍
					$('#guoji').show();//显示国籍
					
					$("#jiguan").hide();//隐藏籍贯
				}
				
			}else{// 内地居民身份证
				$("#idpicPathDiv").hide();
				$("#sfzText").val(null);
				$("#sfz_model").attr("src",null);
				
				$('#showAuthDiv').hide();
				$("#showAuthDiv input").each(function(){
				    $(this).attr("disable","disable");
				});
				$('#guoji').hide();//隐藏国籍
				
				$("#jiguan").show();//显示籍贯
			}
			checkBirthDay();
		});
		
		$("#birthday").change(function(){
			var age;
			var idCardType = $('#idcardType').val();
			//alert($('#yearInput').val());
			age = getAgeByBirthday($('#birthday').val());
			//alert(age);
			if(age <= 18 && idCardType == 1){// 未满18岁的中国公民要展示并填写监护人信息
				$("#linkmanDiv").show();
			}else{
				$("#linkmanDiv").hide();
			}
		});
		
		//监护人身份证校验
	    $("#linkidentity").blur(function(){
	    	var identityCard = $(this).val();
	    	if(!IdentityCodeValid(identityCard)){
	    		alert("错误的监护人身份证号码，请核对！");
	            return;
	    	}
	    });
	    
		$.getJSON(path+"/region/region/getCountryList",function(data){
			$("#censusCountry").empty();
	 		var city = "";
	 		city += "<option value=''>请选择国籍</option>";
			$.each(data,function(){
				city += "<option id='"+this.countryCode+"' value='"+this.countryCode+"'>"+this.countryName+"</option>";
			});
			$("#censusCountry").append(city);

	 	});
	});
	
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
	 * 内地居民身份证自动填充出生日期,否则手动填写
	 */
	function checkBirthDay(){
		var idCardType = $('#idcardType').val();
		var idcardCode = $('#idcardCode').val();
		var birthday;
		var gender;
		//alert(idCardType);
		if(!idcardCode){
			return false;
		}
		if(idCardType == 1){// 证件类型为身份证
			birthday = getBirthdatByIdcard(idcardCode);
			gender = getSexByIdcard(idcardCode);
			$('#birthday').val(birthday).change();
			$('#gender').val(gender).change();
			$('#birthday').attr("readonly","readonly");
			$('#gender').attr("readonly","readonly");
		}else{
			$('#birthday').removeAttr("disabled");
		}
		//alert(birthday);
	}
	
	function closeBox(){
		$("#popup").css('display','none'); 
		$("#backgroundPopup").remove();
	}  
	 

	  
	 function hideDiv(div_id) {   
		$("#backgroundPopup").remove();   
		$("#" + div_id).hide(0).css({left: 0, top: 0});   
	} 

	 function uploadIdPic(){
			$('#sfzText').val($('#fileInput').val());
			if($('#sfzText').val()==''){
				alert("请上传证件照片！");
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
					
					alert("哈哈哈")
					if(data.status == 'OK'){
						$('#sfzText').val(data.relative_path);
						$('#sfz_model').attr('src',data.path).show();
						alert('上传成功');
						
					}else{
						$('#sfzText').val('');
						alert(data.msg);
					}
				},  
		        error: function (data, status, e){
		        	alert("上传失败!");
		        	$("#sfzText").val('');
		        }  
			});
		}

	 function trim(s) { return s.replace(/^\s+|\s+$/g, ""); };
	//验证身份证号并获取出生日期
	 function getBirthdatByIdcard(idcardCode) {
		 var tmpStr = "";
		 var idDate = "";
		 var tmpInt = 0;
		 var strReturn = "";
	
		 idcardCode = trim(idcardCode);
	
		 if ((idcardCode.length != 15) && (idcardCode.length != 18)) {
			 strReturn = "0000-00-00";//输入的身份证号位数错误
			 return strReturn;
		 }
	
		 if (idcardCode.length == 15) {
			 tmpStr = idcardCode.substring(6, 12);
			 tmpStr = "19" + tmpStr;
			 tmpStr = tmpStr.substring(0, 4) + "-" + tmpStr.substring(4, 6) + "-" + tmpStr.substring(6)
		
			 return tmpStr;
		 }
		 else {
			 tmpStr = idcardCode.substring(6, 14);
			 tmpStr = tmpStr.substring(0, 4) + "-" + tmpStr.substring(4, 6) + "-" + tmpStr.substring(6)
		
			 return tmpStr;
		 }
	 }
	 
	 function getSexByIdcard(idcardCode){
		    var sexno,sex
		    if(idcardCode.length==18){
		        sexno=idcardCode.substring(16,17)
		    }else if(idcardCode.length==15){
		        sexno=idcardCode.substring(14,15)
		    }else{
		    	sex='0'
		    }
		    var tempid=sexno%2;
		    if(tempid==0){
		        sex='2'
		    }else{
		        sex='1'
		    }
		    return sex
		}
 /**
  * 国内居民身份证校验
  */
 function IdentityCodeValid(code) { 
     var city={11:"北京",12:"天津",13:"河北",14:"山西",15:"内蒙古",21:"辽宁",22:"吉林",23:"黑龙江 ",31:"上海",32:"江苏",33:"浙江",34:"安徽",35:"福建",36:"江西",37:"山东",41:"河南",42:"湖北 ",43:"湖南",44:"广东",45:"广西",46:"海南",50:"重庆",51:"四川",52:"贵州",53:"云南",54:"西藏 ",61:"陕西",62:"甘肃",63:"青海",64:"宁夏",65:"新疆",71:"台湾",81:"香港",82:"澳门",91:"国外 "};
     var tip = "";
     var pass= true;
     
     if(!code || !/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[12])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i.test(code)){
         tip = "身份证号格式错误";
         pass = false;
     }
     
    else if(!city[code.substr(0,2)]){
         tip = "地址编码错误";
         pass = false;
     }
     else{
         //18位身份证需要验证最后一位校验位
         if(code.length == 18){
             code = code.split('');
             //∑(ai×Wi)(mod 11)
             //加权因子
             var factor = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 ];
             //校验位
             var parity = [ 1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2 ];
             var sum = 0;
             var ai = 0;
             var wi = 0;
             for (var i = 0; i < 17; i++)
             {
                 ai = code[i];
                 wi = factor[i];
                 sum += ai * wi;
             }
             var last = parity[sum % 11];
             if(parity[sum % 11] != code[17]){
                 tip = "校验位错误";
                 pass =false;
             }
         }
     }
     //if(!pass) alert(tip);
     return pass;
 }
 
 
 areaCity();
 
 function areaCity(){
	 
	 $.ajax({
			url:path4api+"/region/region/getRegionList.do",
			dataType:"json",
			success:function(data){
				$("#idProvince").append("<option value='0'>请选择</option>");
				$.each(data,function(i){
					$("#idProvince").append("<option value='"+data[i].regionId+"'>"+data[i].name+"</option>");
				});
			},
			error:function(){}
		});
 }

 
 
 
 