-- Table to map teachers to subjects
CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES enseignants(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES matieres(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject (teacher_id, subject_id)
);

-- Table to store teacher availability in 4-hour pairs starting from 8:30
CREATE TABLE IF NOT EXISTS teacher_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    availability_date DATE NOT NULL,
    start_time TIME NOT NULL, -- e.g., 08:30:00, 12:30:00, 16:30:00, 20:30:00
    duration_minutes INT NOT NULL DEFAULT 240, -- 4 hours = 240 minutes
    FOREIGN KEY (teacher_id) REFERENCES enseignants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_availability (teacher_id, availability_date, start_time)
);

-- Table to link students to subjects
DROP TABLE IF EXISTS etudiant_matiere;

CREATE TABLE etudiant_matiere (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_etudiant INT NOT NULL,
    id_matiere INT NOT NULL,
    FOREIGN KEY (id_etudiant) REFERENCES etudiants(id) ON DELETE CASCADE,
    FOREIGN KEY (id_matiere) REFERENCES matieres(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_subject (id_etudiant, id_matiere)
);
