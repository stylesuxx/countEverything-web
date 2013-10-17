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
   */
  public function renderItems($items, $id) {
    $this->render($this->getJson($items, $id), 'tables');
  }

  /**
   * Render a html page.
   *
   * @param $json The content to render
   */
  function render($json, $template) {
  	header('HTTP/1.1 200 OK');
    $content = $json;
    include 'templates/' . $template . '.tpl';
  }
}
?>