//手机邮箱校验表达式
var easyReg=new Object();
easyReg.mobile_code=/^[1][3,4,5,6,7,8,9][0-9]{9}$/;
easyReg.email_code=/^\w+(\.\w+)*@\w+(\.\w+)+$/;

/**
 * 邮箱跳转
 */
function goEmail(s){
		if(s==''){
			window.open('about:Tabs','blank');
			return ;
		}
		if(s.indexOf('@sina.com')!=-1){
			//window.location.href='http://mail.sina.com.cn';
			window.open('http://mail.sina.com.cn','blank');
			return ;
		}
		if(s.indexOf('@qq.com')!=-1){
			//window.location.href='http://mail.qq.com';
			window.open('http://mail.qq.com','blank');
			return ;
		}
		if(s.indexOf('@126.com')!=-1){
			//window.location.href='http://126.com/';
			window.open('http://126.com/','_blank');
			return ;
		}
		if(s.indexOf('@163.com')!=-1){
			//window.location.href='http://email.163.com/';
			window.open('http://email.163.com/','_blank');
			return ;
		}
		if(s.indexOf('@souhu.com')!=-1){
			//window.location.href='http://mail.souhu.com';
			window.open('http://email.163.com/','_blank');
			return ;
		}
		if(s.indexOf('@hotmail.com')!=-1){
			//window.location.href='http://mail.sina.com.cn';
			window.open('http://mail.sina.com.cn','_blank');
		}
		if(s.indexOf('@gmail.com')!=-1){
			//window.location.href='http://mail.sina.com.cn';
			window.open('http://mail.sina.com.cn','_blank');
			return ;
		}
		if(s.indexOf('@139.com')!=-1){
			//window.location.href='http://mail.sina.com.cn';
			window.open('http://mail.10086.cn','_blank');
			return ;
		}
		alert('未检测到指定邮箱地址，请手动登入邮箱');
}