/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$( document ).ready(function() {  
  $('#form_the_text').hide();
  $('#form_upload_xml').hide();
  $('#form_upload_text').hide();
  
  $('#form_button').change(function() {      
    if($('#form_button').val() === "1") {
        $('#form_upload_text').show();
        $('#form_the_text').hide();
        $('#form_upload_xml').hide();
    }
    
    if($('#form_button').val() === "2") {
        $('#form_upload_text').hide();
        $('#form_the_text').show();
        $('#form_upload_xml').hide();
    }
      
    if($('#form_button').val() === "3") {
        $('#form_upload_text').hide();
        $('#form_the_text').hide();
        $('#form_upload_xml').show();
    }
  });
});