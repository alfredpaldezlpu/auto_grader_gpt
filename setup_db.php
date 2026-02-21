<?php
require_once 'config.php';

try {
    // Connect without database first
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");

    // Grading sessions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `grading_sessions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_name` VARCHAR(255) NOT NULL,
        `folder_path` TEXT NOT NULL,
        `exam_instructions` LONGTEXT,
        `total_students` INT DEFAULT 0,
        `graded_count` INT DEFAULT 0,
        `status` ENUM('pending','processing','completed','error') DEFAULT 'pending',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Student submissions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `student_submissions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_id` INT NOT NULL,
        `student_name` VARCHAR(255) NOT NULL,
        `folder_name` VARCHAR(500) NOT NULL,
        `folder_path` TEXT NOT NULL,
        `part_a` DECIMAL(5,1) DEFAULT 0,
        `part_b` DECIMAL(5,1) DEFAULT 0,
        `part_c` DECIMAL(5,1) DEFAULT 0,
        `part_d` DECIMAL(5,1) DEFAULT 0,
        `part_e` DECIMAL(5,1) DEFAULT 0,
        `bonus` DECIMAL(5,1) DEFAULT 0,
        `total_score` DECIMAL(5,1) DEFAULT 0,
        `final_score` DECIMAL(5,1) DEFAULT 0,
        `percentage` DECIMAL(5,1) DEFAULT 0,
        `remarks` VARCHAR(50) DEFAULT '',
        `has_venv` TINYINT(1) DEFAULT 0,
        `venv_location` VARCHAR(500) DEFAULT '',
        `feedback` LONGTEXT,
        `code_files` LONGTEXT COMMENT 'JSON of collected code',
        `gpt_response` LONGTEXT COMMENT 'Raw GPT response',
        `status` ENUM('pending','grading','graded','error') DEFAULT 'pending',
        `error_message` TEXT,
        `graded_at` DATETIME,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`session_id`) REFERENCES `grading_sessions`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Exam criteria table  
    $pdo->exec("CREATE TABLE IF NOT EXISTS `exam_criteria` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_id` INT NOT NULL,
        `part_label` VARCHAR(20) NOT NULL,
        `part_name` VARCHAR(100) NOT NULL,
        `max_points` DECIMAL(5,1) NOT NULL,
        `description` TEXT,
        FOREIGN KEY (`session_id`) REFERENCES `grading_sessions`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    echo "Database and tables created successfully!";

} catch (PDOException $e) {
    die("Setup failed: " . $e->getMessage());
}
