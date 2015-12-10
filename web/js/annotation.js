/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$( document ).ready(function() {
    $('#tag-attributes').hide();
    
    /*
     * Called when the user clicks to close the annotation area
     */
    $('#close-annotation-area').click(function() {
        $('#tag-attributes').hide();
    });
    
    /*
     * Called when the user clicks on a meta-discourse marker
     * Opens the annotation dialogue
     */
    $('.meta-marker').click(function() {
        $('#tag-attributes').show(); 
        $.ajax({
            type: 'POST',
            url: '/document/marker/' + $(this).attr("id"),
            dataType: 'json',
            success: function(data) {
                /* For debugging purposes alert(JSON.stringify(data));*/
                $('#marker').html(data.mark_text);
                $('#tok_id').val(data.tok_id);
                $('#mark_id').val(data.mark_id);
                if(data.current_sense) {
                    $('#current-annotation-label').html("Current annotation: ");
                    $('#current-annotation').html(data.current_sense);                    
                } else {
                    $('#current-annotation-label').html("This marker hasn't been annotated yet!!!");
                    $('#current-annotation').html("");
                }
                $('#comment').val(data.comment);
                var sense_html = "<option>...</option>";
                for(var i = 0; i < data.senses.length; i++) {
                    sense_html += "<option value='" + data.senses[i][0] + "'>" + data.senses[i][1] + "</option>";
                }
                $('#list-senses').html(sense_html);                
            }
        });
    });
    
    /*
     * Function triggered when the user changes the selection of a sense
     */
    $('#list-senses').change(function() {
        $('#current-annotation-label').html("Current annotation: ");
        $('#current-annotation').html(this.options[this.value].innerHTML);
        $('#update-annotation').removeClass('disabled');
        $('#update-annotation').addClass('red');
        $('#sense-id').val(this.value);
    });
    
    /*
     * Function triggered when the user indicates that a word is not a 
     * meta-discourse marker
     */
    $('#not-marker').click(function() {
        $('#current-annotation-label').html("Current annotation: ");
        $('#current-annotation').html('Not a real metadiscourse marker');
        $('#update-annotation').removeClass('disabled');
        $('#update-annotation').addClass('red');
        $('#sense-id').val(0);
    });
    
    /*
     * Function called when the user saves the annotation to the database
     */
    $('#update-annotation').click(function() {
        var url = "";
        var token = $('#tok_id').val();
        var sense = $('#sense-id').val();
        if($('#comment').val()) url = '/document/annotation/add/' + token + '/' + sense + '/' + $('#comment').val();
        else url = '/document/annotation/add/' + token + '/' + sense;
        $.ajax({
            type: 'POST',
            url: url,
            success: function(data) {                
                $('#' + token).removeClass();
                $('#' + token).addClass("meta-marker");
                $('#' + token).addClass(data.style); 
                $('#current-annotation').html(data.current_sense);
                $('#update-annotation').removeClass('red');
                $('#update-annotation').addClass('disabled');
                
                $("#message-area").html("Saved!");
                $("#message-area").show();
                setTimeout(function() { $("#message-area").fadeOut(); }, 3000);
            }
        });
    });
});
