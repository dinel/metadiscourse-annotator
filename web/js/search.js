/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$( document ).ready(function() {
    $('#search-button').click(function() {
        var search_term  = $('#search-field').val();
        window.location.href = "/search/term/" + search_term ;
    });
    
    $('.concordance-left').each(function() {
        console.log($(this).html());
        $(this).html($.fn.textWidth($(this).html(), 350, true));
    });
    
    $('.concordance-right').each(function() {
        console.log($(this).html());
        $(this).html($.fn.textWidth($(this).html(), 350, false));
    });
    
});

/*
 * Function that returns a string that is not wider than a certain width
 * Adapted from http://stackoverflow.com/questions/1582534/calculating-text-width-with-jquery
 */
$.fn.textWidth = function(text, width, reverse) {
    if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').appendTo(document.body);
    var tmp_str = "";
    var ret_str = "";
    var array_str = text.split(" ");
    if(reverse) array_str = array_str.reverse();
    
    for(var i = 0; i < array_str.length; i++) {
        if(reverse) {
            tmp_str = array_str[i] + " " + tmp_str;
        } else {
            tmp_str = tmp_str + array_str[i] + " ";
        }
        var htmlText = tmp_str;
        htmlText = $.fn.textWidth.fakeEl.text(htmlText).html(); //encode to Html
        htmlText = htmlText.replace(/\s/g, "&nbsp;"); //replace trailing and leading spaces
        $.fn.textWidth.fakeEl.html(htmlText).css('font', this.css('font'));
        if($.fn.textWidth.fakeEl.width() > width) {            
            return ret_str;
        }
        ret_str = tmp_str;
    }
    
    return ret_str;
};