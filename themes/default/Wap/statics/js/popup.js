function init(){
	var province = $("#province").find("option:selected").text();
	var city = $("#city").val();
	var bankName = $("#bankName").val();
	
	$('#datalistTB').datagrid({ 
        width: 'auto', 
        height: 435, 
        nowrap: false, 
        striped: true, 
        border: true, 
        collapsible:false,//是否可折叠的 
        url:path+'/bankBranch/getBankBranchMsg?province='+province+"&city="+city+"&bankName="+bankName+"&bankType="+bankType, 
        sortName: 'branchName', 
        sortOrder: 'asc', 
        remoteSort:false,  
        singleSelect:true,//是否单选 
        pagination:true,//分页控件 
        pageSize:10,//分页大小
        pageList:[5,10,15],//每页的个数  
        beforePageText: '第',//页数文本框前显示的汉字 
        afterPageText: '页    共 {pages} 页', 
        displayMsg: '当前显示 {from} - {to} 条记录   共 {total} 条记录', 
        rownumbers:true//行号 
    });
	
/*	$('#datalistTB').datagrid('reload',{
		province: province,
		city: city,
		bankName:bankName
	});*/
}


function getCity(regionPid){
	$("#city").empty();
	$.getJSON(path+"/region/region/getRegionList?regionId="+regionPid,function(data){
 		var city = "";
 		city += "<option value='请选择'>请选择</option>";
		$.each(data,function(){
			city += "<option value='"+this.name+"'>"+this.name+"</option>";
		});
		$("#city").append(city);
 	});
}

function searchBank(){
	var province = $("#province").find("option:selected").text();
	var city = $("#city").val();
	var bankName = $("#bankName").val();
    $('#datalistTB').datagrid({  
        url:path+'/bankBranch/getBankBranchMsg?province='+province+"&city="+city+"&bankName="+bankName+"&bankType="+bankType,  
    });  
}

function selectBank(){
    var selRows = $('#datalistTB').datagrid('getChecked'); 
    if(selRows.length > 0){
    	var bankingOutlets = selRows[0].branchCode;
    	var branchName = selRows[0].branchName; 
    	window.opener.document.getElementById('bankingOutlets').value=bankingOutlets;
    	window.opener.document.getElementById('receiveAddress').value=branchName;
    	window.close();
    }else{
    	alert('请选择一个网点');
    }
}