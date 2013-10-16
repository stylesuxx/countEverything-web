<?php
class Database{
  private $_dbh;

  function __construct($host, $db, $user, $pass){ 
    try {
      $this->_dbh = new PDO('mysql:host=' . $host . ';dbname=' . $db, $user, $pass);
      $this->create();
    } catch (PDOException $e) {
       print "Error!: " . $e->getMessage() . "<br/>";
       die();
    }
  }

	// Add item to the database
	function addItem($name, $amount, $id){
    $stmt = $this->_dbh->prepare('INSERT INTO item SET name = :name, amount = :amount, user_id = :id');
    $stmt->execute(array(':name' => strtolower($name), ':amount' => $amount, ':id' => $id));
	}

	// Get item count by name
	function getCount($name){
    $stmt = $this->_dbh->prepare('SELECT COUNT(*) FROM item WHERE name = :name');
    $stmt->execute(array(':name' => strtolower($name))); 
    return $stmt->fetchColumn();
	}

  // Get item amount by name
  function getAmount($name){
    $stmt = $this->_dbh->prepare('SELECT SUM(amount) FROM item WHERE name = :name');
    $stmt->execute(array(':name' => strtolower($name))); 
    return $stmt->fetchColumn();
  }

  // Get names, count and amount of each distinct item
  function getAllStats() {
    $stmt = $this->_dbh->prepare('SELECT name, SUM(amount) AS amount, COUNT(id) AS count FROM item GROUP BY name');
    $stmt->execute();
    $results = $stmt->fetchAll();

    return $results;
  }

  // Returns an array of all distinct item names 
  function getItemNames() {
    $stmt = $this->_dbh->prepare('SELECT name FROM item GROUP BY name');
    $stmt->execute();
    $results = $stmt->fetchAll();

    return $results;
  }

  // Retruns all distinct item names by a user
  function getUserItemNames($id) {
    $stmt = $this->_dbh->prepare('SELECT name FROM item WHERE user_id = :id GROUP BY name');
    $stmt->execute(array(':id' => strtolower($id))); 
    return $stmt->fetchAll();
  }

  // Retruns all distinct item names by a user
  function getUserItemSummary($id) {
    $stmt = $this->_dbh->prepare('SELECT name, SUM(amount) AS amount, COUNT(id) AS count FROM item WHERE user_id = :id GROUP BY name');
    $stmt->execute(array(':id' => strtolower($id))); 
    return $stmt->fetchAll();
  }

	// Get beverage count by name and timerange
  // TODO
	function getCountRange($name, $from, $to){
    $sql = '';
		return 'get count range';
	}

  // Add a new user to the database
  function addUser($token, $name) {
    $stmt = $this->_dbh->prepare('INSERT INTO user SET name = :name, token = :token');
    $stmt->execute(array(':name' => $name, ':token' => $token));
  }

  // Get a user ID by token
  function getUserId($token) {
    $stmt = $this->_dbh->prepare('SELECT id FROM user WHERE token = :token');
    $stmt->execute(array(':token' => $token));
    return $stmt->fetchColumn();
  }

  // Get a user name by token
  function getUserName($token) {
    $stmt = $this->_dbh->prepare('SELECT name FROM user WHERE token = :token');
    $stmt->execute(array(':token' => $token));
    return $stmt->fetchColumn();
  }

  // Check if a given api token is found in the user table
  function isValidToken($token) {
    $stmt = $this->_dbh->prepare('SELECT id FROM user WHERE token = :token');
    $stmt->execute(array(':token' => $token));
    if($stmt->rowCount() > 0){
      return True;
    }
    return False;
  }

  // Create the tables in the datbase if they do not exist yet.
  function create() {
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

    // Create beverage table
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