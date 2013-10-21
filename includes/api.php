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
      $rows = $this->_db->getItems($item, $id);
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
   * @return True or False
   */
  public function isValidToken($token) {
    if(empty($token)) return False;
    $user = $this->_db->getUser($token);
    if(count($user) > 0) return True;
    return False;
  }
}
?>