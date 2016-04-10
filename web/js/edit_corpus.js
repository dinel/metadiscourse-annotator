/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var typingTimer;                
var doneTypingInterval = 500;  

$( document ).ready(function() {
    $('#add-existing-div').hide();
    
    $('#existing-text').click(function() {
        $('#add-existing-div').show();
    });
    
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
    
    /* Inspired by http://stackoverflow.com/questions/4220126/run-javascript-function-when-user-finishes-typing-instead-of-on-key-up */
    $('#text-name').on('keyup', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(searchTexts, doneTypingInterval);
    });
    
    $('#text-name').on('keydown', function () {
        clearTimeout(typingTimer);
    });    
});

function searchTexts() {
    var hint = $('#text-name').val();
    var corpus = $("#corpus-id").val();
    if(hint.length > 3) {
        $.ajax({
            type: 'POST',
            url: '/admin/corpus/list_texts/' + corpus + "/" + hint, 
            dataType: 'json',
            success: function(data) {
                var contents = "<ul>";
                for(var i = 0; i < data.texts.length; i++) {
                    contents += "<li>" + data.texts[i][1];
                    contents += "<a href='/admin/corpus/add_existing/" + corpus + "/" + data.texts[i][0] + "'>Add</a>";
                    contents += "</li>";
                }
                contents += "</li>";
                $('#list-files').html(contents);
            }
        });
    }
}