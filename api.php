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
 * Multiple items may be requested at once if they are delimited with a comma(,).
 *
 * html - all time      ?action=get&token=asdf_1234&item=beer
 * json - all time      ?action=get&token=asdf_1234&item=beer&format=json
 * html - time range    ?action=get&token=asdf_1234&item=beer&from=timestamp&to=timestamp
 * json - time range    ?action=get&token=asdf_1234&item=beer&format=json&from=timestamp&to=timestamp
 */
if(isset($_GET['action']) && isset($_GET['token'])){
  $action = $_GET['action'];
  $token = $_GET['token'];

  // The token has to be valid before any action may be performed
  if($db->isValidToken($token)){
    $user_id = $db->getUserId($token);
    switch($action){
      // ACTION: Get statistics
      case 'get': {
        $format = "html";
        if(isset($_GET['format'])){
          $format = $_GET['format'];
        }
        if(isset($_GET['item'])){
          $items = explode(',', $_GET['item']);
        }
        else {
          $items = array();
          $names = $db->getItemNamesByUser($user_id);
          foreach ($names as $key => $value) {
            array_push($items, $value['name']);
          }
        }
        switch($format) {
          // FORMAT: JSON
          case 'json': {
            $json = $api->getJson($items, $user_id);
            renderOK($json);
          } break;
          
          // FORMAT: HTML
          default: {
            $html = $api->getHtml($items, $user_id);
            renderOK($html);
          } break;
        }
      } break;

      // ACTION: Add an item to the database
      case 'add': {
        if(isset($_GET['item']) && isset($_GET['amount']) && is_numeric($_GET['amount'])){
          $db->addItem($_GET['item'], $_GET['amount'], $user_id);
          $json = json_encode(array(
            'http' => 200,
            'message' => 'Counted '. $_GET['amount']. ' ' . $_GET['item'],
          ));
          renderOK($json);
        }
        else{
          renderError(header('HTTP/1.1 400 Bad Request'));
        }
      } break;

      // ACTION: Test the settings
      case 'test': {
        renderOK(json_encode(array('message' => 'Ready to go.')));
      } break;

      // ACTION: Default - Bad Request
      default: {
          renderError(header('HTTP/1.1 400 Bad Request'));
      } break;
    }
  }

  // Token is not valid
  else{
    renderError('HTTP/1.1 401 Unauthorized');
  }
}

/**
 * Render content.
 *
 * @param $content The content to render
 */
function renderOK($content) {
  header('HTTP/1.1 200 OK');
  echo $content;
  exit;
}

/**
 * Send an error response.
 */
function renderError($header) {
  header($header);
  exit;
}
?>
