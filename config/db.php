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
  $pdoServer = new PDO("mysql:host={$DB_HOST};charset=utf8mb4", $DB_USER, $DB_PASS, $options);

  $safeDbName = str_replace('`', '', $DB_NAME);

  $pdoServer->exec("
    CREATE DATABASE IF NOT EXISTS `{$safeDbName}`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
  ");

  $pdo = new PDO("mysql:host={$DB_HOST};dbname={$safeDbName};charset=utf8mb4", $DB_USER, $DB_PASS, $options);

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
      UNIQUE KEY uniq_sub (faculty_id, semester_id, code),
      CONSTRAINT fk_subject_faculty
        FOREIGN KEY (faculty_id) REFERENCES faculties(id)
        ON DELETE CASCADE,
      CONSTRAINT fk_subject_semester
        FOREIGN KEY (semester_id) REFERENCES semesters(id)
        ON DELETE CASCADE
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
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_student_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
      CONSTRAINT fk_student_faculty
        FOREIGN KEY (faculty_id) REFERENCES faculties(id)
        ON DELETE RESTRICT,
      CONSTRAINT fk_student_semester
        FOREIGN KEY (semester_id) REFERENCES semesters(id)
        ON DELETE RESTRICT
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
      updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY uniq_grade (student_id, subject_id),
      CONSTRAINT fk_grade_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE,
      CONSTRAINT fk_grade_subject
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
        ON DELETE CASCADE
    ) ENGINE=InnoDB
  ");

  $facultyCount = (int)$pdo->query("SELECT COUNT(*) AS c FROM faculties")->fetch()['c'];
  if ($facultyCount === 0) {
    $pdo->exec("INSERT INTO faculties (name) VALUES ('BSc Computing'),('BBA'),('BSc IT')");
  }

  $semCount = (int)$pdo->query("SELECT COUNT(*) AS c FROM semesters")->fetch()['c'];
  if ($semCount === 0) {
    $pdo->exec("
      INSERT INTO semesters (name) VALUES
      ('Semester 1'),('Semester 2'),('Semester 3'),
      ('Semester 4'),('Semester 5'),('Semester 6')
    ");
  }

  $subCount = (int)$pdo->query("SELECT COUNT(*) AS c FROM subjects")->fetch()['c'];
  if ($subCount === 0) {

  $faculties = $pdo->query("SELECT id, name FROM faculties")->fetchAll();
  $semesters = $pdo->query("SELECT id, name FROM semesters")->fetchAll();

  $stmt = $pdo->prepare("
    INSERT INTO subjects (faculty_id, semester_id, code, title)
    VALUES (?,?,?,?)
  ");

  foreach ($faculties as $f) {

    foreach ($semesters as $s) {

      $fid = (int)$f['id'];
      $sid = (int)$s['id'];

      if ($f['name'] === 'BSc IT' && $s['name'] === 'Semester 6') {
        $stmt->execute([$fid,$sid,'IT601','Cloud Computing']);
        $stmt->execute([$fid,$sid,'IT602','Cyber Security Management']);
        $stmt->execute([$fid,$sid,'IT603','Final Year Project']);
      }

      if ($f['name'] === 'BBA' && $s['name'] === 'Semester 1') {
        $stmt->execute([$fid,$sid,'BBA101','Principles of Management']);
        $stmt->execute([$fid,$sid,'BBA102','Business Communication']);
      }

      if ($f['name'] === 'BSc Computing' && $s['name'] === 'Semester 1') {
        $stmt->execute([$fid,$sid,'CS101','Programming Fundamentals']);
        $stmt->execute([$fid,$sid,'CS102','Computer Systems']);
      }

    }
  }
}

  $adminExists = (int)$pdo->query("SELECT COUNT(*) AS c FROM users WHERE role='admin'")->fetch()['c'];
  if ($adminExists === 0) {
    $hash = password_hash('Admin@123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name,email,password_hash,role) VALUES (?,?,?,?)");
    $stmt->execute(['System Admin', 'admin@portal.com', $hash, 'admin']);
  }

} catch (PDOException $e) {
  http_response_code(500);
  echo "Database initialization failed.";
  exit;
}
?>