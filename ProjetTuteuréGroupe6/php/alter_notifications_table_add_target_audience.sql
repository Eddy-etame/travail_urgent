ALTER TABLE notifications
ADD COLUMN target_audience ENUM('teachers', 'students', 'both') NOT NULL DEFAULT 'both';
