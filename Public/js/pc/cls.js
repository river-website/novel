/**
 * Created by bill on 16/9/14.
 */
//页面跳转
function jump_page(){
    var c_page = $("#c_page").val();
    var max_page = $("#max_page").val();
    var tmp_page = parseInt(c_page);
    if(tmp_page < 1 || tmp_page > parseInt(max_page)){
        $("#c_page").val('');
    }else{
        if(c_page != ''){
            var c_url = $("#c_prefix").val() + c_page;
            window.location.href = c_url;
        }
    }
}

//设置焦点框id
function set_foucs_input(id){
    $("#focus_input").val(id);
}

//键盘按下事件
$(function(){
   $(document).keydown(function(e){
       if(e.keyCode == 13){
           var id = $("#focus_input").val();
           if(id != undefined && id != ''){
               $("#"+id).click();
           }
       }
   });
});
