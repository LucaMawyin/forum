<?php
  $host = 'localhost';
  $dbname = 'mawyinl_db';
  $username = 'root';
  $password = '';

  try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;port=3307", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
  }
?>
