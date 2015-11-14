/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$( document ).ready(function() {
  $('.editable-link').mouseenter(function() {
     $(this).children().last().addClass('fa fa-pencil-square-o'); 
  });
  
  $('.editable-link').mouseleave(function() {
      $(this).children().last().removeClass('fa fa-pencil-square-o'); 
  });
});