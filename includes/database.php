<?php
/**
 * This is the database adapter.
 * It tries to connect to the database. If the tables are not present in the
 * database but the database does exist, the tables are created automatically.
 *
 * All getter methods return raw rows from the database.
 * All add methods return the id of the last inserted row or 0 if no row
 * was inserted.
 * The sql statements are prepared statements to prevent 1st and 2nd order SQL
 * injection.
 */
class Database{
  private $_dbh;

  /**
   * Database constructor. Tries to connect to the database or dies trying hard.
   *
   * @param $host The database host
   * @param $db The database name
   * @param $user The database user
   * @param $pass The database pass
   */
  function __construct($host, $db, $user, $pass){
    try {
      $this->_dbh = new PDO('mysql:host='.$host.';dbname='.$db, $user, $pass);
      $this->create();
    } catch (PDOException $e) {
       print "Error!: " . $e->getMessage() . "<br/>";
       die();
    }
  }

  /**
   * Inserts an item, amount and submitting users ID to the database.
   *
   * @param $name The items name
   * @param $amount The items amount
   * @param $user_id The id of the submitting user
   * @return The id of the added item
   */
  public function addItem($name, $amount, $user_id){
    $stmt = $this->_dbh->prepare(
      'INSERT INTO item
       SET name = :name, amount = :amount, user_id = :id'
    );
    $stmt->execute(array(
      ':name' => strtolower($name),
      ':amount' => $amount,
      ':id' => $user_id
    ));

    return $this->_dbh->lastInsertId();
  }

  /**
   * Add a new user to the database.
   * The token has to be unique amongst all users.
   *
   * @param $token The new users token
   * @param $name The new users name
   * @return The id of the added user
   */
  public function addUser($token, $name) {
    $stmt = $this->_dbh->prepare(
      'INSERT INTO user 
       SET name = :name, token = :token'
    );
    $stmt->execute(array(
      ':name' => $name,
      ':token' => $token
    ));

    return $this->_dbh->lastInsertId();
  }

  /**
   * Get a user id by token.
   * Token must be valid.
   *
   * @param $token The token to get the id for
   * @return A users id
   */
  public function getUserId($token) {
    $stmt = $this->_dbh->prepare('SELECT id FROM user WHERE token = :token');
    $stmt->execute(array(':token' => $token));
    return $stmt->fetchColumn();
  }

  /**
   * Get a user name by token.
   * Token must be valid.
   *
   * @param $token The token to get the name for
   * @return A users name
   */
  public function getUserName($token) {
    $stmt = $this->_dbh->prepare('SELECT name FROM user WHERE token = :token');
    $stmt->execute(array(':token' => $token));
    return $stmt->fetchColumn();
  }

  // Get beverage count by name and timerange
  // TODO
  public function getCountRange($name, $from, $to){
    $sql = '';
    return 'get count range';
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
    $stmt = $this->_dbh->prepare('SELECT id FROM user WHERE token = :token');
    $stmt->execute(array(':token' => $token));
    if($stmt->rowCount() > 0) return True;
    return False;
  }

  /**
   * Get the amount of distinct users that have countet at least one item.
   *
   * @return Amount of distinct users
   */
  public function getDistinctUsers(){
    $stmt = $this->_dbh->prepare('SELECT COUNT(*) FROM item GRPUP BY user_id');
    $stmt->execute();
    return $stmt->fetchColumn();
  }

  /**
   * Get the amount of distinct users that have countet a specific item.
   *
   * @return Amount of distinct users
   */
  public function getDistinctUsersForItem($name){
    $stmt = $this->_dbh->prepare('SELECT COUNT(*) FROM item GRPUP BY user_id WHERE item = :name');
    $stmt->execute(array(':name' => strtolower($name)));
    return $stmt->fetchColumn();
  }

  /**
   * Get the total amount of a specific item.
   *
   * @return Amount of items
   */
  public function getTotalAmount($name){
    $stmt = $this->_dbh->prepare('SELECT SUM(amount) FROM item WHERE name = :name');
    $stmt->execute(array(':name' => strtolower($name)));
    return $stmt->fetchColumn();
  }

  // Get names, count and amount of each distinct item
  public function getAllItemStats() {
    $stmt = $this->_dbh->prepare('SELECT name, SUM(amount) AS amount, COUNT(id) AS count FROM item GROUP BY name');
    $stmt->execute();
    $results = $stmt->fetchAll();

    return $results;
  }

  /**
   * Get all distinct item names available in the database.
   *
   * @return Item names
   */
  public function getAllItemNames() {
    $stmt = $this->_dbh->prepare(
      'SELECT name, sum(amount) as amount 
       FROM item 
       GROUP BY name 
       ORDER BY amount DESC');
    $stmt->execute();
    $results = $stmt->fetchAll();

    return $results;
  }

  /**
   * Return all distinct item names of a specific user.
   *
   * @param $id The users id
   * @return Item names
   */
  public function getItemNamesByUser($id) {
    $stmt = $this->_dbh->prepare(
      'SELECT name, sum(amount) as amount
       FROM item 
       WHERE user_id = :id 
       GROUP BY name
       ORDER BY amount DESC');
    $stmt->execute(array(':id' => strtolower($id)));
    return $stmt->fetchAll();
  }

  /**
   * Get all entries of a specific item by a specific user
   * 
   * @param $item The item to look up
   * @param $id The user id to look the items up
   * @return All matched rows
   */
  public function getItems($item, $id) {
    $stmt = $this->_dbh->prepare(
      'SELECT sum(amount) AS amount, DATE(added) AS date  
       FROM item 
       WHERE user_id = :id AND name = :item
       GROUP BY DATE(added)
       ORDER BY date');
    $stmt->execute(array(':item' => strtolower($item), ':id' => $id));
    return $stmt->fetchAll();
  }
  
  /**
   * Get all entries of a specific item
   * 
   * @param $item The item to look up
   * @return All matched rows
   */
  public function getItemsFromAll($item) {
    $stmt = $this->_dbh->prepare(
      'SELECT name, sum(amount) AS amount, DATE(added) AS date  
       FROM item 
       WHERE name = :item
       GROUP BY DATE(added)
       ORDER BY date');
    $stmt->execute(array(':item' => strtolower($item)));
    return $stmt->fetchAll();
  }

  /**
   * Get all stats from the database
   */
  public function getAllItems($limit) {
    $items = array();
    $names = $this->getAllItemNames();
    foreach ($names as $key => $value) {
      if($limit-- == 0) break;
      $items[$value['name']] = $this->getItemsFromAll($value['name']);
    }
    
    return $items;
  }

  /**
   * Creates all the needed database tables if they have not been created yet.
   */
  private function create() {
    // Create user table
    $sql = 'CREATE TABLE IF NOT EXISTS user (
      id int(11) NOT NULL AUTO_INCREMENT,
      token varchar(40) NOT NULL,
      name varchar(40) NOT NULL,
      added timestamp,
      UNIQUE KEY token (token),
      UNIQUE KEY id (id),
      PRIMARY KEY id (id)
    )
    CHARACTER SET utf8 COLLATE utf8_general_ci';
    $this->_dbh->exec($sql);

    // Create item table
    $sql = 'CREATE TABLE IF NOT EXISTS item (
      id int(11) NOT NULL AUTO_INCREMENT,
      user_id int(11) NOT NULL,
      name varchar(40) NOT NULL,
      amount decimal(10, 5) NOT NULL,
      added timestamp,
      UNIQUE KEY id (id),
      FOREIGN KEY (user_id) REFERENCES user(id),
      PRIMARY KEY id (id)
    )
    CHARACTER SET utf8 COLLATE utf8_general_ci';
    $this->_dbh->exec($sql);
  }
}
?>
