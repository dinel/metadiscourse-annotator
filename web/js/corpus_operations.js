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
                $('<i class="fas fa-thumbtack green" title="Text pinned"></i>').insertBefore(pos);
                $(this).parents('.dropdown').find('.pin-text').addClass('d-none');
                $(this).parents('.dropdown').find('.unpin-text').removeClass('d-none');
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
                $(this).parents('.dropdown').find('.fa-thumbtack').remove();
                $(this).parents('.dropdown').find('.unpin-text').addClass('d-none');
                $(this).parents('.dropdown').find('.pin-text').removeClass('d-none');
            }  
        });        
    }); 
    
    $('.done-text').click(function() {
        var text = $(this).data('text');
        $.ajax({
            type: 'POST',
            url: '/corpus/text_done/' + text,
            dataType: 'json',
            context: this, 
            success: function(data) {
                var pos = $(this).parents('.dropdown').children().first();
                $('<i class="fas fa-check green" title="Text done"></i>').insertBefore(pos);
            }  
        });        
    }); 
    
    $('.undone-text').click(function() {
        var text = $(this).data('text');
        $.ajax({
            type: 'POST',
            url: '/corpus/text_undone/' + text,
            dataType: 'json',
            context: this, 
            success: function(data) {
                $(this).parents('.dropdown').find('.fa-check').remove();
            }  
        });        
    }); 
});

function orderTexts() {
    var doneTexts = $('.in-a-box').has('.fa-check');
    doneTexts.appendTo("#texts");
    var pinnedTexts = $('.in-a-box').has('.fa-thumbtack');
    pinnedTexts.prependTo('#texts');    
};
