/* 
 * Copyright 2018 dinel.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

$( document ).ready(function() {
    $("#results").on('click', '.details', function(e) {
        var node = $(this).next();
        $(this).find(".fa-plus").toggle();
        $(this).find(".fa-minus").toggle();
        
        if(node.hasClass("more-info-shown")) {
            node.removeClass("more-info-shown")
        } else {
            node.addClass("more-info-shown")
        }
        
        if(node.html()) {
            node.toggle();
            return;
        }
        
        node.html('<div class="text-center"><img width="30px;" src="/ajax-loader.gif"></div>');
        $.ajax({
            type: 'GET',
            url: '/search/retrieve_info/' + $(this).attr("id"),
            success: function(data) {
                var display = "<strong>Annotator: </strong>" + data.annotator;
                display += "<br/><strong>Sense selected: </strong> " + data.sense;
                display += "&nbsp;&nbsp;|&nbsp;&nbsp;<strong>Category: </strong> " + data.category;
                display += "&nbsp;&nbsp;|&nbsp;&nbsp;<strong>Polarity: </strong> " + data.polarity;
                display += "<br/><strong>Notes: </strong>" + data.comments;
                display += "<br/><strong>Source: </strong>" + data.source_title;
                display += "<span style='margin-left: 5em'><a target='_blank' " 
                        + " href='/document/" + data.id_document + "/" + data.id_token + "'"
                        + ">Go to annotation</a></span>";
                if(data.source) {
                    display += "<br/><strong>Source segment: </strong>" + data.source;
                    display += "<br/><strong>Target segment: </strong>" + data.target;
                }
                
                if(data.uncertain === true) {
                    display += "<br/><strong style='text-color: red;'>The annotator was uncertain about this annotation!!</strong>";
                }
                display += "<br/>";                
                node.html(display);
                $(this).find(".fa-plus").toggle();                
            }
        });
        e.preventDefault();
        
        node.parent().show();
    }); 
    
    $('.left-context').each(function(index) {
        trimText($(this).children().get(0), 0);
    });
    
    $('.right-context').each(function(index) {
        trimText($(this).children().get(0), 1);
    });
    
    $('#filter-text-btn').click(function(e) {
        var word = $('#filter-text').val();
        $('.concordance').each(function(i, obj) {
           if(!$(this).find('.left-context').text().includes(word) &&
              !$(this).find('.right-context').text().includes(word)) {
                $(this).hide();          
           }
        });
        e.preventDefault();
    });
    
    $('#filter-clear-btn').click(function(e) {
        $('#filter-text').val("");
        $('.concordance').each(function(i, obj) {
           $(this).show();
        });
        e.preventDefault();
    });
    
    $('#sort-position').change(function(e){
        var selected = parseInt($('#sort-position').val());
        var context = "";
        if(selected < 0) {
            context = '.left-context';
        } else {
            context = '.right-context';
        }
        console.log(selected, context)
        
        var sortedDivs = $('.concordance').sort(function(a, b) {            
            var aord = getComparator($(a).find(context).text(), selected);
            var bord = getComparator($(b).find(context).text(), selected);
            return aord > bord;
        });
        
        $('#results').html(sortedDivs);
    });
});

function getComparator(str, pos) {    
    var words = str.split(" ");
    if(pos > 0) {
        return words[pos - 1];
    } else {
        return words[words.length + pos - 1];
    }
}

/*
 * Adapted from http://jsfiddle.net/x1Lvq2ex/
 */
function trimLeft(row){
    var trimContents = function(row, node){
        while (row.scrollWidth > row.offsetWidth) {          
            var childNode = node.firstChild;
            
            if (!childNode)
                return true;
            
            if (childNode.nodeType === document.TEXT_NODE){
                
                trimText(row, node, childNode);
            }
            else {
                var empty = trimContents(row, childNode);
                if (empty){
                    node.removeChild(childNode);
                }
            }
        }
    }
    var trimText = function(row, node, textNode){
        var value = '...' + textNode.nodeValue;
        do {
            value = '...' + value.substr(4);
            textNode.nodeValue = value;
            
            if (value === '...'){
                node.removeChild(textNode);
                return;
            }
        }
        while (row.scrollWidth > row.offsetWidth);
    }

    trimContents(row, row);    
}

function trimText(row, direction){
    var trimContents = function(row, node, direction){
        while (row.scrollWidth > row.offsetWidth) {
            var childNode;
        
            if(direction == 0) {
                childNode = node.firstChild;
            } else {
                childNode = node.lastChild;
            }
            
            if (!childNode)
                return true;
            
            if (childNode.nodeType === document.TEXT_NODE){
                if(direction === 0) {
                    leftTrimText(row, node, childNode);
                } else {
                    rightTrimText(row, node, childNode);
                }
            }
            else {
                var empty = trimContents(row, childNode, direction);
                if (empty){
                    node.removeChild(childNode);
                }
            }
        }
    }
    
    var leftTrimText = function(row, node, textNode){
        var value = '...' + textNode.nodeValue;
        do {
            value = '...' + value.substr(4);
            textNode.nodeValue = value;
            
            if (value === '...'){
                node.removeChild(textNode);
                return;
            }
        }
        while (row.scrollWidth > row.offsetWidth);
    }
    
    var rightTrimText = function(row, node, textNode){
        var value = textNode.nodeValue + "...";
        do {
            value = value.substr(0, value.length - 4) + "...";
            textNode.nodeValue = value;
            
            if (value === '...'){
                node.removeChild(textNode);
                return;
            }
        }
        while (row.scrollWidth > row.offsetWidth);
    }

    trimContents(row, row, direction);    
}