(function ($) {
     $.fn.zzmmListSelect = function (option) {
     	var national = [
            "中共党员", "中共预备党员", "共青团员", "民革党员", "民盟盟员", "民建会员", "民进会员", "农工党党员", "致公党党员", "九三学社社员", "台盟盟员", "无党派人士", "群众"
		];
		//selected
		for(var i = 0; i < national.length; i++){
			var option = "";
			if($(this).attr("val") == national[i]){
				option = "<option selected='selected' value="+national[i]+">"+national[i]+"</option>";
			}else{
				option = "<option value="+national[i]+">"+national[i]+"</option>";
			}
			$(this).append(option);
		}
    }
})(jQuery);