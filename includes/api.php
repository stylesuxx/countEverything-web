<?php
/**
 * This class is responsible for all API calls.
 */
class API {
  private $_db;

  /**
   * API Constructor.
   *
   * @param $db The database handler
   */
  function __construct($db) {
  	$this->_db = $db;
  }

  /**
   * Get Json object with statistics for all requested items.
   *
   * @param $items An array of items to look up
   * @param $id The user ID to look the items up for
   * @return A Json representation of the requestet items
   */
  public function getJson($items, $id) {
    $json = array();
    foreach ($items as $item) {
      $line = array('name' => $item, 'data' => array());
      $rows = $this->_db->getItemFromUser($item, $id);
      foreach ($rows as $row) {
        $timestamp = strtotime($row['date']);
        $line['data'][] = array($timestamp*1000, (float)$row['amount']);
      }
      $json[] = $line;
    }

    return json_encode($json);
  }

  /**
   * Render the items statistics with an html view
   *
   * @param $items An array of items to look up
   * @param $id The id to look the items up for
   * @return A HTML representation of the requested items
   */
  public function getHtml($items, $id) {
    $content = $this->getJson($items, $id);
    ob_start();
    include('templates/tables.tpl');
    $output = ob_get_clean();
    return $output;
  }

  /**
   * Checks if a given token is valid, aka if it exists in the database.
   * An empty token is never valid.
   *
   * @param $token The token to check the validity
   * @return The user id if it is a valid token or False otherwise
   */
  public function isValidToken($token) {
    if(empty($token)) return False;
    $user = $this->_db->getUser($token);
    if(count($user) > 0) return $user[0]['id'];
    return False;
  }

  /**
   * Get all items with all stats from all users from the database.
   *
   * @param $limit The maximum amount of items to return
   * @return Return all entries
   */
  public function getAllItems($limit) {
    $items = array();
    $names = $this->_db->getAllItemNames();
    foreach ($names as $key => $value) {
      if($limit-- == 0) break;
      $items[$value['name']] = $this->_db->getItemsFromAll($value['name']);
    }
    
    return $items;
  }
  
  /**
   * Get all items with all stats from all users for a specific time range.
   *
   * @param $start The start datetime
   * @param $end The end datetime
   * @return Return all matching entries
   */
  public function getAllItemsRange($start, $end) {
    // TODO
    
    return null;
  }
  
  /**
   * Get all items with all stats from a specific users for a specific time range.
   *
   * @param $user_id The user id to look the items up for
   * @param $start The start datetime
   * @param $end The end datetime
   * @return Return all matching entries
   */
  public function getUserItemsRange($user_id, $start, $end) {
    // TODO
    
    return null;
  }
}
?>
