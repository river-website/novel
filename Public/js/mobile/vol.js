/**
 * Created by bill on 16/9/6.
 */
var chp_page_size = 50;
var total = parseInt($("#chp_all_num").val());
var page_total_float = total / chp_page_size;
var page_total = parseInt(page_total_float);
$(function(){
    var str = "";
    if(page_total_float > page_total){
        page_total += 1;
    }
    for(var i = 0; i < page_total; i++){
        str += "<option value='"+(i+1)+"'>第"+(1+chp_page_size*i)+'章 - 第'+(chp_page_size*(i+1))+'章'+"</option>";
    }
    $("#volumeList1").html(str);
    $("#volumeList2").html(str);
});

//根据id获取不同的tab页
function switch_tab(obj,id){
    $(".tab").removeClass('cur');
    $(obj).addClass('cur');
    for(var i = 1; i < 4; i++){
        var tab_div = $("#tab_"+i);
        if(i != 3){
            tab_div.removeClass('limit_lines-3');
        }
        if(i == 2){
            tab_div.next().removeClass('open_btn');
            tab_div.next().removeClass('close_btn');
        }
        tab_div.addClass("hidden");
    }
    if(id == '1'){
        $("#tab_"+id).addClass('limit_lines-3');
    }
    if(id == '2'){
        var tab_div = $("#tab_"+id);
        $("#tab_"+id).addClass('limit_lines-3');
        tab_div.next().addClass('open_btn');
    }
    $("#tab_"+id).removeClass('hidden');
}

function show_chp_list(id){
    var old_page_num = parseInt($("#page_num").val());
    var page_num = parseInt($("#volumeList"+id).find("option:selected").val());
    var old_page_total = old_page_num * chp_page_size;
    var new_page_total = page_num * chp_page_size;
    $("#page_num").val(page_num);
    $("#selText"+id).text('第'+(1+chp_page_size*(page_num-1))+'章 - 第'+(chp_page_size*page_num)+'章');
    for(var i = (old_page_num - 1)*chp_page_size + 1; i <= old_page_total; i++){
        $(".chapter-"+i).addClass("hidden");
    }
    for(var i = (page_num - 1)*chp_page_size + 1; i <= new_page_total; i++){
        $(".chapter-"+i).removeClass("hidden");
    }
    if(id == '1'){
        id = '2';
    }else{
        id = '1';
    }
    $("#volumeList"+id+" option[value='"+page_num+"'").attr("selected",true);
    $("#selText"+id).text('第'+(1+chp_page_size*(page_num-1))+'章 - 第'+(chp_page_size*page_num)+'章');
}

function pre_page_list(id){
    var page_num = parseInt($("#page_num").val());
    if(page_num > 1){
        page_num -= 1;
        $("#volumeList"+id+" option[value='"+page_num+"']").attr("selected",true);
        show_chp_list(id);
    }
}

function next_page_list(id){
    var page_num = parseInt($("#page_num").val());
    if(page_num < page_total){
        page_num += 1;
        $("#volumeList"+id+" option[value='"+page_num+"']").attr("selected",true);
        show_chp_list(id);
    }
}

var show_flag = false;
function show_introduce(){
    var tab_2_div = $("#tab_2");
    var tmp_div = tab_2_div.next();
    if(!show_flag){
        tab_2_div.removeClass('limit_lines-3');
        tmp_div.removeClass('open_btn');
        tmp_div.addClass('close_btn');
        show_flag = true;
    }else{
        tab_2_div.addClass('limit_lines-3');
        tmp_div.removeClass('close_btn');
        tmp_div.addClass('open_btn');
        show_flag = false;
    }
}