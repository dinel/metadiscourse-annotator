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

/* global id_marker, cat_boxes */

$( document ).ready(function() {
    reorderCategories(categoriesLabel);
    $('[data-toggle="popover"]').popover();
    
    $('#add-alternative-button').click(function() {        
        var alternative = $('#txt-alternative').val();        
        $.ajax({            
            type: 'POST',
            url: '/admin/marker/add-alternative/' + id_marker + "/" + alternative,
            success: function(msg) {
                if(msg === "OK") {
                    $('#list-alternatives').append('<span class="alternative">' + alternative + '</span>');        
                } else {
                    $('#modal-msg').html(msg);
                    $('#myModal').modal('show');
                }
            }
        });
        $('#txt-alternative').val("");
    });
    
    $('#list-alternatives').on('click', '.alternative', function() {
        var answer = confirm("Are you sure you want to delete the form?");
        if(answer) {
            $.ajax({
                type: 'POST',
                url: '/admin/marker/remove-alternative/' + id_marker + "/" + $(this).text(),
                success: function(msg) {
                }
            });
            $(this).remove();
        }
    });
    
    $('#show-categories').click(function() {
       $('#cat-tree-intern').toggle(); 
       $('.msg-show-categories').toggle();
    });
});

function reorderCategories(categoriesLabel) {
    for(var i = 0; i < cat_boxes.length; i++) {
        for(var j = 1; j < cat_boxes[i].length; j++) {                    
            $('#' + categoriesLabel + '_categories_'+cat_boxes[i][j]).parent().parent().appendTo($('#parent-cat-'+cat_boxes[i][0]));
        }
    }
    $('#cat-tree-container').appendTo($('#' + categoriesLabel + '_categories'));
}