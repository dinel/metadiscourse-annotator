/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$( document ).ready(function() {
    $('#filter-on-any').click(function() {
        var filter = getFilter();
        
        $.ajax({
            type: 'POST',
            url: '/admin/corpus/filter_any' + filter,
            dataType: 'json',
            success: function(data) {
                var h = "";
                for(var i = 0; i < data.corpora.length; i++) {
                    h += getHTML(data.corpora[i]);
                }
                $('#list-corpora').html(h);
            }  
        });
    });
    
    $('#filter-on-all').click(function() {
        var filter = getFilter();
        
        $.ajax({
            type: 'POST',
            url: '/admin/corpus/filter_all' + filter,
            dataType: 'json',
            success: function(data) {
                var h = "";
                for(var i = 0; i < data.corpora.length; i++) {
                    h += getHTML(data.corpora[i]);                
                }
                $('#list-corpora').html(h);
            }  
        });
    });
});

function getFilter() {
    var filter = "";

    $('.filter-value').each(function(i) {
        if($(this).is(':checked')) {
            filter += $(this).attr('id');
        }
    });

    if(filter) filter = "/" + filter;
    
    return filter;
}

function getHTML(data) {
    var h = "";
    
    h += '<div class="voffset3">';
    h += '<strong>' + data[1] + '</strong><br/>';
    h += data[2].substring(0, 200) + '...<br/>';
    h += 'No of texts: ' + data[3] + ' <br/>';
    h += '<a id="new-text" href="/admin/corpus/new/' + data[0] + '" class="btn btn-default" role="button">Edit</a>';
        /*<a id="new-text" href="{{ path('admin_text_add') }}" class="btn btn-default" role="button">Delete</a>*/
    h += '</div>';
    
    return h;
}