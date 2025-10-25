-- ====================================
-- سكريبت إضافة 2000 صف اختباري
-- لجدول 0_stock_moves
-- ====================================

-- الحصول على آخر trans_id موجود
SET @last_id = (SELECT IFNULL(MAX(trans_id), 0) FROM 0_stock_moves);

-- إضافة 2000 صف جديد
INSERT INTO 0_stock_moves (
    trans_id,
    trans_no,
    stock_id,
    type,
    loc_code,
    tran_date,
    person_id,
    price,
    reference,
    qty,
    discount_percent,
    standard_cost,
    visible
)
SELECT 
    @last_id + (@row_number := @row_number + 1) AS trans_id,
    FLOOR(1000 + RAND() * 9000) AS trans_no,
    CONCAT('ITEM', LPAD(FLOOR(1 + RAND() * 100), 3, '0')) AS stock_id,
    FLOOR(10 + RAND() * 20) AS type,
    CONCAT('LOC', FLOOR(1 + RAND() * 5)) AS loc_code,
    DATE_ADD(CURDATE(), INTERVAL -FLOOR(RAND() * 365) DAY) AS tran_date,
    FLOOR(1 + RAND() * 50) AS person_id,
    ROUND(10 + RAND() * 990, 2) AS price,
    CONCAT('REF-', LPAD(FLOOR(1 + RAND() * 99999), 5, '0')) AS reference,
    FLOOR(1 + RAND() * 100) AS qty,
    ROUND(RAND() * 20, 2) AS discount_percent,
    ROUND(10 + RAND() * 500, 2) AS standard_cost,
    1 AS visible
FROM 
    (SELECT @row_number := 0) AS init,
    (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS t1,
    (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS t2,
    (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS t3,
    (SELECT 0 UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS t4
LIMIT 2000;

-- عرض النتيجة
SELECT 
    '✅ تم إضافة 2000 صف جديد بنجاح!' AS message,
    MIN(trans_id) AS first_trans_id,
    MAX(trans_id) AS last_trans_id,
    COUNT(*) AS total_rows
FROM 0_stock_moves 
WHERE trans_id > @last_id;
