<?php


require_once(dirname(__FILE__) . '/../dashlethelper.inc.php');


// initialization stuff
pre_init();

// start session
init_session();

// grab GET or POST variables 
grab_request_vars(false,'post');

// check prereqs
check_prereqs();

// check authentication
check_authentication(false);

header('Content-Type: text/html');
echo servicegroupservices_refresh();

?>