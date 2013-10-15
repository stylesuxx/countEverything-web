<?php
include 'includes/settings.php';
include 'includes/database.php';
$db = new Database($db_host, $db_name, $db_user, $db_pass);

function returnData() {

}

/**
 * Handle API requests and return JSON and set according http status code.
 * 
 * 400 when invalid action
 * 401 when token not valid
 * 200 when successfull request
 *
 * test a connection: ?action=test&token=asdf_1234
 * add a beverage: ?action=add&token=asdf_1234&beverage=beer&amount=0.5
 */
if(isset($_GET['action']) && isset($_GET['token'])){
  $action = $_GET['action'];
  $token = $_GET['token'];
  if($db->isValidToken($token)){
    $user = $db->getUserId($token);
    switch($action){
      case 'add':{
        if(isset($_GET['beverage']) && isset($_GET['amount']) && is_numeric($_GET['amount'])){
          $db->addItem($_GET['beverage'], $_GET['amount'], $user);
          header('HTTP/1.1 200 OK');
          echo json_encode(array(
          	'http' => 200,
          	'message' => 'Added beverage',
          	));
          exit;
        }
        else{
          header('HTTP/1.1 400 Bad Request');
          exit;
        }
      } break;
      case 'test':{
        header('HTTP/1.1 200 OK');
	    echo json_encode(array(
	    	'http' => 200,
	        'message' => 'Everything set up',
	        ));
	    exit;
      } break;
    }
  }
  else{
    header('HTTP/1.1 401 Unauthorized');
    exit;
  }
}
?>