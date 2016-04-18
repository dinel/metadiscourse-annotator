/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var explanations = [];
var currentToken = 0;

$( document ).ready(function() {
    $('#tag-attributes').hide();
    $('#reprocess').hide();
    
    /*
     * Called when the user clicks to close the annotation area
     */
    $('#close-annotation-area').click(function() {
        $('#tag-attributes').hide();        
    });
    
    /*
     * Called when a missing markable is added
     */
    $('#add-markable').click(function() {
        $('#reprocess').show();
        var win = window.open("/admin/marker/add/" + getSelectionText(), '_blank');
        win.focus();
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
                updateAnnotationPanel(data);
            }
        });
    });
    
    $('#next').click(function() {
        $('#tag-attributes').show();
        $.ajax({
            type: 'POST',
            url: '/document/next/' + currentToken,
            dataType: 'json',
            success: function(data) {
                updateAnnotationPanel(data);
            }
        });
    });
    
    $('#previous').click(function() {
        $('#tag-attributes').show();
        $.ajax({
            type: 'POST',
            url: '/document/prev/' + currentToken,
            dataType: 'json',
            success: function(data) {
                updateAnnotationPanel(data);
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
        $('#update-annotation').removeClass('disabled');
        $('#update-annotation').addClass('red');
        $('#sense-id').val(this.value);
    });
    
    /*
     * Function triggered when the user indicates that a word is not a 
     * meta-discourse marker
     */
    $('#not-marker').click(function() {
        $('#update-annotation').removeClass('disabled');
        $('#update-annotation').addClass('red');
        $('#sense-id').val(0);
        $('#not-marker').addClass('select-annotation');
        $('#list-senses-container').removeClass('select-annotation');
        $('#explanation').html("This is not a metadiscourse marker");
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
                $('#update-annotation').removeClass('red');
                $('#update-annotation').addClass('disabled');
                
                $("#message-area").html("Saved!");
                $("#message-area").show();
                setTimeout(function() { $("#message-area").fadeOut(); }, 3000);
                updateDisplayedAnnotation(sense);
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
    
    $('#list-senses').change(function() {
        $('#explanation').html(explanations[$('#list-senses').val() - 1]);
        $('#not-marker').removeClass('select-annotation');
        $('#list-senses-container').addClass('select-annotation');
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

function updateDisplayedAnnotation(current_annotation) {
    if(current_annotation) {
        if(current_annotation == "N/M" || current_annotation == "0") {
            $('#not-marker').addClass('select-annotation');
            $('#list-senses-container').removeClass('select-annotation');
        } else {
            $('#not-marker').removeClass('select-annotation');
            $('#list-senses-container').addClass('select-annotation');
        }
        $('#annotation-message').html("Annotation");                    
        $('.current-annotation').css("background-color", '#ffffff')
    } else {
        $('#annotation-message').html("Not annotated yet");
        $('#list-senses-container').removeClass('select-annotation');
        $('#not-marker').removeClass('select-annotation');
        $('.current-annotation').css("background-color", '#ffd');
        $('#explanation').html("");
        
    }
}

function updateAnnotationPanel(data) {
    /* For debugging purposes alert(JSON.stringify(data));*/
    currentToken = data.tok_id;
    
    $('#context').html(data.context);
    $('#marker').html(data.mark_text);
    $('#tok_id').val(data.tok_id);
    $('#mark_id').val(data.mark_id);
    $('#sense-id').val(data.current_sense_id);

    // select the annotation
    updateDisplayedAnnotation(data.current_sense);  
    
    // scroll to element
    $('html,body').animate({scrollTop: $('#' + data.tok_id).offset().top - 50});

    $('#comment').val(data.comment);
    var sense_html = "<option>...</option>";

    explanations = [];
    for(var i = 0; i < data.senses.length; i++) {
        selected = "";
        if(data.senses[i][1] == data.current_sense) {
            selected = " selected ";
            $('#explanation').html(data.senses[i][2]);
        }
        sense_html += "<option value='" + data.senses[i][0] + "'" + selected + ">" + data.senses[i][1] + "</option>";
        explanations.push(data.senses[i][2]);
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

function getSelectionText() {
    var text = "";
    if (window.getSelection) {
        text = window.getSelection().toString();
    } else if (document.selection && document.selection.type != "Control") {
        text = document.selection.createRange().text;
    }
    return text;
}