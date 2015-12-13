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
                $('#sense-id').val(data.current_sense_id);
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
                
                // categories
                var primary_cat_html = "<option value='0'>...</option>";
                for(var i = 0; i < data.parent_categories.length; i++) {
                    primary_cat_html += "<option value='" + data.parent_categories[i][0] + "'>" + data.parent_categories[i][1] + "</option>";
                }
                $('#primary-category').html(primary_cat_html);
                $('#primary-category').val(data.parent_category_id);
                
                if(data.sub_categories.length !== 0) {
                    var sub_categories_html = "<option value='0'>...</option>";
                    for(var i = 0; i < data.sub_categories.length; i++) {
                        sub_categories_html += "<option value='" + data.sub_categories[i][0] + "'>" + data.sub_categories[i][1] + "</option>";
                    }
                    $('#secondary-category').html(sub_categories_html);
                    $('#secondary-category').val(data.sub_category_id);
                    $('#subcategory-div').show();
                } else {
                    $('#subcategory-div').hide();
                }
                
                // the rest: polarity, uncertain
                $("#slider").slider("value", data.polarity);
                $("#polarity").val(data.polarity);
                $('#uncertain').prop("checked", data.uncertain);
            }
        });
    });
    
    $('#primary-category').change(function() {
       var sel = $('#primary-category').val();
       $.ajax({
            type: 'POST',
            url: '/utils/subcategory/' + $('#primary-category').val(),
            dataType: 'json',
            success: function(data) {
                if(data.sub_categories.length > 0) {
                    var secondary_cat_html = "<option value='null'>...</option>";
                    for(var i = 0; i < data.sub_categories.length; i++) {
                        secondary_cat_html += "<option value='" + data.sub_categories[i][0] + "'>" + data.sub_categories[i][1] + "</option>";
                    }
                    $('#secondary-category').html(secondary_cat_html);
                    $('#subcategory-div').show();
                } else {
                    $('#subcategory-div').hide();
                    $('#secondary-category').html("");
                }
                
                $('#update-annotation').removeClass('disabled');
                $('#update-annotation').addClass('red');
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
        var category = "test";
        if($('#secondary-category').val() === "null" || $('#secondary-category').val() === null) {
            category = $('#primary-category').val();
        } else {
            category = $('#secondary-category').val();
        }
        var polarity = $("#polarity").val();
        var uncertain = 0;
        if($('#uncertain').is(':checked')) uncertain = 1;
        
        if($('#comment').val()) url = '/document/annotation/add/' + token + '/' + sense + '/' + category + '/' + polarity + '/' + uncertain + '/' + $('#comment').val();
        else url = '/document/annotation/add/' + token + '/' + sense + '/' + category + '/' + polarity + '/' + uncertain;
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
    
    $('#comment').on('change keyup paste', function() {
        $('#update-annotation').removeClass('disabled');
        $('#update-annotation').addClass('red');   
    });
    
    $('#uncertain').change(function() {
        $('#update-annotation').removeClass('disabled');
        $('#update-annotation').addClass('red');   
    });
    
});

$(function() {
    $( "#slider" ).slider({
        value:0,
        min: -5,
        max: 5,
        step: 1,
        slide: function( event, ui ) {
            $( "#polarity" ).val( ui.value );
            $('#update-annotation').removeClass('disabled');
            $('#update-annotation').addClass('red');   
        }
    });
    $( "#polarity" ).val( $( "#slider" ).slider( "value" ) );
});