<?php
include 'includes/settings.php';
include 'includes/database.php';
include 'includes/api.php';
$db = new Database($db_host, $db_name, $db_user, $db_pass);
$api = new API($db);

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
    if($user_id = $api->isValidToken($token)) {

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

/**
 * Proccess a requested action.
 *
 * @param request The reques JSON
 * @param The requesting users id
 */
function processAction($request, $user_id) {
  GLOBAL $db, $api;

  $action = $request->action;
  switch($action){
    
    // For testing the settings. If we make it till hear, a correct
    // token and the test action were provided.
    case 'test': {
      renderOK(json_encode(array('message' => 'OK')));
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
      $all = false;
      
      // If items where provided query for them
      if(property_exists($request, 'items') && is_array($request->items)){
        $items = $request->items;
      }

      // If no items were provided get statistics for all items
      else{
        $all = true;
      }

      // Return request depending on format
      $format = (property_exists($request, 'format'))? $request->format : 'json';
      switch($format) {
        // FORMAT: HTML
        case 'html': {
          $content = $api->getAllStatsByUser($user_id);
          ob_start();
          include('includes/templates/tables.tpl');
          $html = ob_get_clean();

          renderOK($html);
        } break;
          
        // FORMAT: JSON
        case 'json':
        default: {
          if($all) $json = $api->getAllStatsByUser($user_id);
          else $json = "nope";
          renderOK($json);
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
