-- =====================================================================
-- Lumina Graph v2 — DATABASE UPGRADE (Fasa 0)
-- ADDITIVE ONLY. Tiada DROP/ALTER pada table sedia ada.
-- Selamat dijalankan berulang kali (IF NOT EXISTS).
-- Letak fail ini di: app_ci4/database/upgrade_v2.sql
-- Jalankan: mysql -h HOST -u USER -p DBNAME < app_ci4/database/upgrade_v2.sql
-- =====================================================================

-- 1) Simpanan hasil analisis resume / no-resume
CREATE TABLE IF NOT EXISTS resume_analyses (
  id                 INT AUTO_INCREMENT PRIMARY KEY,
  session_key        VARCHAR(64)  NULL,
  source             ENUM('resume','no_resume') NOT NULL DEFAULT 'resume',
  name               VARCHAR(120) NULL,
  raw_text           MEDIUMTEXT   NULL,
  target_domain      VARCHAR(40)  NULL,
  career_cluster     VARCHAR(60)  NULL,
  readiness          INT          NULL,
  employability_band VARCHAR(20)  NULL,
  animal_primary     VARCHAR(20)  NULL,
  animal_secondary   VARCHAR(20)  NULL,
  animal_growth      VARCHAR(20)  NULL,
  skills_json        JSON         NULL,
  projects_json      JSON         NULL,
  leadership_json    JSON         NULL,
  feedback_json      JSON         NULL,
  next_action        VARCHAR(255) NULL,
  created_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_session (session_key),
  INDEX idx_domain  (target_domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Profil calon yang dipersist (boleh disambung ke Compass/Match/Employer)
CREATE TABLE IF NOT EXISTS candidate_profiles (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  analysis_id   INT          NULL,
  session_key   VARCHAR(64)  NULL,
  name          VARCHAR(120) NULL,
  stage         VARCHAR(12)  NULL,
  programme     VARCHAR(120) NULL,
  cgpa          DECIMAL(3,2) NULL,
  target_domain VARCHAR(40)  NULL,
  animal        VARCHAR(20)  NULL,
  verified      TINYINT      NOT NULL DEFAULT 0,
  evidence_text MEDIUMTEXT   NULL,
  skills_json   JSON         NULL,
  readiness     INT          NULL,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_session (session_key),
  INDEX idx_domain  (target_domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Padanan calon-ke-role (untuk employer ranking & compare)
CREATE TABLE IF NOT EXISTS candidate_role_matches (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  profile_id   INT          NULL,
  role_key     VARCHAR(40)  NULL,
  match_score  INT          NULL,
  readiness    INT          NULL,
  matched_json JSON         NULL,
  gap_json     JSON         NULL,
  reason       TEXT         NULL,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_profile (profile_id),
  INDEX idx_role    (role_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
