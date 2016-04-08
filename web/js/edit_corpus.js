/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$( document ).ready(function() {
    $('.value-corpus').click(function() {
        console.log($(this).attr("id"));
        var value = $(this).attr("id");
        var corpus = $("#corpus-id").val();
        
        $.ajax({
            type: 'POST',
            url: '/admin/corpus/category/' + corpus + "/" + value,
            dataType: 'json',
            success: function(data) {
                alert("success");
            }
        });
    });
    
    $('.editable-link').mouseenter(function() {
        $(this).children().last().addClass('fa fa-times red'); 
    });
  
    $('.editable-link').mouseleave(function() {
        $(this).children().last().removeClass('fa fa-times red'); 
    });
    
    $('.editable-link').click(function() {
        var value = $(this).attr('id');
        var corpus = $("#corpus-id").val();
        var r = confirm("Do you really want to remove the text from the corpus?" + "C" + corpus + "T" + value);
        if(r == true) {
            $.ajax({
                type: 'POST',
                url: '/admin/corpus/remove_text/' + corpus + "/" + value,
                dataType: 'json',
                success: function(data) {
                    alert("text removed" + data);
                }
            });
        }
        
    });
});