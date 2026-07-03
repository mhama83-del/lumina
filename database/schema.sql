-- ============================================================
-- LUMINA — Database schema (Fasa 1)  ·  MySQL / MariaDB
-- Run once: mysql -u USER -p DBNAME < schema.sql
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120),
  role ENUM('student','university','faculty','employer','alumni','admin') NOT NULL,
  ref_id INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120), age INT, stage VARCHAR(10),
  university VARCHAR(80), faculty VARCHAR(80), programme VARCHAR(120),
  cgpa DECIMAL(3,2) NULL, target_domain VARCHAR(60),
  work_animal VARCHAR(40) NULL, evidence_text TEXT,
  has_resume TINYINT DEFAULT 0, salary_target INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) UNIQUE, label VARCHAR(80), domain VARCHAR(60),
  high_value TINYINT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS student_skills (
  student_id INT, skill_id INT,
  source ENUM('stated','inferred') DEFAULT 'stated',
  confidence DECIMAL(3,2) DEFAULT 1.00,
  PRIMARY KEY (student_id, skill_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120), domain VARCHAR(60), company VARCHAR(120),
  location VARCHAR(60), salary_band VARCHAR(40), high_income TINYINT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_skills (
  role_id INT, skill_id INT, required TINYINT DEFAULT 1,
  PRIMARY KEY (role_id, skill_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evidence (
  id INT AUTO_INCREMENT PRIMARY KEY, student_id INT,
  type ENUM('mycsd','project','internship','leadership','community','innovation','cert'),
  title VARCHAR(160), verified TINYINT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS certifications (
  id INT AUTO_INCREMENT PRIMARY KEY, student_id INT,
  name VARCHAR(160), issuer VARCHAR(120), verified TINYINT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS opportunities (
  id INT AUTO_INCREMENT PRIMARY KEY, role_id INT,
  kind ENUM('job','internship','project') DEFAULT 'job',
  status VARCHAR(30) DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS matches (
  id INT AUTO_INCREMENT PRIMARY KEY, student_id INT, role_id INT,
  match_score INT, fit_label VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cohort_metrics (
  id INT AUTO_INCREMENT PRIMARY KEY, university VARCHAR(80), faculty VARCHAR(80),
  metric VARCHAR(60), value DECIMAL(6,2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employer_feedback (
  id INT AUTO_INCREMENT PRIMARY KEY, employer VARCHAR(120),
  university VARCHAR(80), satisfaction INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS student_scores (
  student_id INT PRIMARY KEY,
  readiness INT, gap_pct INT, industry_exposure INT, risk_level VARCHAR(20),
  high_income INT, job_creator INT, outcomes_index INT, updated_at DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
