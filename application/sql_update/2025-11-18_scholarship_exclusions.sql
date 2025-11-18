-- Scholarship Mutual Exclusions Table
-- Purpose: Store pairs of scholarship IDs that cannot be assigned together to the same student in the same term.
-- Engine: MySQL/MariaDB (InnoDB)

-- IMPORTANT USAGE RULE:
-- Always store the smaller scholarship ID in scholarship_id_a and the larger in scholarship_id_b
-- to avoid duplicate/improper pairs. A unique key on (scholarship_id_a, scholarship_id_b) will
-- then guarantee each pair exists only once.

-- Example:
--   Suppose scholarship 7 and scholarship 12 are mutually exclusive:
--   INSERT INTO tb_mas_scholarship_exclusions (scholarship_id_a, scholarship_id_b) VALUES (7, 12);
--   NOT: (12, 7)

START TRANSACTION;

CREATE TABLE IF NOT EXISTS tb_mas_scholarship_exclusions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  scholarship_id_a INT UNSIGNED NOT NULL,
  scholarship_id_b INT UNSIGNED NOT NULL,

  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),

  -- Foreign keys to scholarships table (adjust if your table/column names differ)
  CONSTRAINT fk_scholarship_excl_a
    FOREIGN KEY (scholarship_id_a)
    REFERENCES tb_mas_scholarships (intID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  CONSTRAINT fk_scholarship_excl_b
    FOREIGN KEY (scholarship_id_b)
    REFERENCES tb_mas_scholarships (intID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

  -- Ensure a pair is stored only once (requires storing smaller ID in _a)
  UNIQUE KEY uq_scholarship_pair (scholarship_id_a, scholarship_id_b),

  -- Helpful indexes
  KEY idx_scholarship_id_a (scholarship_id_a),
  KEY idx_scholarship_id_b (scholarship_id_b)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

COMMIT;

-- ------------------------------------------------------------
-- Admin usage examples:
-- ------------------------------------------------------------
-- Insert mutually exclusive pair (ensure smaller ID is in scholarship_id_a):
-- INSERT INTO tb_mas_scholarship_exclusions (scholarship_id_a, scholarship_id_b) VALUES (101, 202);
-- INSERT INTO tb_mas_scholarship_exclusions (scholarship_id_a, scholarship_id_b) VALUES (303, 404);

-- View configured pairs with scholarship names:
-- SELECT
--   e.id,
--   e.scholarship_id_a,
--   sa.name AS scholarship_a_name,
--   e.scholarship_id_b,
--   sb.name AS scholarship_b_name,
--   e.created_at,
--   e.updated_at
-- FROM tb_mas_scholarship_exclusions e
-- JOIN tb_mas_scholarships sa ON sa.intID = e.scholarship_id_a
-- JOIN tb_mas_scholarships sb ON sb.intID = e.scholarship_id_b
-- ORDER BY e.id DESC;

-- Delete a pair:
-- DELETE FROM tb_mas_scholarship_exclusions WHERE id = ?;

-- Notes:
-- - The application code reads these pairs and enforces mutual exclusivity when:
--   * Rendering available scholarships for assignment (filtered list)
--   * Validating add_scholarship POST to block invalid assignments
-- - Exclusivity applies only to deduction_type = 'scholarship', not 'discount'.
