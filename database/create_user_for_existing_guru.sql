-- Script untuk membuat user account untuk guru yang belum punya
-- Jalankan script ini di database production

-- 1. Lihat guru yang belum punya user account
SELECT 
    id_guru,
    nama_guru,
    nip,
    user_id
FROM guru 
WHERE user_id IS NULL
ORDER BY nama_guru;

-- 2. Buat user account untuk setiap guru yang belum punya
-- Script ini akan membuat username dari NIP dan password default 'guru123'

DELIMITER $$

CREATE PROCEDURE create_user_for_existing_guru()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_guru INT;
    DECLARE v_nama_guru VARCHAR(100);
    DECLARE v_nip VARCHAR(20);
    DECLARE v_username VARCHAR(50);
    DECLARE v_password VARCHAR(255);
    DECLARE v_user_id INT;
    
    DECLARE guru_cursor CURSOR FOR 
        SELECT id_guru, nama_guru, nip 
        FROM guru 
        WHERE user_id IS NULL;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Password default: guru123 (hashed)
    SET v_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    OPEN guru_cursor;
    
    read_loop: LOOP
        FETCH guru_cursor INTO v_id_guru, v_nama_guru, v_nip;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Generate username dari NIP
        SET v_username = CONCAT('guru_', v_nip);
        
        -- Cek apakah username sudah ada, jika ya tambahkan suffix
        WHILE EXISTS (SELECT 1 FROM users WHERE name = v_username) DO
            SET v_username = CONCAT('guru_', v_nip, '_', SUBSTRING(MD5(RAND()), 1, 4));
        END WHILE;
        
        -- Insert user
        INSERT INTO users (name, password, role, created_at)
        VALUES (v_username, v_password, 'guru', NOW());
        
        SET v_user_id = LAST_INSERT_ID();
        
        -- Update guru dengan user_id
        UPDATE guru 
        SET user_id = v_user_id 
        WHERE id_guru = v_id_guru;
        
        -- Log
        SELECT CONCAT('Created user: ', v_username, ' for guru: ', v_nama_guru, ' (ID: ', v_id_guru, ')') AS log_message;
        
    END LOOP;
    
    CLOSE guru_cursor;
    
    SELECT 'User accounts created successfully for all guru!' AS result;
END$$

DELIMITER ;

-- 3. Jalankan procedure
CALL create_user_for_existing_guru();

-- 4. Drop procedure setelah selesai
DROP PROCEDURE IF EXISTS create_user_for_existing_guru;

-- 5. Verifikasi hasil
SELECT 
    g.id_guru,
    g.nama_guru,
    g.nip,
    g.user_id,
    u.name AS username,
    u.role
FROM guru g
LEFT JOIN users u ON g.user_id = u.id
ORDER BY g.nama_guru;

-- 6. Lihat semua user guru yang baru dibuat
SELECT 
    u.id,
    u.name AS username,
    u.role,
    g.nama_guru,
    g.nip,
    u.created_at
FROM users u
LEFT JOIN guru g ON u.id = g.user_id
WHERE u.role = 'guru'
ORDER BY u.created_at DESC;

-- CATATAN PENTING:
-- Password default untuk semua guru adalah: guru123
-- Silakan informasikan kepada setiap guru untuk login dengan:
-- Username: guru_[NIP]
-- Password: guru123
-- 
-- Guru disarankan untuk mengganti password setelah login pertama kali
