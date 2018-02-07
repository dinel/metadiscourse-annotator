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
    
    $('#add-alternative-button').click(function() {
        $('#list-alternatives').append('<span class="alternative">' + $('#txt-alternative').val() + '</span>');        
        
        $.ajax({
            type: 'POST',
            url: '/admin/marker/add-alternative/' + id_marker + "/" + $('#txt-alternative').val(),
            success: function(msg) {
            }
        });
        $('#txt-alternative').val("");

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