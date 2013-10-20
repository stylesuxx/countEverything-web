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

/**
 * The API expects a JSON with at least a valid token and an action.
 * Depending on the action other paramaters have to be provided as well.
 *
 * Error Codes:
 * 400: No action specified or missing parameters
 * 401: No token or invalid token provided
 * 405: Requested action is not implemented
 *
 * Test client setup:
 * ?json=[{"token":"asdfgh","action":"test"}]
 *
 * Add an item with a specific amount:
 * ?json=[{"token":"asdfgh","action":"add","item":"foo","amount":1.2}]
 *
 * Request statisitics for all items:
 * ?json=[{"token":"asdfgh","action":"query"}]
 *
 * Request statisitics for multiple items:
 * ?json=[{"token":"asdfgh","action":"query","items":["item_1","item_2","item_3"]}]
 *
 */
if(isset($_GET['json'])){
  $request = json_decode($_GET['json'])[0];

  // Each requests needs a token
  if(property_exists($request, 'token')){
    $token = $request->token;

    // the token has to be valid
    if($db->isValidToken($token)) {
      $user_id = $db->getUserId($token);

      // Each request also needs an action
      if(property_exists($request, 'action')){
        processAction($request, $user_id);
      }

      // No action provided
      else{
        renderError(header('HTTP/1.1 400 Bad Request'));
      }
    }

    // Token invalid
    else{
      renderError('HTTP/1.1 401 Unauthorized');
    }
  }

  // No token provided
  else{
    renderError('HTTP/1.1 401 Unauthorized');
  }
}

else if(isset($_GET['action']) && isset($_GET['token'])){
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
 * Proccess a requested action
 */
function processAction($request, $user_id) {
  GLOBAL $db, $api;

  $action = $request->action;
  switch($action){
    
    // For testing the settings. If we make it till hear, a correct
    // token and the test action were provided.
    case 'test': {
      renderOK("");
    } break;

    // Add an item to the database when item is set and amount is numeric.
    // Otherwise return bad request error.
    case 'add': {
      if(property_exists($request, 'item') && property_exists($request, 'amount') && is_numeric($request->amount)){
        $db->addItem($request->item, $request->amount, $user_id);
        $json = json_encode(array('message' => 'Counted '. $request->amount . ' ' . $request->item));
        renderOK($json);
      }
      else{
        renderError('HTTP/1.1 400 Bad Request');
      }
    } break;

    // Query the database for statistics
    case 'query': {
      $items = array();
      
      // If items where provided query for them
      if(property_exists($request, 'items') && is_array($request->items)){
        $items = $request->items;
      }

      // If no items were provided get statistics for all items
      else{
        $names = $db->getItemNamesByUser($user_id);
        foreach ($names as $key => $value) {
          array_push($items, $value['name']);
        }
      }

      // Return request depending on format
      $format = (property_exists($request, 'format'))? $request->format : 'html';
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
      // TODO: Check for time range, if no time range provided check all time
    }
          
    // Action not implemented
    default: {
      renderError('HTTP/1.1 405 Method Not Allowed');    
    }
  }
}

/**
 * Render content.
 *
 * @param $content The html content to render
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
