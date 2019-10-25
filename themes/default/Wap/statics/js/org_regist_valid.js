var sendSmsTime = 0;
var imgVerifyCodeFlag = false;
var sendFlag = true;
$(function () {
    refreshVerifyCode('idVerifyCode');
    //initWangDian();
    //alert("告知：由于系统遭受第三方恶意注册刷短信验证码，为保障系统安全，系统短信功能暂时关闭，用户注册的短信验证码暂停发送。我方技术正全力解决，抱歉带来不便，敬请谅解！");
    layer.config({
        extend: '../layer/extend/layer.ext.js'
    });
    $("#sendcode_AID").removeAttr("disabled");
    $("#address").val("");


    $("#idcardType").change(function () {
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
            function () {
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
                }

                $(this).css("border", "1px solid #ccc");
            });

    /*对象失去焦点事件*/
    var checkFlag = true;
    $("input[blurCheck='true']").blur(function () {

        if ($(this).attr("id") == "userName") {
            if (!isChineseChar($("#userName").val())) {
                mylayer("真实姓名应为中文！", $("#userName"));
                checkFlag = false;
                return false;
            }
        }

        if ($(this).attr("id") == "idcardCode") {
            if (checkIdcardByCard($("#idcardCode").val()) != "验证通过!") {
                mylayer("身份证格式不正确！", $("#idcardCode"));
                checkFlag = false;
                return false;
            }
        }
        if ($(this).attr("id") == "sendcode_AID") {
            if ($(this).val() == "") {
                mylayer("验证码不能为空！", $(this));
                checkFlag = false;
                return false
            }
        }
        $(this).css("border", "1px solid #ccc");


    });

    $("#info_tishi").click(function () {
        layer.msg('建议使用身份证号！');
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



function VeMobile(Str) {
    var reg = /^[1][3,4,5,6,7,8,9][0-9]{9}$/;
    return reg.test(Str);
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

    // 超过五次拒绝再发验证码
    if (sendSmsTime >= 4) {
        alert("24小时内只能发送五条验证码哦！");
        return false;
    }

    if ((/^1[3456789]\d{9}$/.test($("#mobile").val()))) {
        $.ajax({
            type: 'post',
            url: '/user/apply/sendsms',
            data: {
                'mobile': $("#mobile").val()
            },
            async: false,
            success: function (data) {
                if (data) {
                    $("#sendcode_AID").attr("disabled", "disabled");
                    sendSmsTime = sendSmsTime + 1;
                    sendFlag = true;
                } else {
                    $("#sendcode_AID").removeAttr("disabled");
                    mylayer("验证码发送失败", $("#sendcode_AID"));
                    alert("验证码发送失败");
                    sendFlag = false;
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                // alert(XMLHttpRequest.status + "错误信息："
                // 		+ XMLHttpRequest.responseText,"错误提示");
                if (typeof (tips) != "undefined") {
                    clearInterval(tips);
                }
                $("#sendcode_AID").val("获取验证码");
                $("#sendcode_AID").removeAttr("disabled");

                alert("请求失败，请重试！");
            }
        });
        imgVerifyCodeFlag = false;
    }

    if (sendFlag) {
        $("#sendcode_AID").focus();
        $("#sendcode_AID").val("120");
        $("#sendcode_AID").attr("disabled", "disabled");
        tips = setInterval("timeinter()", 1000);
    } else {
        $('#sendcode_AID').css({'background-color': '#cdcdcd', 'color': '#fff'});
        $("#sendcode_AID").attr("disabled", "disabled");
    }


    return;//暂不发送邮箱
}
function userInfoSubmit(){
	if ($("#userName").val() == "") {
		mylayer("姓名不能为空！", $("#userName"), "1");
		return false;
	}
	if ($("#idcardCode").val() == "") {
		mylayer("证件号码不能为空！", $("#idcardCode"), "1");
		return false;
	}
	//身份证校验
    if (checkIdcardByCard($("#idcardCode").val()) != "验证通过!") {
        mylayer(checkIdcardByCard($("#idcardCode").val()),
            $("#idcardCode"));
        return false;
    }
    if ($("#head_model").attr("src") == "") {
        mylayer("头像不能为空！", $("#fileUpload"), "1");
        return false;
    }
    if ($("#organization_id").val() == "") {
        mylayer("归属组织不能为空！", $("#organization_id"), "1");
        return false;
    }
	if ($("#mobile").val() == "") {
		mylayer("手机号码不能为空！", $("#mobile"), "1");
		return false;
	}

	if ($("#inputVerifyCode").val() == "") {
		mylayer("验证码不能为空！", $("#inputVerifyCode"), "1");
		return false;
	}
	$.ajax({
		type:"POST",
		url:"/user/apply/volunteerCard",
		data:{
			"userName":$("#userName").val(),
			"idcardCode":$("#idcardCode").val(),
			"mobile":$("#mobile").val(),
			"code":$("#inputVerifyCode").val(),
            "head_url":$("#head_model").attr("src"),
            "organization_id":$("#organization_id").val()
		},
        dataType:"json",
		success:function (data) {
            alert(data.msg);
			if(data.status == 'success'){
                location.href = "/user/apply/volunteerCard";
            }
		},
		err:function (err) {
			alert("认证失败，请稍后重试！");
		}
	});
}



function timeinter() {
    val = parseInt($("#sendcode_AID").val());
    val = val - 1;
    $("#sendcode_AID").val((val).toString());
    $("#sendcode_AID").attr("disabled", "disabled");
    if (val == 0) {
        clearInterval(tips);
        $("#sendcode_AID").val("获取验证码");
        $("#sendcode_AID").removeAttr("disabled");
    }
}

//是否含有中文（也包含日文和韩文）
function isChineseChar(str) {
    var reg = /[\u4E00-\u9FA5\uF900-\uFA2D]/;
    return reg.test(str);
}


//tips，通用
function mylayer(msg, $obj, type) {
    if (type == "1") {
        $obj.css("border", "1px solid red");
        layer.tips(msg, $obj, {
            tips: [3, 'red'],
            time: 2500
        });
        alert(msg);
    } else {
        $obj.css("border", "1px solid red");
        layer.tips(msg, $obj, {
            tips: [3, 'red'],
            time: 2500
        });
    }
}

/* 刷新随机码 */
function refleshCode(imgId) {
    var src = '/api/common/captcha/create.do?rc=' + Math.random();
    $('#' + imgId).attr('src', src);
}

//验证码信息
function refreshVerifyCode(imgId) {
    var src = '/api/common/randomcode/staticimge4regist?t=' + new Date().getTime();
    $('#' + imgId).attr('src', src);
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

function closeBox() {
    $("#popup").css('display', 'none');
    $("#backgroundPopup").remove();

}

function hideDiv(div_id) {
    $("#backgroundPopup").remove();
    $("#" + div_id).hide(0).css({
        left: 0,
        top: 0
    });
}


//刷新随机码图片
function refresh() {
    var idVerifyCode = document.getElementById("idVerifyCode");
    idVerifyCode.style.display = "inline";
    idVerifyCode.src = '/api/common/captcha/create?t=' + new Date().getTime();
}

/**
 * 选择登记备案情况
 */
function changerecordType() {
    var temprecordType = $("#recordType").val();
    if (temprecordType == 1) {
        $("#teamClassifyType").html('<option value="0">组织/团体类别</option><option value="1">社会团体</option><option value="2">社会服务机构（民办非企）</option><option value="3">基金会</option>');
        $("#beianjigou").css("display", "");
        $("#tongyishehui").css("display", "");
        $("#saomiaojian").css("display", "");
//			$("#recordOrganization").val("");
        $("#recordDiv").css('width', '100%');
//			$("#recordOrganization").readOnly=true;
    } else if (temprecordType == 3) {
        $("#teamClassifyType").html('<option value="0">组织/团体类别</option><option value="4">国有企业</option>' +
            '<option value="5">党政机关</option><option value="6">教育事业单位</option><option value="7">卫生事业单位</option>' +
            '<option value="8">科技事业单位</option><option value="9">文化事业单位</option>' +
            '<option value="10">社会福利事业单位</option>' +
            '<option value="11">其他事业单位</option>' +
            '<option value="12">群团组织</option>' +
            '<option value="13">居委会或村委会</option>' +
            '<option value="14">非国有企业</option>' +
            '<option value="15">其他</option>');
        $("#recordDiv").css('width', '0%');
        $("#recordOrganization").removeAttr("readOnly")
    } else {
        $("#beianjigou").css("display", "");
        $("#tongyishehui").css("display", "");
        $("#saomiaojian").css("display", "");
        $("#recordDiv").css('width', '100%');
    }
    $("#teamClassifyType").val("0");
    $("#recordOrganization").val("");
    $("#recordorganizationid").val("");
    $("#recordpicPath").val("");
    $("#recordkeepingId").val("");
}


function changeFyTeam() {
    var fyTeam = $("#teamClassifyType").val();
    var Obj = $("#teamClassifyType").parents('.inner_item_item');
    if (fyTeam == 13 || fyTeam == 14 || fyTeam == 15) {
        $("#beianjigou").css("display", "none");
        $("#tongyishehui").css("display", "none");
        $("#saomiaojian").css("display", "none");
        $("#recordOrganization").val("");
        $("#recordorganizationid").val("");
        $("#recordpicPath").val("");
        $("#recordkeepingId").val("");

    } else {
        $("#beianjigou").css("display", "");
        $("#tongyishehui").css("display", "");
        $("#saomiaojian").css("display", "");
    }
}

function initWangDian() {
    $('#recordOrganization').click(function (event) {
        $('.tc').fadeIn(100);
    });
}