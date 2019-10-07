
//Regional开始
$(document).ready(function(){
    $(".Regional").click(function(){
    	if($(this).attr("ca") == ""){
            if ($('.grade-eject').hasClass('grade-w-roll')) {
                $('.grade-eject').removeClass('grade-w-roll');
            } else {
                $('.grade-eject').addClass('grade-w-roll');
            }   		
    	}else{
            if ($('.grade-eject').hasClass('grade-w-roll-qzty')) {
                $('.grade-eject').removeClass('grade-w-roll-qzty');
            } else {
                $('.grade-eject').addClass('grade-w-roll-qzty');
            }
    	}
    });
});

$(document).ready(function(){
    $(".grade-w>li").click(function(){
    	if($(this).html()!="不限"){
       $(".grade-t").css("left","33.48%")
    	}
     
    });
});

/*$(document).ready(function(){
    $(".grade-t>li").click(function(){
        $(".grade-s").css("left","66.96%")
        
    });
});*/

//Brand开始

$(document).ready(function(){
    $(".Brand").click(function(){
    	if($(this).attr("ca") == ""){
    		if ($('.Category-eject').hasClass('grade-w-roll')) {
                $('.Category-eject').removeClass('grade-w-roll');
            } else {
                $('.Category-eject').addClass('grade-w-roll');
            }
    	}else{
    		if ($('.Category-eject').hasClass('grade-w-roll-qzty')) {
                $('.Category-eject').removeClass('grade-w-roll-qzty');
            } else {
                $('.Category-eject').addClass('grade-w-roll-qzty');
            } 		
    	}
    	
        
    });
});

$(document).ready(function(){
    $(".Category-w>li").click(function(){
        $(".Category-t")
            .css("left","33.48%")
    });
});

$(document).ready(function(){
    $(".Category-t>li").click(function(){
        $(".Category-s")
            .css("left","66.96%")
    });
});

//Sort开始

$(document).ready(function(){
    $(".Sort").click(function(){
    	if($(this).attr("ca") == ""){
            if ($('.Sort-eject').hasClass('grade-w-roll')) {
                $('.Sort-eject').removeClass('grade-w-roll');
            } else {
                $('.Sort-eject').addClass('grade-w-roll');
            }    		
    	}else{
            if ($('.Sort-eject').hasClass('grade-w-roll-qzty')) {
                $('.Sort-eject').removeClass('grade-w-roll-qzty');
            } else {
                $('.Sort-eject').addClass('grade-w-roll-qzty');
            }  		
    	}

    });
});


//判断页面是否有弹出

$(document).ready(function(){
    $(".Regional").click(function(){
    	if($(this).attr("ca") == ""){
            if ($('.Category-eject').hasClass('grade-w-roll')){
                $('.Category-eject').removeClass('grade-w-roll');
            };    		
    	}else{
            if ($('.Category-eject').hasClass('grade-w-roll-qzty')){
                $('.Category-eject').removeClass('grade-w-roll-qzty');
            };    		
    	}
    });
});
$(document).ready(function(){
    $(".Regional").click(function(){
    	if($(this).attr("ca") == ""){
            if ($('.Sort-eject').hasClass('grade-w-roll')){
                $('.Sort-eject').removeClass('grade-w-roll');
            };
    	}else{
            if ($('.Sort-eject').hasClass('grade-w-roll-qzty')){
                $('.Sort-eject').removeClass('grade-w-roll-qzty');
            };
    	}
    });
});
$(document).ready(function(){
    $(".Brand").click(function(){
    	if($(this).attr("ca") == ""){
            if ($('.Sort-eject').hasClass('grade-w-roll')){
                $('.Sort-eject').removeClass('grade-w-roll');
            };
    	}else{
            if ($('.Sort-eject').hasClass('grade-w-roll-qzty')){
                $('.Sort-eject').removeClass('grade-w-roll-qzty');
            };
    	}
    });
});
$(document).ready(function(){
    $(".Brand").click(function(){
    	if($(this).attr("ca") == ""){
            if ($('.grade-eject').hasClass('grade-w-roll')){
                $('.grade-eject').removeClass('grade-w-roll');
            };
    	}else{
            if ($('.grade-eject').hasClass('grade-w-roll-qzty')){
                $('.grade-eject').removeClass('grade-w-roll-qzty');
            };
    	}
    });
});
$(document).ready(function(){
    $(".Sort").click(function(){
    	if($(this).attr("ca") == ""){
            if ($('.Category-eject').hasClass('grade-w-roll')){
                $('.Category-eject').removeClass('grade-w-roll');
            };
    	}else{
            if ($('.Category-eject').hasClass('grade-w-roll-qzty')){
                $('.Category-eject').removeClass('grade-w-roll-qzty');
            };
    	}
    });
});
$(document).ready(function(){
    $(".Sort").click(function(){
    	if($(this).attr("ca") == ""){
            if ($('.grade-eject').hasClass('grade-w-roll')){
                $('.grade-eject').removeClass('grade-w-roll');
            };
    	}else{
            if ($('.grade-eject').hasClass('grade-w-roll-qzty')){
                $('.grade-eject').removeClass('grade-w-roll-qzty');
            };
    	}

    });
});


//js点击事件监听开始
function grade1(wbj){
    var arr = document.getElementById("gradew").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.background = "";
    };
    wbj.style.background = "#eee";
  if($(wbj).html()!="不限"){
	    $(".grade-t").css("left","33.48%");	
  }

}

function gradet(tbj){
    var arr = document.getElementById("gradet").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.background = "";
    };
    tbj.style.background = "#fff"
}
function gradee(tbe){
	var arr = document.getElementById("gradee").getElementsByTagName("li");
	for (var i = 0; i < arr.length; i++){
		var a = arr[i];
		a.style.background = "";
	};
	tbe.style.background = "#fff"
}

function grades(sbj){
    var arr = document.getElementById("gradee").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.borderBottom = "";
    };
    sbj.style.borderBottom = "solid 1px #ff7c08"
}

function Categorytw(wbj){
    var arr = document.getElementById("Categorytw").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.background = "";
    };
    wbj.style.background = "#eee"
}

function Categoryt(tbj){
    var arr = document.getElementById("Categoryt").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.background = "";
    };
    tbj.style.background = "#fff"
}

function Categorys(sbj){
    var arr = document.getElementById("Categorys").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.borderBottom = "";
    };
    sbj.style.borderBottom = "solid 1px #ff7c08"
}

function Sorts(sbj){
    var arr = document.getElementById("Sort-Sort").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.borderBottom = "";
    };
    sbj.style.borderBottom = "solid 1px #ff7c08"
}



function searchBox02(sbj){
    var arr = document.getElementById("search-Bbox").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.borderBottom = "";
    };
    sbj.style.borderBottom = "solid 1px #ff7c08"
}













$(document).ready(function(){
    $(".searchBox03").click(function(){
    	if($(this).attr("ca") == ""){
    		if ($('.searchBox').hasClass('grade-w-roll')) {
                $('.searchBox').removeClass('grade-w-roll');
            } else {
                $('.searchBox').addClass('grade-w-roll');
            }
    	}else{
    		if ($('.searchBox').hasClass('grade-w-roll-qzty')) {
                $('.searchBox').removeClass('grade-w-roll-qzty');
            } else {
                $('.searchBox').addClass('grade-w-roll-qzty');
            }
    	}
    });
});



$(document).ready(function(){
    $(".Regional").click(function(){
    	if($(this).attr("ca") == ""){
	        if ($('.searchBox').hasClass('grade-w-roll')){
	            $('.searchBox').removeClass('grade-w-roll');
	        };
    	}else{
	        if ($('.searchBox').hasClass('grade-w-roll-qzty')){
	            $('.searchBox').removeClass('grade-w-roll-qzty');
	        };   		
    	}
    });
});
$(document).ready(function(){
    $(".Brand").click(function(){
    	if($(this).attr("ca") == ""){
            if ($('.searchBox').hasClass('grade-w-roll')){
                $('.searchBox').removeClass('grade-w-roll');
            };	
    	}else{
	        if ($('.searchBox').hasClass('grade-w-roll-qzty')){
	            $('.searchBox').removeClass('grade-w-roll-qzty');
	        };
    	}
    });
});