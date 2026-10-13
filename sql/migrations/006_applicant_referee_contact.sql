ALTER TABLE students
    ADD COLUMN applicant_ref_name VARCHAR(150) NULL AFTER passport_photo,
    ADD COLUMN applicant_ref_phone VARCHAR(20) NULL AFTER applicant_ref_name,
    ADD COLUMN applicant_ref_email VARCHAR(100) NULL AFTER applicant_ref_phone;