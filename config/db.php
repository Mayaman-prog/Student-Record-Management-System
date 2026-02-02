<?php
declare(strict_types=1);

$DB_HOST = 'localhost';
$DB_NAME = 'Student_Record_Management_System';
$DB_USER = 'root';
$DB_PASS = '';

$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {

  $pdo = new PDO(
    "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    $options
  );

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS faculties (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(120) NOT NULL UNIQUE
    ) ENGINE=InnoDB
  ");

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS semesters (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(60) NOT NULL UNIQUE
    ) ENGINE=InnoDB
  ");

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      full_name VARCHAR(120) NOT NULL,
      email VARCHAR(160) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      role ENUM('admin','staff','student') NOT NULL,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
  ");

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS subjects (
      id INT AUTO_INCREMENT PRIMARY KEY,
      faculty_id INT NOT NULL,
      semester_id INT NOT NULL,
      code VARCHAR(30) NOT NULL,
      title VARCHAR(160) NOT NULL,
      UNIQUE KEY uniq_sub (faculty_id, semester_id, code)
    ) ENGINE=InnoDB
  ");

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS students (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL UNIQUE,
      student_uid VARCHAR(40) NOT NULL UNIQUE,
      faculty_id INT NOT NULL,
      semester_id INT NOT NULL,
      attendance_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
  ");

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS grades (
      id INT AUTO_INCREMENT PRIMARY KEY,
      student_id INT NOT NULL,
      subject_id INT NOT NULL,
      marks DECIMAL(5,2) NOT NULL DEFAULT 0.00,
      grade_letter ENUM('A','B','C','D','E','F') NOT NULL DEFAULT 'F',
      remarks VARCHAR(255) NULL,
      UNIQUE KEY uniq_grade (student_id, subject_id)
    ) ENGINE=InnoDB
  ");

} catch (PDOException $e) {
  echo "<pre>";
  die("Database Error: " . $e->getMessage());
}
?>
