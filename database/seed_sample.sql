-- ============================================================
-- LUMINA — Sample seed (Fasa 1)
-- Enough to run the app + /selftest immediately.
-- For judging, expand students to ~1,500 using the prompt at the bottom.
-- Run: mysql -u USER -p DBNAME < seed_sample.sql   (after schema.sql)
-- ============================================================

-- Skills taxonomy (high_value=1 counts toward High Income) ----
INSERT INTO skills (code,label,domain,high_value) VALUES
('python','Python','Data',1),
('sql','SQL','Data',1),
('dashboarding','Dashboarding','Data',0),
('data_analysis','Data Analysis','Data',1),
('software','Software Development','Engineering',1),
('cloud','Cloud (Docker/Provider)','Engineering',1),
('leadership','Leadership','Business',0),
('stakeholder_mgmt','Stakeholder Management','Business',0),
('budgeting','Budgeting','Business',0),
('teamwork','Teamwork','Business',0),
('communication','Communication','Business',0),
('design_thinking','Design Thinking','Design',0),
('entrepreneurship','Entrepreneurship','Business',1),
('innovation','Innovation','Business',0),
('community','Community/ESG','Society',0),
('excel','Excel','Data',0);

-- Roles + required skills ----
INSERT INTO roles (id,title,domain,company,location,salary_band,high_income) VALUES
(1,'Data Analyst','Data','Maybank','KL','RM6-8k',1),
(2,'Backend Engineer','Engineering','CIMB','KL','RM8-10k',1),
(3,'Product Executive','Business','Grab','KL','RM5-7k',0),
(4,'Data Engineer','Data','Petronas','KL','RM9-11k',1),
(5,'Marketing Analyst','Business','Nestle','Selangor','RM5-7k',0);

INSERT INTO role_skills (role_id,skill_id,required) VALUES
(1,(SELECT id FROM skills WHERE code='sql'),1),
(1,(SELECT id FROM skills WHERE code='dashboarding'),1),
(1,(SELECT id FROM skills WHERE code='python'),1),
(1,(SELECT id FROM skills WHERE code='data_analysis'),1),
(2,(SELECT id FROM skills WHERE code='software'),1),
(2,(SELECT id FROM skills WHERE code='cloud'),1),
(2,(SELECT id FROM skills WHERE code='python'),1),
(2,(SELECT id FROM skills WHERE code='communication'),1),
(3,(SELECT id FROM skills WHERE code='stakeholder_mgmt'),1),
(3,(SELECT id FROM skills WHERE code='communication'),1),
(3,(SELECT id FROM skills WHERE code='leadership'),1),
(4,(SELECT id FROM skills WHERE code='sql'),1),
(4,(SELECT id FROM skills WHERE code='python'),1),
(4,(SELECT id FROM skills WHERE code='cloud'),1);

-- 4 hero personas (one per stage) ----
INSERT INTO students (id,name,age,stage,university,faculty,programme,cgpa,target_domain,work_animal,evidence_text,has_resume) VALUES
(1,'Nurul Huda',17,'16-18','Pre-U','Science','STEM stream',NULL,'Data','owl','Active in Science Club; built a small weather logger project; enjoys maths.',0),
(2,'Aiman Rahman',19,'19-22','USM','Computing','Computer Science',3.40,'Data','owl','Treasurer of the Robotics Club for 2 years; built an attendance app; led a data project.',0),
(3,'Wei Jie Tan',24,'23-28','UM','Science','Data Science',3.70,'Data','fox','Internship at a fintech; built dashboards; led a small analytics team.',1),
(4,'Sara Lim',27,'26-28+','UTM','Engineering','Software Engineering',3.55,'Engineering','eagle','3 years backend dev; mentors juniors; shipped cloud microservices.',1);

INSERT INTO student_skills (student_id,skill_id,source,confidence) VALUES
(2,(SELECT id FROM skills WHERE code='python'),'stated',1.00),
(2,(SELECT id FROM skills WHERE code='teamwork'),'stated',1.00),
(2,(SELECT id FROM skills WHERE code='budgeting'),'inferred',0.80),
(2,(SELECT id FROM skills WHERE code='stakeholder_mgmt'),'inferred',0.70),
(2,(SELECT id FROM skills WHERE code='leadership'),'inferred',0.70),
(2,(SELECT id FROM skills WHERE code='software'),'inferred',0.70),
(2,(SELECT id FROM skills WHERE code='data_analysis'),'inferred',0.70);

INSERT INTO evidence (student_id,type,title,verified) VALUES
(2,'leadership','Treasurer, Robotics Club (2y)',1),
(2,'project','Attendance App',0),
(2,'mycsd','Co-curricular transcript',1);

-- Cohort metrics (for University dashboard, Fasa 6) ----
INSERT INTO cohort_metrics (university,faculty,metric,value) VALUES
('USM','Computing','career_ready_pct',64),
('USM','Computing','industry_cert_pct',38),
('USM','Computing','high_income_pct',41),
('USM','Engineering','career_ready_pct',71);

INSERT INTO employer_feedback (employer,university,satisfaction) VALUES
('Maybank','USM',82),('CIMB','UM',86);

-- ============================================================
-- TO SCALE TO ~1,500 STUDENTS (for judging):
-- Prompt Claude: "Generate N MySQL INSERT rows for `students` matching this
-- schema: vary universities (UM, USM, UKM, UPM, UTM, UiTM, IIUM, UUM, Taylor's,
-- Sunway, MMU), faculties, programmes, ages 16-28, stages, a 1-3 sentence
-- evidence_text mixing clubs/projects/part-time work, ~30% has_resume=0,
-- a target_domain, a work_animal. IDs from 100 upward. Output valid SQL only."
-- Then generate matching student_skills rows.
-- ============================================================
