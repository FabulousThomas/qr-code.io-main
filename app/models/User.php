<?php
/*
 * User model
 * Creates Database instance
 * Handles users details
 * Register users
 * Inserts into users table
 */
class User
{
   private $db;

   public function __construct()
   {
      $this->db = new Database;
   }

   // Users register
   public function register($data)
   {
      $this->db->query('INSERT INTO users (name, username, email, password) VALUES (:name, :username, :email, :password)');
      // Bind values
      $this->db->bind(':name', $data['name']);
      $this->db->bind(':username', $data['username']);
      $this->db->bind(':email', $data['email']);
      $rawPassword = $data['password'] ?? '';
      $info = is_string($rawPassword) ? password_get_info($rawPassword) : ['algo' => 0];
      $hashed = ($info['algo'] ?? 0) ? $rawPassword : password_hash((string) $rawPassword, PASSWORD_DEFAULT);
      $this->db->bind(':password', $hashed);

      // Execute query
      if ($this->db->execute()) {
         return true;
      } else {
         return false;
      }
   }

   // Users login
   public function login($email)
   {
      $this->db->query('SELECT * FROM users WHERE email = :email');
      $this->db->bind(':email', $email);

      $row = $this->db->singleSet();

      if ($this->db->rowCount() > 0 && $row) {
         return $row;
      } else {
         return false;
      }
   }

   // Check for email
   public function checkEmail($email)
   {
      $this->db->query('SELECT * FROM users WHERE email = :email');
      $this->db->bind(':email', $email);
      $this->db->singleSet();

      if ($this->db->rowCount() > 0) {
         return true;
      } else {
         return false;
      }
   }

   public function getUserByEmail($email)
   {
      $this->db->query('SELECT * FROM users WHERE email = :email');
      $this->db->bind(':email', $email);
      $row = $this->db->singleSet();

      if ($this->db->rowCount() > 0) {
         return $row;
      }

      return false;
   }

   public function findOrCreateByEmail($email)
   {
      if (empty($email)) {
         return false;
      }

      $existing = $this->getUserByEmail($email);
      if ($existing) {
         return $existing;
      }

      $this->db->query('INSERT INTO users (name, username, email, password) VALUES (:name, :username, :email, :password)');
      $this->db->bind(':name', $email);
      $this->db->bind(':username', $email);
      $this->db->bind(':email', $email);
      $random = bin2hex(random_bytes(16));
      $this->db->bind(':password', password_hash($random, PASSWORD_DEFAULT));

      if ($this->db->execute()) {
         return $this->getUserByEmail($email);
      }

      return false;
   }

   // Check for username
   public function checkUsername($username)
   {
      $this->db->query('SELECT * FROM users WHERE username = :username');
      $this->db->bind(':username', $username);
      $this->db->singleSet();

      if ($this->db->rowCount() > 0) {
         return true;
      } else {
         return false;
      }
   }

   public function getUserById($id)
   {
      $this->db->query('SELECT * FROM users WHERE id = :id');
      $this->db->bind(':id', $id);

      $row = $this->db->singleSet();

      return $row;
   }

   // Check for Password
   public function checkPassword($password)
   {
      // Deprecated: hashed passwords cannot be checked with direct equality.
      // Use verifyPassword($email, $password) instead.
      return false;
   }

   public function verifyPassword($email, $password)
   {
      $user = $this->getUserByEmail($email);
      if (!$user || !isset($user->password)) {
         return false;
      }
      return password_verify((string)$password, (string)$user->password);
   }
}
