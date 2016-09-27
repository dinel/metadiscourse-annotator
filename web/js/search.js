/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$( document ).ready(function() {
    $('#search-button').click(function() {
        var search_term  = $('#search-field').val();
        var corpus = $('#corpus-id').val();
        window.location.href = "/search/term/" + corpus + "/" + search_term ;
    });
    
    $('.concordance-left').each(function() {
        $(this).html($.fn.textWidth($(this).html(), 335, true));
    });
    
    $('.concordance-right').each(function() {
        $(this).html($.fn.textWidth($(this).html(), 335, false));
    });
    
    $('.more-info').hide();
    
    $("#results").on('click', '.concordance', function() {        
        var node = $(this).parent().parent().next().find('.more-info-block');
        if(node.html()) {
            node.parent().toggle();
            return;
        }
        
         $.ajax({
            type: 'POST',
            url: '/search/retrieve_info/' + $(this).attr("id"),
            dataType: 'json',
            success: function(data) {
                var display = "<strong>Annotator: </strong>" + data.annotator;
                display += "&nbsp;&nbsp;|&nbsp;&nbsp;<strong>Sense selected: </strong> " + data.sense;
                display += "&nbsp;&nbsp;|&nbsp;&nbsp;<strong>Category: </strong> " + data.category;
                display += "&nbsp;&nbsp;|&nbsp;&nbsp;<strong>Polarity: </strong> " + data.polarity;
                display += "<br/><strong>Notes: </strong>" + data.comments;
                display += "<br/><strong>Source: </strong>" + data.source;
                
                if(data.uncertain === true) {
                    display += "<br/><strong style='text-color: red'>The annotator was uncertain about this annotation!!</strong>";
                }
                display += "<br/>";
                
                node.html(display);
            }
        });
        
        node.parent().show();
    });
    
    /** Shows/hides concordances no the basis of their class */
    $("#results").on('change', ':checkbox', function() {
       if(this.checked) {
           $('.row-' + $(this).attr('class')).show();
       } else {
           $('.row-' + $(this).attr('class')).hide();
       }
    });
    
    /** Shows/hides all concordances */
    $('#results').on('click', '#all', function() {
       if(this.checked) {
           $('.instance :checkbox').prop("checked", true);
           $('[class^="row-"]').show();
       } else {
           $('.instance :checkbox').prop("checked", false);
           $('[class^="row-"]').hide();
       }
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
            $.fn.textWidth.fakeEl.html("");
            return ret_str;
        }
        ret_str = tmp_str;
    }
    
    $.fn.textWidth.fakeEl.html("");    
    return ret_str;
};