/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* global callFunction */

var explanations = [];
var currentToken = 0;
var discardTaggleEvent = false;
var ctrlIsDown = false;
var annotationAreaOn = false;
var currentSenses = [];
var goNext = false;

var nextMarkerQueue = [];

$( document ).ready(function() {
    $('#tag-attributes').hide();
    $('#reprocess').hide();
    $('.sense-group').hide();
    
    $(document).keyup(function(e) {
        if(e.which === 17) {
            ctrlIsDown = false;
        }
    });
    
    $(document).keydown(function(e) {
        if(e.which === 17) {
            ctrlIsDown = true; 
        }
               
        if(ctrlIsDown && annotationAreaOn && e.which === 48) { 
            $('#not-marker').trigger('click');
            return false;
        } 
        
        /* Select Ctrl + Shift + [1-9] */
        if(ctrlIsDown && annotationAreaOn && e.which > 48 && e.which < 58) {           
            $('#list-senses').val(currentSenses[e.which - 49]);
            $('#list-senses').selectmenu("refresh");
            $('#list-senses').trigger('change');
            
            return false;
        }
        
        if(ctrlIsDown && e.which === 83) {
            $('#update-annotation').trigger('click');
            return false;
        }
        
        /* Next marker Ctrl+> */
        if(ctrlIsDown && e.which === 190) {
            $('#next').trigger('click');
            return false;
        }
        
        /* Previous marker Ctrl+< */
        if(ctrlIsDown && e.which === 188) {
            $('#previous').trigger('click');
            return false;
        }
    });
    
    /* Call display token ? */
    if(callFunction) {
        $('#tag-attributes').show(); 
        annotationAreaOn = true;
        $.ajax({
            type: 'POST',
            url: '/document/marker/' + callFunction,
            dataType: 'json',
            success: function(data) {
                updateAnnotationPanel(data);
            }
        });        
    }
    
    /* Fiter what is displayed */
    $('#selectors').on('change', ':checkbox', function() {
        if(discardTaggleEvent) {
            discardTaggleEvent = false;
            return;
        }
        
        if(! $(this).attr('id')) return;
        
        var type = $(this).attr('id').substring(0,2);
        var target = $(this).attr('id').substring(3);
        if(! this.checked)  {
            if(type == "mk") {
                $("#maindoc .dsp-" + target).addClass('plain');
                $(this).parent().parent().find(".sn-filter").each(function(index) {                    
                    discardTaggleEvent = true;
                    $(this).bootstrapToggle('off');
                });
            } else if(type == "an") {
                $("#maindoc .meta-marker-todo.dsp-" + target).addClass('plain');
                checkAllOff($(this).parents(".sense-group"));
            } else {
                $("#maindoc .sense" + target).addClass('plain');
                checkAllOff($(this).parents(".sense-group"));
            }
        } else {
            if(type == "mk") {
                $("#maindoc .dsp-" + target).removeClass('plain');
                $(this).next().children('.toggle-on').html("All");
                $(this).parent().parent().find(".sn-filter").each(function(index) {
                    discardTaggleEvent = true;
                    $(this).bootstrapToggle('on');                    
                });
            } else if(type == "an") {
                $("#maindoc .meta-marker-todo.dsp-" + target).removeClass('plain');
                checkAllOn($(this).parents(".sense-group"));
            }
            else {
                $("#maindoc .sense" + target).removeClass('plain');
                checkAllOn($(this).parents(".sense-group"));
            }
        }
    });
    
    $('#filter-by-senses').on('change', ':checkbox', function() {
        if(this.checked) {
            $('.sense-group').fadeIn();
        } else {
            $('.sense-group').hide();
        }
    });
    
    /*
     * Called when the user clicks to close the annotation area
     */
    $('#close-annotation-area').click(function() {
        annotationAreaOn = false;
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
        annotationAreaOn = true;
        var currentTag = $(this).attr("id");
        nextMarkerQueue = [];
        $('#tag-attributes').show(); 
        $.ajax({
            type: 'POST',
            url: '/document/marker/' + currentTag,
            dataType: 'json',
            success: function(data) {
                updateAnnotationPanel(data);
                preFetch(currentTag);
            }
        });
    });
    
    $('#next').click(function() {
        annotationAreaOn = true;
        $('#tag-attributes').show();
        disableSave();
        
        if(nextMarkerQueue.length > 0) {
            data = nextMarkerQueue.shift();
            updateAnnotationPanel(data);
            $('#locate').trigger('click');
            preFetch(currentToken);
        } else {
            var nextToken = getNextToken(currentToken);

            $.ajax({
                type: 'POST',
                url: '/document/next/' + nextToken,
                dataType: 'json',
                success: function(data) {
                    updateAnnotationPanel(data);
                    $('#locate').trigger('click');
                    preFetch(nextToken);
                }
            });
        }
    });
    
    $('#previous').click(function() {
        annotationAreaOn = true;
        $('#tag-attributes').show();
        disableSave();
        nextMarkerQueue = [];
        
        var prevToken = currentToken;
        var metaMarkers = $('.meta-marker').toArray();
        for(var i = 0; i < metaMarkers.length; i++) {
            if((metaMarkers[i].id === currentToken) && (i > 0)) {
                prevToken = metaMarkers[i-1].id;
                break;                
            }
        }
        
        $.ajax({
            type: 'POST',
            url: '/document/prev/' + prevToken,
            dataType: 'json',
            success: function(data) {
                updateAnnotationPanel(data);
                $('#locate').trigger('click');
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
                
                enableSave();
            }
        });
    });
    
    $('#secondary-category').change(function() {
        enableSave();
    });
    
    /*
     * Function triggered when the user changes the selection of a sense
     */           
    $('#list-senses').on( "selectmenuchange", function() {
        enableSave();
        $('#sense-id').val(this.value);
        $('#not-marker').removeClass('select-annotation');
        $('#list-senses-container').addClass('select-annotation');
        
        $('#explanation').html(explanations[$('#list-senses').val() - 1]);

        
        $.ajax({
            type: 'POST',
            url: '/utils/data-by-sense/' + this.value,
            dataType: 'json',
            success: function(data) {
                $("#polarity").val(data.polarity);
                $("#slider").slider("value", data.polarity);
                
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
                
                if(data.selected_leaf !== -1) {
                    $('#secondary-category option[value="' + data.selected_leaf + '"]').prop('selected', true);
                }
                
                if(data.selected_parent !== -1) {
                    $('#primary-category option[value="' + data.selected_parent + '"]').prop('selected', true);
                } else {
                    $('#primary-category option[value="0"]').prop('selected', true);
                }
            }
        });
    });
    
    /*
     * Function triggered when the user indicates that a word is not a 
     * meta-discourse marker
     */
    $('#not-marker').click(function() {
        enableSave();
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
                disableSave();

                
                $("#message-area").html("Saved!");
                $("#message-area").show();
                setTimeout(function() { $("#message-area").fadeOut(); }, 3000);
                updateDisplayedAnnotation(sense);
                if(goNext === true) {
                    goNext = false;
                    $('#next').trigger('click');
                }
            }
        });
    });
    
    $('#save-next').click(function() {
        $('#update-annotation').trigger('click');
        goNext = true;
    });
    
    $('#comment').on('change keyup paste', function() {
        enableSave();
    });
    
    $('#uncertain').change(function() {
        enableSave();
    });    
    
    $('#locate').click(function() {
        if($('#pointer').css('display') !== 'none') {
            $('#pointer').finish();
        }
        var pos = $('#' + currentToken).offset();
        pos.left = pos.left + 108 + $('#' + currentToken).width();
        pos.top = pos.top - 10;
        $('#pointer').show();
        $('#pointer').offset(pos);
        $('#pointer').animate({
            left: '-=100px'
        }, "slow", function() {
            $('#pointer').fadeOut("slow");
        });
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
            enableSave();
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
    currentSenses = []
    for(var i = 0; i < data.senses.length; i++) {
        selected = "";
        if(data.senses[i][1] === data.current_sense) {
            selected = " selected ";
            $('#explanation').html(data.senses[i][2]);
        }
        sense_html += "<option value='" + data.senses[i][0] + "'" + selected + ">" + data.senses[i][1] + "</option>";
        explanations.push(data.senses[i][2]);
        currentSenses.push(data.senses[i][0]);
    }
    $('#list-senses').html(sense_html);
    
    $('#list-senses').selectmenu({
        style: 'popup',
        width: 350,
        appendTo: '#tag-attributes'
    });
    $('#list-senses').selectmenu("refresh");

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

function checkAllOff(node) {    
    var states = node.find(".sn-filter").toArray();    
        
    var flag = states.reduce(function(state, item) {
        return state && !item.checked;
    }, true);
 
    if(flag) {
        discardTaggleEvent = true;
        node.parent(".mk-group").find(".mk-filter").bootstrapToggle('off')
    } else {
        node.parent(".mk-group").find(".mk-filter").next().children('.toggle-on').html("Some");
    }
}

function checkAllOn(node) {    
    var states = node.find(".sn-filter").toArray();    
        
    var flag = states.reduce(function(state, item) {
        return state && item.checked;
    }, true);
 
    if(flag) {
        discardTaggleEvent = true;
        node.parent(".mk-group").find(".mk-filter").bootstrapToggle('on')
        node.parent(".mk-group").find(".mk-filter").next().children('.toggle-on').html("All");
    } else {
        discardTaggleEvent = true;
        node.parent(".mk-group").find(".mk-filter").bootstrapToggle('on');
        node.parent(".mk-group").find(".mk-filter").next().children('.toggle-on').html("Some");
    }
}

/**
 * Function which does the prefetching of next elements to speed up the
 * user interaction with the platform
 * @param start the element from which the fetching to start
 */
function preFetch(start) {
    if(nextMarkerQueue.length > 0) {
        start = nextMarkerQueue[nextMarkerQueue.length - 1].tok_id;
    } 
    
    var nextToken = getNextToken(start);
    
    if(nextToken !== start) {
        $.ajax({
            type: 'POST',
            url: '/document/next/' + nextToken,
            dataType: 'json',
            success: function(data) {
                if(! checkAlreadyInQueue(data.tok_id)) {
                    nextMarkerQueue.push(data);
                }

                if(nextMarkerQueue.length < 2) {
                    preFetch(nextToken);
                }
            }
        });
    }
}

/**
 * Function which checks whether there is already an element with idElement 
 * in the queue
 * @param idElement
 * @returns {Boolean} True if the element exists, False otherwise
 */
function checkAlreadyInQueue(idElement) {
    for(var i = 0; i < nextMarkerQueue.length; i++) {
        if(idElement === nextMarkerQueue[i].tok_id) {
            return true;
        }
    }
    
    return false;
}

/**
 * Returns the ID of the token that is next marker
 * @param {int} start the position after which the token should apper
 * @returns {int} the id of the token
 */
function getNextToken(start) {
    var nextToken = start;
    
    var metaMarkers = $('.meta-marker').toArray();
    for(var i = 0; i < metaMarkers.length - 1; i++) {
        if(metaMarkers[i].id === start && i < metaMarkers.length) {
            nextToken = metaMarkers[i+1].id;
            break;                
        }
    }
    
    return nextToken;
}

/**
 * Function which disables the save buttons
 */
function disableSave() {
    $('#update-annotation').removeClass('red');
    $('#update-annotation').addClass('disabled');
    $('#save-next').removeClass('red');
    $('#save-next').addClass('disabled');
}

/**
 * Function which enables the save buttons
 */
function enableSave() {
    $('#update-annotation').removeClass('disabled');
    $('#update-annotation').addClass('red');   
    $('#save-next').removeClass('disabled');
    $('#save-next').addClass('red');
}