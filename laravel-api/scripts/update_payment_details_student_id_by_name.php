<?php
/**
 * DEPRECATED SCRIPT
 *
 * This script is deprecated and has been replaced by the slug-based updater:
 *   php laravel-api/scripts/update_payment_details_student_id.php [--apply] [--backup] [--limit=N] [--no-transaction]
 *
 * The slug-based updater matches:
 *   payment_details.student_number = tb_mas_users.slug
 * and sets:
 *   payment_details.student_information_id = tb_mas_users.intID
 *
 * Please use the script above instead.
 */
fwrite(STDERR, "Deprecated: Use laravel-api/scripts/update_payment_details_student_id.php (slug join: payment_details.student_number = tb_mas_users.slug)\n");
exit(1);
