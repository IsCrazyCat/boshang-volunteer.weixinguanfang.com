	var sendSmsTime = 0;
	var imgVerifyCodeFlag = false;
	var sendFlag = true;
	$(function() {
		refreshVerifyCode('idVerifyCode');
		//initWangDian();
		//alert("告知：由于系统遭受第三方恶意注册刷短信验证码，为保障系统安全，系统短信功能暂时关闭，用户注册的短信验证码暂停发送。我方技术正全力解决，抱歉带来不便，敬请谅解！");
		layer.config({
			extend : '../layer/extend/layer.ext.js'
		});
		$("#sendcode_AID").removeAttr("disabled");
		$("#address").val("");
		
		$.ajax({
			url : path+"/region/region/getRegionsListJson.do",
			dataType : "json",
			success : function(data) {
				$.each(data.data, function(i) {
					$("#province").append(
							"<option value='"+data.data[i].id+"'>"
									+ data.data[i].name + "</option>");
				});
			},
			error : function() {
			}
		});

		$("#province").change(
				function() {
					$.ajax({
						url : path+"/region/region/getRegionsListJson.do",
						data : {
							regionId : $(this).val()
						},
						dataType : "json",
						success : function(data) {
							$("#city option").not(":first").remove();
							$.each(data.data, function(i) {
								$("#city").append(
										"<option value='"+data.data[i].id+"'>"
												+ data.data[i].name
												+ "</option>");
							});
						},
						error : function() {
						}
					});
				});

		$("#city").change(
				function() {
					$.ajax({
						url : path+"/region/region/getRegionsListJson.do",
						data : {
							regionId : $(this).val()
						},
						dataType : "json",
						success : function(data) {
							$("#sarea option").not(":first").remove();
							if (data.data.length != 0) {
								$("#sarea").parent().show();
								$.each(data.data, function(i) {
									$("#sarea").append(
											"<option value='"+data.data[i].id+"'>"
													+ data.data[i].name
													+ "</option>");
								});
							} else {
								$("#sarea").parent().hide();
							}
						},
						error : function() {
						}
					});
				});

		/* 	//同意协议操作
			$("#isAgree").removeAttr("checked");
			$("#button2").attr("disabled","disabled");
			$("#isAgree").click(function(){
				if($(this).is(":checked")){
					$("#button2").removeAttr("disabled");
				}else{
					$("#button2").attr("disabled","disabled");
				}
			}); */

		/* 		//民族select加载
		 $("#mingzu").mingzuListSelect();
		 //政治面貌
		 $("#zzmm").zzmmListSelect();
		 */

		$("#idcardType").change(function() {
			$("#idcardCode").val("");
			if ($(this).val() == "1") {
				$("#YYYY").attr("disabled", "disabled");
				$("#MM").attr("disabled", "disabled");
				$("#DD").attr("disabled", "disabled");
				//$("#gender").attr("disabled","disabled");
			} else {
				$("#YYYY").removeAttr("disabled");
				$("#MM").removeAttr("disabled");
				$("#DD").removeAttr("disabled");
				//$("#gender").removeAttr("disabled");
			}
		});

		$("#idcardCode")
				.blur(
						function() {
							if ($("#idcardType").val() == "1") {
								$("#YYYY").find("option").attr("selected",
										false);
								$("#MM").find("option").attr("selected", false);
								$("#DD").find("option").attr("selected", false);

								$("#YYYY").attr("disabled", "disabled");
								$("#MM").attr("disabled", "disabled");
								$("#DD").attr("disabled", "disabled");

								if (checkIdcardByCard($(this).val()) != "验证通过!") {
									mylayer(checkIdcardByCard($(this).val()),
											$(this));
									return false;
								}

								//检查身份证号码唯一性，ajax;  注：组织注册不需要验证身份证号码唯一性
								/* var thisObj = $(this);
								if (thisObj.val() != "") {
									$.ajax({
												url : "${path}/user/userLogin/checkIdcardCodeUnique.do",
												data : {
													"idcardCode" : thisObj
															.val()
												},
												type : "post",
												dataType : "json",
												success : function(json) {
													if (json[0].status == "true") {
														mylayer("身份证已存在请重新输入！",
																thisObj);
														return false;
													}
												},
												error : function() {
													mylayer("无法判断，请重试！",
															thisObj);
													return false;
												}
											});
								} */

								/* if ($(this).val().length == 15) {
									$("#YYYY").find(
											"option[value='19"
													+ parseInt($(this).val()
															.substring(6, 8))
													+ "']").get(0).selected = true;
									$("#MM").find(
											"option[value='"
													+ parseInt($(this).val()
															.substring(8, 10))
													+ "']").get(0).selected = true;
									$("#DD").find(
											"option[value='"
													+ parseInt($(this).val()
															.substring(10, 12))
													+ "']").get(0).selected = true;

									$("#gender").find("option").removeAttr(
											"selected");
									if (parseInt($(this).val()
											.substring(14, 15)) % 2 == "0") {
										$("#gender").find("option[value='2']")
												.get(0).selected = true;
									} else {
										$("#gender").find("option[value='1']")
												.get(0).selected = true;
									}
								}
								if ($(this).val().length == 18) {
									$("#YYYY").find(
											"option[value='"
													+ parseInt($(this).val()
															.substring(6, 10))
													+ "']").get(0).selected = true;
									$("#MM").find(
											"option[value='"
													+ parseInt($(this).val()
															.substring(10, 12))
													+ "']").get(0).selected = true;
									$("#DD").find(
											"option[value='"
													+ parseInt($(this).val()
															.substring(12, 14))
													+ "']").get(0).selected = true;

									$("#gender").find("option").removeAttr(
											"selected");
									if (parseInt($(this).val()
											.substring(16, 17)) % 2 == "0") {
										$("#gender").find("option[value='2']")
												.get(0).selected = true;
									} else {
										$("#gender").find("option[value='1']")
												.get(0).selected = true;
									}
								} */
							}

							$(this).css("border", "1px solid #ccc");
						});

		/*对象失去焦点事件*/
		$("input[blurCheck='true']").blur(function() {
							if ($(this).val() == "") {
								mylayer("不能为空！", $(this));
								return false
							}

							if (loginNameOption()) {
								if ($(this).attr("id") == "userPassword") {
									if (!VePassWord($("#userPassword").val())) {
										mylayer("请填写6-12位字母和数字的组合！",
												$("#userPassword"));
										return false;
									}
								}

								if ($(this).attr("id") == "verifyUserPassword") {
									if (!VePassWord($("#verifyUserPassword").val())) {
										mylayer("请填写6-12位字母和数字的组合！",
												$("#verifyUserPassword"));
										return false;
									}
									if($("#userPassword").val() != $("#verifyUserPassword").val()){
										mylayer("两次输入的密码不一致！",$("#verifyUserPassword"));
										return false;
									}
								}

								if ($(this).attr("id") == "mobile") {
									if (!VeMobile($("#mobile").val())) {
										mylayer("手机号码格式有误！", $("#mobile"));
										return false;
									}
									/* if(!mobileUnique()){
										return false;
									} */
								}

								if ($(this).attr("id") == "email") {
									if (!VeEmail($("#email").val())) {
										mylayer("常用邮箱格式有误！", $("#email"));
										return false;
									}
									if(!emailUnique()){
										return false;
									}
								}
								
								
								if ($(this).attr("id") == "userName") {
									if (!isChineseChar($("#userName").val())) {
										mylayer("管理员姓名应为中文！", $("#userName"));
										return false;
									}
								}
								
								if ($(this).attr("id") == "idcardCode") {
									if (checkIdcardByCard($("#idcardCode").val())!= "验证通过!") {
										mylayer("身份证格式不正确！", $("#idcardCode"));
										return false;
									}
								}
								if ($(this).attr("id") == "teamname") {
									if(!checkteamNameUnique()){
										return false;
									}
								}

								$(this).css("border", "1px solid #ccc");
							}

						});

		$("select[blurCheck='true']").blur(function() {
			if ($(this).attr("id") == "zgxl") {
				if ($("#zgxl").val() == "最高学历") {
					mylayer("请选择最高学历！", $("#zgxl"));
					return false;
				}
			}
			if ($(this).attr("id") == "zzmmSelect") {
				if ($("#zzmmSelect").val() == "政治面貌") {
					mylayer("请选择政治面貌！", $("#zzmmSelect"));
					return false;
				}
			}
			if ($(this).attr("id") == "cyzk") {
				if ($("#cyzk").val() == "从业状况") {
					mylayer("请选择从业状况！", $("#cyzk"));
					return false;
				}
			}
			if ($(this).attr("id") == "recordType") {
				if ($("#recordType").val() == "是否登记备案") {
					mylayer("请选择是否登记备案！", $("#recordType"));
					return false;
				}
			}
			if ($(this).attr("id") == "teamClassifyType") {
				if ($("#teamClassifyType").val() == "组织/团体类别") {
					mylayer("请选择组织/团体类别！", $("#teamClassifyType"));
					return false;
				}
			}

			$(this).css("border", "1px solid #ccc");
		});

		$("#info_tishi").click(function() {
			layer.msg('建议使用身份证号！');
		});
		/**
		 * 身份证扫描件
		 */
		$('#sfz_tishi').click(function() {
			if($('#sfz_model').is(':visible')){
				$('#sfz_model').hide();
			}else{
				$('#sfz_model').show();
			}
		});
		$('#sfz_model').click(function() {
			$('#sfz_model').hide();
		});
		$('#sfzText').click(function() {
			$('#fileInput').click();
		});
		$('#fileInput').change(function(){
			$('#sfzText').val($(this).val());
			
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
		     
			$("#background").show();
			$("#progressBar").show();
			$.ajaxFileUpload({
				url:path+'/api/fileUpload/uploadImageAddWatermark.do?filename='+$("#sfzText").val(),
				secureuri:false,
				fileElementId:'fileInput',
				dataType: 'json',
				cache:false,
				data:{filename: $("#sfzText").val()},
				success: function (data, status){
					if(data.status == 'OK'){
						$('#sfzText').val(data.relative_path);
						$('#sfz_model').attr('src',data.path).show();
						$("#background").hide();
						$("#progressBar").hide();
					}else{
						$('#sfzText').val('');
					}
					layer.msg(data.msg);
				},  
	            error: function (data, status, e){
	            	$("#sfzText").val('');
	            }  
			});
			
		});
		
		/**
		 * 备案扫描件上传
		 */
		$('#sfz_tishi2').click(function() {
			if($('#sign_model').is(':visible')){
				$('#sign_model').hide();
			}else{
				$('#sign_model').show();
			}
		});
		$('#sign_model').click(function() {
			$('#sign_model').hide();
		});
		$('#signText').click(function() {
			$('#signInput').click();
		});
		$('#signInput').change(function(){
			$('#signText').val($(this).val());
			
			if($('#signText').val()==''){
				alert("请上传证件照片！");
				return false;
			}
			var location=$('#signInput').val(); 
		    var point = location.lastIndexOf("."); 
		    var type = location.substr(point); 
		     if(type!=".jpg"&&type!=".gif"&&type!=".JPG"&&type!=".GIF"&&type!=".png"&&type!=".PNG"&&type!=".jpeg"&&type!=".JPEG"){ 
		    	 alert("上传的文件格式不正确！");
				 return false;      
		     } 
		     
			$("#background").show();
			$("#progressBar").show();
			$.ajaxFileUpload({
				url:path+'/api/fileUpload/uploadImage.do?filename='+$("#signText").val(),
				secureuri:false,
				fileElementId:'signInput',
				dataType: 'json',
				cache:false,
				data:{filename: $("#signText").val()},
				success: function (data, status){
					if(data.status == 'OK'){
						$('#signText').val(data.relative_path);
						$('#sign_model').attr('src',data.path).show();
						$("#background").hide();
						$("#progressBar").hide();
					}else{
						$('#signText').val('');
					}
					layer.msg(data.msg);
				},  
	            error: function (data, status, e){
	            	$("#signText").val('');
	            }  
			});
			
		});
		
		$('#uploadButton').click(function(){
			if($('#sfzText').val()==''){
				alert("请上传证件照片！");
				return false;
			}
			$("#background").show();
			$("#progressBar").show();
			$.ajaxFileUpload({
				url:path+'/api/fileUpload/uploadImage.do?filename='+$("#sfzText").val(),
				secureuri:false,
				fileElementId:'fileInput',
				dataType: 'json',
				cache:false,
				data:{filename: $("#sfzText").val()},
				success: function (data, status){
					if(data.status == 'OK'){
						$('#sfzText').val(data.relative_path);
						$('#sfz_model').attr('src',data.path).show();
						$("#background").hide();
						$("#progressBar").hide();
					}else{
						$('#sfzText').val('');
					}
					layer.msg(data.msg);
				},  
	            error: function (data, status, e){
	            	$("#sfzText").val('');
	            }  
			});
		});
		
		
	});

	//是否是数字 	
	function isNumber(str) {

		var Letters = "1234567890"; //可以自己增加可输入值
		var i;
		var c;
		for (i = 0; i < str.length; i++) {
			c = str.charAt(i);
			if (Letters.indexOf(c) < 0)
				return false;
		}
		return true;
	}
	
	function checkForm() {
		var radioAgree = document.getElementById("isAgree").checked;
		if (radioAgree == false) {
			mylayer("你必须要同意服务协议才能提交，请确认同意", $("#isAgree"), "1");
			return false;
		}
		if ($("#loginName").val() == "") {
			mylayer("帐号不能为空！", $("#loginName"), "1");
			return false;
		}
		if ($("#inputVerifyCode").val() == "") {
			mylayer("验证码不能为空！", $("#inputVerifyCode"), "1");
			$("#gouImg").hide();
			return false;
		} else {
			if ($("#inputVerifyCode").val() == 'false') {
				mylayer("校验码错误，请重新输入", $("#inputVerifyCode"), "1");
				return false;
			}
		}

		if ($("#userPassword").val() == "") {
			mylayer("密码不能为空！", $("#userPassword"), "1");
			return false;
		}

		if ($("#verifyUserPassword").val() == "") {
			mylayer("确认密码不能为空！", $("#verifyUserPassword"), "1");
			return false;
		}

		if ($("#mobile").val() == "") {
			mylayer("手机号码不能为空！", $("#mobile"), "1");
			return false;
		}

		if ($("#email").val() == "") {
			mylayer("邮箱不能为空！", $("#email"), "1");
			return false;
		}
		
		if ($("#teamemail").val() == "") {
			mylayer("组织邮箱不能为空！", $("#teamemail"), "1");
			return false;
		}

		if ($("#userName").val() == "") {
			mylayer("姓名不能为空！", $("#userName"), "1");
			return false;
		}

		if ($("#idcardCode").val() == "") {
			mylayer("证件号不能为空！", $("#idcardCode"), "1");
			return false;
		}
		
		if ($("#sfzText").val() == "") {
			mylayer("证件图片不能为空！", $("#sfzText"), "1");
			return false;
		}

		if ($("#workingAddress").val() == "") {
			mylayer("归属组织不能为空！", $("#workingAddress"), "1");
			return false;
		}
		
		if ($("#address").val() == "") {
			mylayer("组织地址不能为空！", $("#address"), "1");
			return false;
		}
		
		if ($("#teamtotal").val() == "") {
			mylayer("组织/团体人数不能为空！", $("#teamtotal"), "1");
			return false;
		}
		if (!isNumber($("#teamtotal").val())) {
			mylayer("组织/团体人数为正整数！", $("#teamtotal"), "1");
			return false;
		}
		if ($("#teamintroduction").val() == "") {
			mylayer("组织/团体简介不能为空！", $("#teamintroduction"), "1");
			return false;
		}
		
		if ($("#addr").val() == "") {
			mylayer("组织详细地址不能为空！", $("#address"), "1");
			return false;
		}

		if ($("#idcardType").val() == "证件类型") {
			mylayer("请选择证件类型！", $("#idcardType"), "1");
			return false;
		}

		if ($("#zgxl").val() == "最高学历") {
			mylayer("请选择最高学历！", $("#zgxl"), "1");
			return false;
		}

		if ($("#zzmmSelect").val() == "政治面貌") {
			mylayer("请选择政治面貌！", $("#zzmmSelect"), "1");
			return false;
		}

		if ($("#cyzk").val() == "从业状况") {
			mylayer("请选择从业状况！", $("#cyzk"), "1");
			return false;
		}
		
		if ($("#recordType").val() == 0) {
			mylayer("请选择是否登记备案！", $("#recordType"), "1");
			return false;
		}
		if ($("#teamClassifyType").val() == 0) {
			mylayer("请选择组织类型！", $("#teamClassifyType"), "1");
			return false;
		}
		if($("#recordType").val()==1){
			if($("#recordOrganization").val()==''){
				mylayer("登记/备案机构不能为空！", $("#recordOrganization"), "1");
				return false;
			}
			if($("#recordorganizationid").val()==''){
				mylayer("统一社会信用代码不能为空！", $("#recordorganizationid"), "1");
				return false;
			}
			if($("#signText").val()==''){
				mylayer("请上传登记/备案证书扫描件！", $("#signText"), "1");
				return false;
			}
		}

		//出生日期校验
		/* var yearStr = $("#YYYY").val();
		var mmStr = parseInt($("#MM").val()) < 10 ? "0" + $("#MM").val() : $(
				"#MM").val();
		var ddStr = parseInt($("#DD").val()) < 10 ? "0" + $("#DD").val() : $(
				"#DD").val();
		var birthday = yearStr + "-" + mmStr + "-" + ddStr;
		if (birthday.length == 10) {
			$("#birthday").val(birthday);
		} else {
			if (yearStr == "出生年") {
				mylayer("请填写生出年！", $("#YYYY"), "1");
			} else if (mmStr == "出生月") {
				mylayer("请填写生出月！", $("#MM"), "1");
			} else if (ddStr == "出生日") {
				mylayer("请填写生出日！", $("#DD"), "1");
			}
			return false;
		} */

		//*************************上以为是否为空判断,以下为格式校验、一致检验。****************************///	

		if (!VeloginName($("#loginName").val())) {
			mylayer("帐号有误！", $("#loginName"), "1");
			return false;
		}

		if ($("#userPassword").val() != ""
				&& $("#verifyUserPassword").val() != "") {
			if (VePassWord($("#userPassword").val())) {
				if ($("#userPassword").val() != $("#verifyUserPassword").val()) {
					mylayer("两次输入的密码不一致！", $("#verifyUserPassword"), "1");
					return false;
				}
			} else {
				mylayer("请填写6-12位字母和数字的组合！", $("#userPassword"), "1");
				return false;
			}
		}

		if (!VeMobile($("#mobile").val())) {
			mylayer("手机号码格式有误！", $("#mobile"), "1");
			return false;
		}

		if (!VeEmail($("#email").val())) {
			mylayer("邮箱格式有误！", $("#email"), "1");
			return false;
		}
		
		if (!VeEmail($("#teamemail").val())) {
			mylayer("组织邮箱格式有误！", $("#teamemail"), "1");
			return false;
		}

		if ($("#checkVerifyCode").val() != "true") {
			mylayer("验证码错误！", $("#inputVerifyCode"), "1");
			return false;
		}

		var r = true;
		//身份证校验
		if ($("#idcardType").val() == "1") {
			if (checkIdcardByCard($("#idcardCode").val()) != "验证通过!") {
				mylayer(checkIdcardByCard($("#idcardCode").val()),
						$("#idcardCode"));
				return false;
			}

		}

		return r;
		
	}


	function VeloginName(Str) {
		var reg = /^\w+$/;
// 		/^[1][3,4,5,7,8][0-9]{9}$|^\s*\w+(?:\.{0,1}[\w-]+)*@[a-zA-Z0-9]+(?:[-.][a-zA-Z0-9]+)*\.[a-zA-Z]+\s*$/;
		return reg.test(Str);
	}

	function VeMobile(Str) {
		var reg = /^[1][3,4,5,6,7,8,9][0-9]{9}$/;
		return reg.test(Str);
	}
	function VeEmail(Str) {
		var reg = /^\s*\w+(?:\.{0,1}[\w-]+)*@[a-zA-Z0-9]+(?:[-.][a-zA-Z0-9]+)*\.[a-zA-Z]+\s*$/;
		return reg.test(Str);
	}
	function VePassWord(Str) {
		var reg1 = /^[\dA-Za-z]{6,12}$/;
		var reg2 = /^\d+$/;
		var reg3 = /^[a-zA-Z]+$/;
		if(reg2.test(Str)||reg3.test(Str)){return false;}
		return reg1.test(Str);
	}

	Date.prototype.Format = function(formatStr) {
		var str = formatStr;
		var Week = [ '日', '一', '二', '三', '四', '五', '六' ];

		str = str.replace(/yyyy|YYYY/, this.getFullYear());
		str = str.replace(/yy|YY/,
				(this.getYear() % 100) > 9 ? (this.getYear() % 100).toString()
						: '0' + (this.getYear() % 100));

		str = str.replace(/MM/, this.getMonth() > 9 ? this.getMonth()
				.toString() : '0' + this.getMonth());
		str = str.replace(/M/g, this.getMonth());

		str = str.replace(/w|W/g, Week[this.getDay()]);

		str = str.replace(/dd|DD/, this.getDate() > 9 ? this.getDate()
				.toString() : '0' + this.getDate());
		str = str.replace(/d|D/g, this.getDate());

		str = str.replace(/hh|HH/, this.getHours() > 9 ? this.getHours()
				.toString() : '0' + this.getHours());
		str = str.replace(/h|H/g, this.getHours());
		str = str.replace(/mm/, this.getMinutes() > 9 ? this.getMinutes()
				.toString() : '0' + this.getMinutes());
		str = str.replace(/m/g, this.getMinutes());

		str = str.replace(/ss|SS/, this.getSeconds() > 9 ? this.getSeconds()
				.toString() : '0' + this.getSeconds());
		str = str.replace(/s|S/g, this.getSeconds());

		return str;
	}

	function emailUnique(){
		//检查email唯一性，ajax
		var thisObj = $("#email");
		if (thisObj.val() != "") {
			$.ajax({
				url : path+"/api/register/checkEmailUnique.do",
				data : {
					"email" : thisObj.val()
				},
				type : "post",
				dataType : "json",
				success : function(data) {
					if (data.msg == "true") {
						mylayer("邮箱已存在请重新输入！", thisObj);
						return false;
					}
				},
				error : function() {
					mylayer("无法判断，请重试！", thisObj);
					return false;
				}
			});
		}
		return true;
	}
	function checkteamNameUnique(){
		//检查teamname唯一性，ajax
		var thisObj = $("#teamname");
		//var teamreDistrictId = $("#pdistrictId");
		if (thisObj.val() != "") {
			$.ajax({
				url : path+"/api/register/checkteamnameUnique.do",
				data : {
					"teamname" : thisObj.val(),
					"teamreDistrictId" :/* teamreDistrictId.val()*/null
				},
				type : "post",
				dataType : "json",
				success : function(json) {
					if (json.msg == "fail") {
						$("#sendcode_AID").attr("disabled", "disabled");
						mylayer("组织名称已存在请重新输入！", thisObj);
						return false;
					}
					$("#sendcode_AID").attr("disabled", false);
				},
				error : function() {
					mylayer("无法判断，请重试！", thisObj);
					return false;
				}
			});
		}
		return true;
	}
	//解决手机软键盘隐藏动画和按钮倒计时不兼容问题---------开始
	function sendCode() {
		setTimeout(timeOutGo, 500);
	}
	function timeOutGo() {
		sendCodeAction();
	}
	//解决手机软键盘隐藏动画和按钮倒计时不兼容问题---------结束

	function sendCodeAction() {
		if (typeof (tips) != "undefined") {
			clearInterval(tips);
		}

		var code=document.getElementById("code").value;

		
// 		if (!easyReg.email_code.test(loginName)
// 				&& !easyReg.mobile_code.test(loginName)) {
// 			mylayer("请输入正确的手机号码或常用邮箱", $("#loginName"));
// 			$("#loginName").focus();
// 			return;
// 		}
		
		
		/* checkIdVerifyCode();
		if(imgVerifyCodeFlag != true){
			return false;
		} */
		
		// 超过五次拒绝再发验证码
		if(sendSmsTime >= 4){
			alert("24小时内只能发送五条验证码哦！");
			return false;
		}
		
		var checkImgVerify = true;
		
		if (easyReg.mobile_code.test($("#mobile").val())) {
			 var referer = window.location.href;
			$.ajax({
				type : "POST",
				url : "/user/apply/sendsms",
				data : {
					mobile : $("#mobile").val()
				},
				dataType : "json",
				async:false,
				success : function(data) {
					if (data.success) {
						$("#sendcode_AID").attr("disabled", "disabled");
						sendSmsTime = sendSmsTime + 1;
						sendFlag = true;
					} else {
						$("#sendcode_AID").removeAttr("disabled");
						mylayer(data.message, $("#code"));
						sendFlag = false;
						if(data.message == "随机码不正确，请重新输入!"){
							checkImgVerify = false;
						}
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					/* alert(XMLHttpRequest.status + "错误信息："
							+ XMLHttpRequest.responseText,"错误提示"); */
					if (typeof (tips) != "undefined") {
						clearInterval(tips);
					}
					$("#sendcode_AID").val("获取验证码");
					$("#sendcode_AID").removeAttr("disabled");

					alert("请求失败，请重试！");
				}
			});
			refresh();
			//$("#code").val('');
			imgVerifyCodeFlag = false;
		}
		
		if(checkImgVerify){
			if(sendFlag){
				$("#sendcode_AID").focus();
				$("#sendcode_AID").val("120秒后重新获取");
				$("#sendcode_AID").attr("disabled", "disabled");
				tips = setInterval("timeinter()", 1000);
			}else{
				$('#sendcode_AID').css({'background-color':'#cdcdcd','color':'#fff'});
				$("#sendcode_AID").attr("disabled","disabled");
			}
		}
		
		
		return;//暂不发送邮箱
		
		if (!easyReg.mobile_code.test($("#email").val())) {
			mylayer("请输入正确的邮箱", $("#email"));
			$("#email").focus();
			return;
		}
		
		if (easyReg.email_code.test($("#email").val())) {
			$.ajax({
				type : "POST",
				url : "${path}/findPwd/sendEmailCode.do",
				data : {
					email : $("#email").val()
				},
				dataType : "json",
				success : function(data) {
					if (data.success) {
						$("#sendcode_AID").attr("disabled", "disabled");
					} else {
						$("#sendcode_AID").removeAttr("disabled");
						mylayer(data.msg, $("#email"));
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					/* $.jBox.error(XMLHttpRequest.status + "错误信息："
							+ XMLHttpRequest.responseText,"错误提示"); */
					if (typeof (tips) != "undefined") {
						clearInterval(tips);
					}
					$("#sendcode_AID").val("获取验证码");
					$("#sendcode_AID").removeAttr("disabled");
					$("#gouImg").hide();
					alert("请求失败，请重试！");
				}
			});
		}
	}
	
	
	// 校验随机验证码
	function checkIdVerifyCode(){
		var inputCode = $("#code").val();
		$.ajax({
			type:"GET",
			url:path+"/common/verifycode/checkCodeInMobile",
			data:{code:inputCode},
			dataType:"json",
			async:false,
			success:function(data) {
				if(data.success){
					imgVerifyCodeFlag = true;
				}else{
					alert(data.msg);
					refresh();
					$("#code").val('');
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
				alert(XMLHttpRequest.status + "错误信息："
						+ XMLHttpRequest.responseText,"错误提示");
			}
		});
	}
	

	function timeinter() {
		val = parseInt($("#sendcode_AID").val());
		val = val - 1;
		$("#sendcode_AID").val((val).toString() + "秒后重新获取");
		$("#sendcode_AID").attr("disabled", "disabled");
		if (val == 0) {
			clearInterval(tips);
			$("#sendcode_AID").val("获取验证码");
			$("#sendcode_AID").removeAttr("disabled");
		}
	}

	function checkVerifyCodeIsTrue() {
		var tempinputVerifyCode = $("#inputVerifyCode").val();
		if (tempinputVerifyCode == "") {
			return;
		}
		$.ajax({
			type : "POST",
			url:path+"/common/verifycode/checkCodeInMobile",
			data : {
				inputVerifyCode : tempinputVerifyCode
			},
			dataType : "json",
			success : function(data) {
				if (data.success) {
					//$("#sendcode_AID").val("获取验证码");
					//$("#sendcode_AID").attr("disabled","disabled");
					$("#checkVerifyCode").val('true');
					$("#gouImg").show();
					//clearInterval(tips);
				} else {
					mylayer(data.message, $("#inputVerifyCode"));
					$("#checkVerifyCode").val('false');
					$("#gouImg").hide();
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
				/* alert(XMLHttpRequest.status + "错误信息："
						+ XMLHttpRequest.responseText); */
				if (typeof (tips) != "undefined") {
					clearInterval(tips);
				}
				$("#sendcode_AID").val("获取验证码");
				$("#sendcode_AID").removeAttr("disabled");
				$("#gouImg").hide();
				alert("请求失败，请重试！");
			}
		});
	}

	//是否含有中文（也包含日文和韩文）  
	function isChineseChar(str) {
		var reg = /[\u4E00-\u9FA5\uF900-\uFA2D]/;
		return reg.test(str);
	}

	function loginNameOption() {
		var loginName = document.getElementById("loginName").value;
		if (loginName == "" && $.trim(loginName) == "") {
			mylayer("请输入登录帐号！", $("#loginName"));
			$("#checkloginName").val('false');
			return false;
		} else {
			if (isChineseChar(loginName)) {
				mylayer("请使用字母或数字作为帐号！", $("#loginName"));
				$("#checkloginName").val('false');
				return false;
			} else {
				if (easyReg.email_code.test(loginName)) {
					if ($("#email").val()) {
						$("#email").val(loginName);
					}
				}
// 				if (!easyReg.email_code.test(loginName)
// 						&& !easyReg.mobile_code.test(loginName)) {
// 					mylayer("请输入正确的手机号码或常用邮箱！", $("#loginName"));
// 					$("#checkloginName").val('false');
// 					return false;
// 				}
			}
		}

		//检查loginName唯一性，ajax
		var thisObj = $("#loginName");
		if (thisObj.val() != "") {
			$.ajax({
				url : path+"/api/register/checkLoginNameUnique.do",
				data : {
					"loginName" : thisObj.val()
				},
				type : "post",
				dataType : "json",
				success : function(data) {
					if (data.msg == "true") {
						$("#sendcode_AID").attr("disabled", "disabled");
						mylayer("帐号已存在请重新输入！", thisObj);
						return false;
					}
					$("#sendcode_AID").attr("disabled", false);
				},
				error : function() {
					mylayer("无法判断，请重试！", thisObj);
					return false;
				}
			});
		}
		
		return true;
		
	}

	//loginName 值改变时
	function loginNameChange() {
		$.ajax({
			type : "POST",
			url : path+"/common/verifycode/cancelVerifyCode.do",
			dataType : "json",
			success : function(data) {
				if (typeof (tips) != "undefined") {
					clearInterval(tips);
				}
				$("#sendcode_AID").val("获取验证码");
				$("#sendcode_AID").removeAttr("disabled");
				$("#gouImg").hide();
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
				if (typeof (tips) != "undefined") {
					clearInterval(tips);
				}
				$("#sendcode_AID").val("获取验证码");
				$("#sendcode_AID").removeAttr("disabled");
				$("#gouImg").hide();
				alert("请求失败，请重试！");
			}
		});
		$("#checkVerifyCode").val('false');
	}

	//tips，通用
	function mylayer(msg, $obj, type) {
		if (type == "1") {
			$obj.css("border", "1px solid red");
			layer.tips(msg, $obj, {
				tips : [ 3, 'red' ],
				time : 2500
			});
			alert(msg);
		} else {
			$obj.css("border", "1px solid red");
			layer.tips(msg, $obj, {
				tips : [ 3, 'red' ],
				time : 2500
			});
		}
	}
	
	/* 刷新随机码 */
	function refleshCode(imgId){
	    var src = path+'/api/common/captcha/create.do?rc='+Math.random();
	    $('#'+imgId).attr('src',src);
	}
	
    //验证码信息
    function refreshVerifyCode(imgId){
	    var src = path+'/api/common/randomcode/staticimge4regist?t='+new Date().getTime();
	    $('#'+imgId).attr('src',src);
    }
	
	function popupDiv(div_id) {
		var div_obj = $("#" + div_id);
		var windowWidth = document.documentElement.offsetWidth;
		var windowHeight = $(window).height()

		var popupHeight = div_obj.height();
		var popupWidth = div_obj.width();
		//添加并显示遮罩层   
		$("<div id='backgroundPopup'></div>").css({
			"height": '100%',
			"opacity": "0.7"
		}).appendTo("body").fadeIn(0);
		div_obj.show(0).css({
			left: windowWidth * 0.5 - popupWidth * 0.5,
			top: "50px"
		});

	}

	function closeBox(){
		$("#popup").css('display','none'); 
		$("#backgroundPopup").remove(); 
		
	}
	
	function hideDiv(div_id) {
		$("#backgroundPopup").remove();
		$("#" + div_id).hide(0).css({
			left: 0,
			top: 0
		});
	}

	$(function() {
		$('#subnt').click(function() {
			var province = $("#province").is(":hidden") ? "": $("#province option:selected").text();
			var city = $("#city").is(":hidden") ? "": $("#city option:selected").text();
			var sarea = $("#sarea").is(":hidden") ? "": $("#sarea option:selected").text();
			var addr = $("#addr").val();
			if(province == "请选择"){
				mylayer("请填写完整！", $("#province"));
			}else if(city == "请选择"){
				mylayer("请填写完整！", $("#city"));
			}else if(sarea == "请选择"){
				mylayer("请填写完整！", $("#sarea"));
			}else if(addr == ""){
				mylayer("请填写完整！", $("#addr"));
			}else{
			if ($("#sarea").is(":hidden")) {
				$("#hasArea").val("0");
			} else {
				$("#hasArea").val("1");
			}
			var address = '';
			if(province != '请选择'){
				address += province;
				$('#regionId').val($("#province").val());
				$('#hidP2').val(province);
			}
			if(city != '请选择'){
				address += city;
				$('#regionId').val($("#city").val());
				$('#hidC2').val(city);
			}
			if(sarea != '请选择'){
				address += sarea;
				$('#regionId').val($("#sarea").val());
				$('#hidA2').val(sarea);
			}
			address += addr;
			//暂时不用
			$("#address").val(address);
			hideDiv('popup');
			if ((province + city + sarea + addr).indexOf("请选择") < 0) {
				;
			} /* else {
				if (province == "请选择") {
					mylayer("请填写完整！", $("#province"));
				} else if (city == "请选择") {
					mylayer("请填写完整！", $("#city"));
				} else if (sarea == "请选择") {
					mylayer("请填写完整！", $("#sarea"));
				}
			} */
			return false;
			}
		});

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
					mylayer("请填写归属组织！", $("#workingAddress"));
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

		$('#fwxywindow #cancelbtn').click(function() {
			$('#fwxywindow,#fullxi').hide();
		});

		$('#gsdw #cancelbtn').click(function() {
			$('#gsdw,#fullxi').hide();
			$("#workingAddress").val($("input[name='radioItem']:checked").attr("val"));
			$("#pdistrictId").val($("input[name='radioItem']:checked").parents("li").attr("val"));
			$("#inputText").val('');
		});
		
		$('#gsdw_close').click(function() {
			$('#gsdw,#fullxi').hide();
//			$("#workingAddress").val('');
//			$("#pdistrictId").val('');
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

		$("#workingAddressDiv").click(function() {
			$('#gsdw,#fullxi').show();
			gsdwULItem("40288188119c102f01119cadc42d01d0");//40288188119c102f01119cadc42d01d0--全国；b0dc9771d14211e18718000aebf5352e--广东
		});
		
		/**
		 * 登记备案
		 */
		$("#recordDiv").click(function() {
			$('#gsdw2,#fullxi').show();
			recordULItem();
		});
		$('#gsdw_close2').click(function() {
			$('#gsdw2,#fullxi').hide();
			$("#recordOrganization").val('');
			$("#recordkeepingId").val('');
			$("#inputText2").val('');
		});
		$('#gsdw2 #cancelbtn2').click(function() {
			$('#gsdw2,#fullxi').hide();
			$("#recordOrganization").val($("input[name='radioItem']:checked").attr("val"));
			$("#recordkeepingId").val($("input[name='radioItem']:checked").parents("li").attr("val"));
			$("#inputText2").val('');
		});
		$("#searchDis2").click(function(){
			recordULItem();
		});
		function recordULItem(){
			var recordName= $("#inputText2").val();
			$.ajax({
				url : path+"/api/register/getRecordkeepingForMzb.do",
				data : {
					"orgName" : recordName
				},
				type : "post",
				async : true,
				dataType : "json",
				success : function(data) {
					if(data.code=="2"){
						alert(data.msg);
						return false;
					}
					$("#gsdwUL2").empty();
					$.each(data.data,function(i){
						var recordkeepingId = data.data[i].orgId;
						var recordOrganization = data.data[i].orgName;
						$("#gsdwUL2").append("<li val='" + recordkeepingId + "'><font style='float: left; display: block; width: 80%;overflow: hidden; text-overflow: ellipsis;white-space: nowrap;'>" + recordOrganization + "</font><input style='margin-left:10px;border: 1px solid #999999;' name='radioItem' val='" + recordOrganization + "' type='radio'/></li>");
					});
				},
				error : function() {
					r = false;
				}
			});
		}
	});
	
	//刷新随机码图片
	function refresh(){
	  	 var idVerifyCode= document.getElementById("idVerifyCode");
	     idVerifyCode.style.display="inline";
	     idVerifyCode.src=path+'/api/common/captcha/create?t='+new Date().getTime();
	}
	/**
	 * 选择登记备案情况
	 */
	function changerecordType(){
		var temprecordType=$("#recordType").val();
		if(temprecordType==1){
			$("#teamClassifyType").html('<option value="0">组织/团体类别</option><option value="1">社会团体</option><option value="2">社会服务机构（民办非企）</option><option value="3">基金会</option>');
			$("#beianjigou").css("display","");
			$("#tongyishehui").css("display","");
			$("#saomiaojian").css("display","");
//			$("#recordOrganization").val("");
			$("#recordDiv").css('width','100%');
//			$("#recordOrganization").readOnly=true;
		}else if(temprecordType==3){
			$("#teamClassifyType").html('<option value="0">组织/团体类别</option><option value="4">国有企业</option>'+
					'<option value="5">党政机关</option><option value="6">教育事业单位</option><option value="7">卫生事业单位</option>'+
					'<option value="8">科技事业单位</option><option value="9">文化事业单位</option>'+
							'<option value="10">社会福利事业单位</option>'+
							'<option value="11">其他事业单位</option>'+
							'<option value="12">群团组织</option>'+
							'<option value="13">居委会或村委会</option>'+
							'<option value="14">非国有企业</option>'+
							'<option value="15">其他</option>');
			$("#recordDiv").css('width','0%');
			$("#recordOrganization").removeAttr("readOnly")
		}else{
			$("#beianjigou").css("display","");
			$("#tongyishehui").css("display","");
			$("#saomiaojian").css("display","");
			$("#recordDiv").css('width','100%');
		}
		$("#teamClassifyType").val("0");
		$("#recordOrganization").val("");
		$("#recordorganizationid").val("");
		$("#recordpicPath").val("");
		$("#recordkeepingId").val("");
	}
	

	function changeFyTeam(){
		var fyTeam=$("#teamClassifyType").val();
		var Obj=$("#teamClassifyType").parents('.inner_item_item');
		if(fyTeam==13||fyTeam==14||fyTeam==15){
			$("#beianjigou").css("display","none");
			$("#tongyishehui").css("display","none");
			$("#saomiaojian").css("display","none");
			$("#recordOrganization").val("");
			$("#recordorganizationid").val("");
			$("#recordpicPath").val("");
			$("#recordkeepingId").val("");
			
		}else{
			$("#beianjigou").css("display","");
			$("#tongyishehui").css("display","");
			$("#saomiaojian").css("display","");
		}
	}
	
	function initWangDian(){
		$('#recordOrganization').click(function(event) {
	        $('.tc').fadeIn(100);
	    });
	}