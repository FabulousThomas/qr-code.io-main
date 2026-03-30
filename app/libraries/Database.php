<?php
/*
 * PDO Database Class
 * Connect to database
 * Bind values
 * Create prepared statements
 * Return rows and result
 */
class Database
{
   private $host = DB_HOST;
   private $user = DB_USER;
   private $pass = DB_PASS;
   private $dbname = DB_NAME;

   private $dbh;
   private $stmt;
   private $error;

   public function __construct()
   {
      // Set DSN (DATABASE STRING NAME)
      $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
      $options = array(
         PDO::ATTR_PERSISTENT => true,
         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_EMULATE_PREPARES => false,
      );

      // Create PDO instance
      try {
         $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
      } catch (PDOException $e) {
         $this->error = $e->getMessage();
         error_log('Database connection error: ' . $this->error);
         if ((getenv('APP_DEBUG') ?: '0') === '1') {
            // In debug, you may want to surface a generic message
            echo 'Database connection failed.';
         }
      }
   }

   public function query($sql)
   {
      $this->stmt = $this->dbh->prepare($sql);
   }

   // Bind Values
   public function bind($param, $value, $type = null)
   {
      if (is_null($type)) {
         switch (true) {
            case is_int($value):
               $type = PDO::PARAM_INT;
               break;
            case is_bool($value):
               $type = PDO::PARAM_BOOL;
               break;
            case is_null($value):
               $type = PDO::PARAM_NULL;
               break;
            default:
               $type = PDO::PARAM_STR;
         }
      }
      $this->stmt->bindValue($param, $value, $type);
   }
   // Execute the prepared statement
   public function execute()
   {
      return $this->stmt->execute();
   }

   // Get result set as array of object
   public function resultSet()
   {
      $this->execute();
      return $this->stmt->fetchAll(PDO::FETCH_OBJ);
   }

   // Get single set as object
   public function singleSet()
   {
      $this->execute();
      return $this->stmt->fetch(PDO::FETCH_OBJ);
   }

   // Get row count
   public function rowCount()
   {
      return $this->stmt->rowCount();
   }

   // Get last insert ID
   public function lastInsertId()
   {
      return $this->dbh->lastInsertId();
   }
}
