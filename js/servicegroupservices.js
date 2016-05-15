 
      function set_interval (){
      setInterval(function(){  
      var x ="";
      
      var id = "";
      
      var svg_val;
      
      var container ="";

      var url ="";
      
      url = base_url + "includes/dashlets/servicegroupservices/servicegroupservices_refresh.php";
      
        $('.svg').each(
      
          function(){
            
            x = $(this).attr('id'); 
            
            id = $(this).attr('id');
      
          	container = id.replace('_dashlet','_table');

            
          	svg_val = $('#'+x).children().attr('value');
          	
            
          	$.ajax({ "url": url, async: false, type:'POST', data:{svg:svg_val}, "success": function (result){$('#'+container).html(result);} } );
      
          }
      
          );
      
        }
  ,300000);}
 
