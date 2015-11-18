/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$( document ).ready(function() {
    $('#tag-attributes').hide();
    $('.meta-marker').click(function() {
        $('#tag-attributes').show(); 
        $.ajax({
            type: 'POST',
            url: '/document/marker/' + $(this).attr("id"),
            dataType: 'json',
            success: function(data) {
                /*alert(JSON.stringify(data));*/
                $('#tok_id').val(data.tok_id);
                $('#mark_id').val(data.mark_id);
                $('#current-annotation').html(data.current_sense);
                $('#comment').val(data.comment);
                var sense_html = "";
                for(var i = 0; i < data.senses.length; i++) {
                    sense_html += "<li>" + data.senses[i][1];
                    sense_html += "<a class='select_annotation' href='javascript:select_annotation(" 
                            + data.tok_id + "," + data.senses[i][0] + ")" + "'>Select &raquo;</a>";
                }
                $('#list-senses').html('<ul>' + sense_html + '</ul>');
            }
        });
    });
    
    $('#form_save').click(function() {        
        $.ajax({
            type: 'POST',
            url: '/document/sense/add/' + $('#tok_id').val() + '/' + $('#form_definition').val(),
            success: function() {

            }
        });
        alert("Here");
    });    
});

function select_annotation(token, sense) {
    var url = "";
    if($('#comment').val()) url = '/document/annotation/add/' + token + '/' + sense + '/' + $('#comment').val();
    else url = '/document/annotation/add/' + token + '/' + sense;
    $.ajax({
        type: 'POST',
        url: url,
        success: function() {
            /*alert("Success");*/
            $('#' + token).css('background-color', 'red');
        }
    });        
}

