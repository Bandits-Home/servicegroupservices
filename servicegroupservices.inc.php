<?php
  #----------------------------------------------------------------
  #--Service Group Services Dashlet
  #--
  #----------------------------------------------------------------

  include_once(dirname(__FILE__).'/../dashlethelper.inc.php');
  servicegroupservices_dashlet_init();

  #----------------------------------------------------------------
  #--
  #--
  #----------------------------------------------------------------   
  FUNCTION servicegroupservices_dashlet_init(){
    $name = "servicegroupservices";
    $args = 
    array(
      DASHLET_NAME => $name,
      DASHLET_VERSION => "1.0",
	    DASHLET_DATE => "2014-12-03",
	    DASHLET_AUTHOR => "ITconvergence",
	    DASHLET_DESCRIPTION => "Service Group Services",					
	    DASHLET_FUNCTION => "servicegroupservices_dashlet_func",
	    DASHLET_TITLE => "Service Group Services 1.0",	
	    DASHLET_OUTBOARD_CLASS => "servicegroupservices_outboardclass",
	    DASHLET_INBOARD_CLASS => "servicegroupservices_inboardclass",
	    DASHLET_PREVIEW_CLASS => "servicegroupservices_previewclass",		
	    DASHLET_CSS_FILE => "css/servicegroupservices.css",  
	    DASHLET_JS_FILE => "js/servicegroupservices.js", 
	    DASHLET_WIDTH => "300px",
	    DASHLET_HEIGHT => "300px",
	    DASHLET_OPACITY => "1", 
      DASHLET_BACKGROUND => "",
      DASHLET_REFRESHRATE => 1,
    );
    register_dashlet($name,$args);
	}


  #----------------------------------------------------------------
  #--
  #--
  #---------------------------------------------------------------- 
  FUNCTION servicegroupservices_dashlet_func($mode=DASHLET_MODE_PREVIEW,$id="",$args=null){
    $output = "";
	  $imgbase = get_dashlet_url_base("servicegroupservices")."/images/";
    
	  switch ($mode){
	    
      case DASHLET_MODE_GETCONFIGHTML:
        $output .='<br/>'.gettext('Service Group:').'<br/><select name="svg" id="svg""> ';
		    $output .= servicegroupservices_get_servicegroups_option_list();
		    $output .='</select>';
	    break;

	    case DASHLET_MODE_OUTBOARD:
	    case DASHLET_MODE_INBOARD:    
	      $backendargs = array();
	      $svg = $args['svg'];
        $id = "sgs_" . random_string(6);
        $output .= '<div class="svg" id="'.$id.'_dashlet">';
        $output .= '<input type="hidden" name="'.$id.'_svg" value="'.$svg.'"/>';
        $output .= '<div id="'.$id.'_table">';
	      $output .= servicegroupservices_get_service_option_list($svg);
	      $output .= '</div>';
        $output .= '</div><script type="text/javascript">set_interval();</script>';
        
      break;
	  
	    case DASHLET_MODE_PREVIEW:
        $output = "<p><img src='".$imgbase."preview.png'></p>";
      break;
	  }
    return $output;
  }


  #----------------------------------------------------------------
  #--
  #--
  #----------------------------------------------------------------  
  FUNCTION servicegroupservices_get_servicegroups_option_list(){
    $option_list = '';
	  $option_list .='<option value="0" selected>Select group..</option>';
	  $groups = get_xml_servicegroup_objects();
	  $count = 1;
	  foreach($groups as $data){
      $data =(array)$data;
	    
      if ($count>1){
			  $option_list .='<option value="'.$data['servicegroup_name'].'">'.$data['servicegroup_name'].'</option>';			  
	    }
	    
      $count = $count + 1;
    }
	  return $option_list;
  }


  #----------------------------------------------------------------
  #--
  #--
  #---------------------------------------------------------------- 
  FUNCTION servicegroupservices_get_service_option_list($svg){
    $table_draw=''; 
    $result_set =array();
    $backendargs = array();
    $backendargs['servicegroup_name'] = $svg;
    $members = get_xml_servicegroup_member_objects($backendargs);
    $table_draw .= '<table class="gridtable">';
	  foreach($members as $member){
      foreach($member as $data_source){
	      foreach ($data_source as $key => $val){
	        $val = (array)$val;
	        $result_set = servicegroupservices_get_service_info($val['host_name'],$val['service_description']);
          if($result_set['table_header'] !== '<tr></tr>'){
            $table_draw .= '<tr><th rowspan="3">'.$val['host_name'].'-'.$val['service_description'].'</th></tr>';
	          $table_draw .= $result_set['table_header'];
	          $table_draw .= $result_set['table_data'];	 
	        }
        }
	    }			
    }
    $table_draw .= '</table>';
	  return $table_draw;
  }


  #----------------------------------------------------------------
  #--
  #--
  #----------------------------------------------------------------   
  FUNCTION servicegroupservices_get_service_info($host, $service_description){
    $table_header = '<tr>';
  	$table_data = '<tr>';
    $backendargs= array();
	  $backendargs['service_description'] = $service_description;
	  $backendargs['host_name']= $host;
	  $service_objects = get_xml_service_status($backendargs);
    foreach($service_objects as $service_element){
     	$service_element = (array) $service_element;
      if(count($service_element)>1){
        $format = explode("=",str_replace(' ','=',$service_element['performance_data']));
        #print_r($format);
        for($i=0;$i<count($format);$i++){
          if($format[$i]!==""){
            if((($i+1)%2) !== 0){
              $table_header .= "<th>";
              $table_header .= $format[$i];
              $table_header .= "</th>";
            }else{
              $table_data .= "<td>";
              $pos = strpos($format[$i],';');
              if($pos === false){
                if( $format[$i] === "" || $format[$i] === null){
                  $table_data .= '0';
                }else{
                  $table_data .= $format[$i];
                }  
              }else{
           	    $token = explode(';',$format[$i]);
           	    if($token[0]==="" || $token[0] === null ){
                  $table_data .= '0'; 
                }else{
                  $table_data .= $token[0];
                }

              }
              $table_data .= "</td>";
            }
          } else {
            $table_header .= "<th>No Current Data</th></tr><td>N/A</td>";
            }
        }	
      }
    }
    $table_header .= '</tr>';
    $table_data .= '</tr>';
    $result_set = array ('table_header' => $table_header,'table_data' => $table_data);
    return $result_set;  
  }

  
  #----------------------------------------------------------------
  #--
  #--
  #---------------------------------------------------------------- 
  FUNCTION servicegroupservices_refresh(){
    $request = $_REQUEST;
    $table_draw=''; 
    $result_set =array();
    $backendargs = array();
    $backendargs['servicegroup_name'] = $request['svg'];
    $members = get_xml_servicegroup_member_objects($backendargs);
    $table_draw .= '<table class="gridtable">';
    foreach($members as $member){
      foreach($member as $data_source){
        foreach ($data_source as $key => $val){
          $val = (array)$val;
          $result_set = servicegroupservices_get_service_info($val['host_name'],$val['service_description']);
          if($result_set['table_header'] !== '<tr></tr>'){
            $table_draw .= '<tr><th rowspan="3">'.$val['host_name'].'-'.$val['service_description'].'</th></tr>';
            $table_draw .= $result_set['table_header'];
            $table_draw .= $result_set['table_data'];  
          }
        }
      }     
    }
    $table_draw .= '</table>';
    return $table_draw;
  }
?>
