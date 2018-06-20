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
    $('.left-context').each(function(index) {
        trimText($(this).children().get(0), 0);
    });
    
    $('.right-context').each(function(index) {
        trimText($(this).children().get(0), 1);
    });
});

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