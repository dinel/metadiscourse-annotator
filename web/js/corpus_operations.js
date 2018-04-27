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
    orderTexts();
    
    $('.pin-text').click(function() {
        var text = $(this).data('text');
        $.ajax({
            type: 'POST',
            url: '/corpus/pin_text/' + text,
            dataType: 'json',
            context: this, 
            success: function(data) {
                var pos = $(this).parents('.dropdown').children().first();
                $('<span class="glyphicon glyphicon-pushpin right-margin-1em" aria-hidden="true" title="Text pinned"></span>').insertBefore(pos);
            }  
        });        
    });    
    
    $('.unpin-text').click(function() {
        var text = $(this).data('text');
        $.ajax({
            type: 'POST',
            url: '/corpus/unpin_text/' + text,
            dataType: 'json',
            context: this, 
            success: function(data) {
                $(this).parents('.dropdown').find('.glyphicon-pushpin').remove();
            }  
        });        
    }); 
});

function orderTexts() {
    var pinnedTexts = $('.in-a-box').has('.unpin-text');
    pinnedTexts.prependTo('#texts');    
};