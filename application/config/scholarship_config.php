<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|----------------------------------------------------------------------
| Scholarship Mutual Exclusions
|----------------------------------------------------------------------
| Define pairs of scholarship IDs (tb_mas_scholarships.intID) that
| cannot be assigned together to the same student in the same term.
|
| Usage:
| - Put scholarship intID pairs as 2-element arrays.
| - The exclusion is symmetric (A excludes B and B excludes A).
| - Applies only to entries where tb_mas_scholarships.deduction_type = 'scholarship'.
|
| Example:
| $config['scholarship_mutual_exclusions'] = array(
|     array(101, 202),
|     array(303, 404),
| );
|
| Leave as empty array to disable.
*/
$config['scholarship_mutual_exclusions'] = array(
    // array(101, 202),
);
