<?php
include 'includes/settings.php';
include 'includes/database.php';
include 'includes/api.php';
$db = new Database($db_host, $db_name, $db_user, $db_pass);
$api = new API($db);

/**
 * Handle API requests and return JSON and set according http status code.
 *
 * 400 when invalid action
 * 401 when token not valid
 * 200 when successfull request
 *
 * Test a connection:   ?action=test&token=asdf_1234
 * Add an item:         ?action=add&token=asdf_1234&item=beer&amount=0.5
 *
 * Request statistics:
 * If requesting a time range, the to parameter may be omitted to get all items
 * from from timestamp till current date.
 *
 * Multiple items may be requested at once if they are provided as a list.
 *
 * html - all time      ?action=get&token=asdf_1234&item=beer
 * json - all time      ?action=get&token=asdf_1234&item=beer&format=json
 * html - time range    ?action=get&token=asdf_1234&item=beer&from=timestamp&to=timestamp
 * json - time range    ?action=get&token=asdf_1234&item=beer&format=json&from=timestamp&to=timestamp
 */
if(isset($_GET['action']) && isset($_GET['token'])){
  $action = $_GET['action'];
  $token = $_GET['token'];
  if($db->isValidToken($token)){
    $user_id = $db->getUserId($token);
    switch($action){
      // ACTION: Get statistics
      case 'get': {
        $format = "html";
        if(isset($_GET['format'])){
          $format = $_GET['format'];
        }
        switch($format) {
          case 'json': {
            $json = $api->getJson(array($_GET['item'], 'beer', 'foo'), $user_id);
            header('HTTP/1.1 200 OK');
            echo $json;
            exit;
          } break;
          default: {
            $html = $api->getHtml(array($_GET['item'], 'beer', 'foo'), $user_id);
            header('HTTP/1.1 200 OK');
            echo $html;
            exit;
          } break;
        }
      } break;

      // ACTION: Add an item to the database
      case 'add': {
        if(isset($_GET['item']) && isset($_GET['amount']) && is_numeric($_GET['amount'])){
          $db->addItem($_GET['item'], $_GET['amount'], $user_id);
          header('HTTP/1.1 200 OK');
          echo json_encode(array(
            'http' => 200,
            'message' => 'Counted '. $_GET['amount']. ' ' . $_GET['item'],
          ));
          exit;
        }
        else{
          header('HTTP/1.1 400 Bad Request');
          exit;
        }
      } break;

      // ACTION: Test the settings
      case 'test': {
        header('HTTP/1.1 200 OK');
        echo json_encode(array(
          'http' => 200,
          'message' => 'Ready to go.',
        ));
        exit;
      } break;
    }
  }

  // Token is not valid
  else{
    header('HTTP/1.1 401 Unauthorized');
    exit;
  }
}
?>
