<?php
/**
 * This class is responsible for all API calls.
 *
 * All getters return a JSON representation of the query.
 */
class API {
  private $_db;
  private $_format;

  /**
   * API Constructor.
   *
   * @param $db The database handler
   */
  function __construct($db) {
  	$this->_db = $db;
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
   * Get all statistics for all users.
   *
   * @return JSON representation of all ever counted items
   */
  public function getAllStats() {
    $items = $this->_db->getAll();

    return $this->formatStats($items);
  }

  public function getAllStatsByRange($start, $end) {

  }

  /**
   * Get all statistics for a specific user.
   *
   * @param $user_id The users id to look the items up for
   * @return JSON representation of all counted items
   */
  public function getAllStatsByUser($user_id) {
    $items = $this->_db->getAllByUser($user_id);

    return $this->formatStats($items);
  }

  public function getAllstatsByUserByRange($start, $end) {

  }

  /**
   * Format items for statistical representation.
   *
   * @return JSON representation of items
   */
  private function formatStats($items) {
    $rows = array();
    foreach($items as $item){
      $name = $item['name'];
      $amount = (float)$item['amount'];
      $timestamp = strtotime($item['date']) * 1000;

      if(!array_key_exists($name , $rows)){
        $rows[$name] = array('name' => $name, 'data' => array());
      }

      $rows[$name]['data'][] = array($timestamp, $amount);
    }

    $json = array();
    foreach ($rows as $key => $value) {
      $json[] = $value;
    }

    return json_encode($json);
  }

  
  /**
   * Get all items with all stats from all users for a specific time range.
   *
   * @param $start The start datetime
   * @param $end The end datetime
   * @return Return all matching entries
   */
  public function getItemsRange($start, $end) {
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
  public function getItemsByUserRange($user_id, $start, $end) {
    // TODO
    
    return null;
  }
}
?>
