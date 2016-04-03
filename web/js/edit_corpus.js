/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$( document ).ready(function() {
    $('.value-corpus').click(function() {
        console.log($(this).attr("id"));
        value = $(this).attr("id");
        corpus = $("#corpus-id").val();
        
        $.ajax({
            type: 'POST',
            url: '/admin/corpus/category/' + corpus + "/" + value,
            dataType: 'json',
            success: function(data) {
                alert("success");
            }
        });
    });
});