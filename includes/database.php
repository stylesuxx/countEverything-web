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

	// Writes a record to the database
	function write($name){
    $stmt = $this->_dbh->prepare('INSERT INTO beverage SET name = :name');
		$stmt->execute(array(':name' => $name));
	}

	// Get beverage count by name
	function getCount($name){
    $stmt = $this->_dbh->prepare('SELECT COUNT(*) FROM beverage WHERE name = :name');
    $stmt->execute(array(':name' => $name)); 
    return $stmt->fetchColumn();
	}

	// Get beverage count by name and timerange
  // TODO
	function getCountRange($name, $from, $to){
    $sql = '';
		return 'get count range';
	}

  // Check if a given api token can be found in the user table
  function validToken($token) {
    return False;
  }

  // Add a new user to the database
  function addUser($token, $name) {
    return False;
  }

  // Create the tables in the datbase if they do not exist yet.
  function create() {
    // Create beverage table
    $sql = 'CREATE TABLE IF NOT EXISTS beverage (
      id int(11) NOT NULL AUTO_INCREMENT,
      name varchar(40) NOT NULL,
      amount decimal(11) NOT NULL,
      added timestamp,
      UNIQUE KEY id (id)
    )
    CHARACTER SET utf8 COLLATE utf8_general_ci';
    $this->_dbh->exec($sql);

    // Generate user table
    $sql = 'CREATE TABLE IF NOT EXISTS user (
      id int(11) NOT NULL AUTO_INCREMENT,
      token varchar(40) NOT NULL,
      name varchar(40) NOT NULL,
      added timestamp,
      UNIQUE KEY token (token),
      PRIMARY KEY id (id)
    )
    CHARACTER SET utf8 COLLATE utf8_general_ci';
    $this->_dbh->exec($sql);
  } 
}
?>