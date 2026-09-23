<?php
/**
 * Cohort & Semester Helpers
 * Resolves Semester and Department/Branch labels for any group_id
 */

if (!function_exists('getCohortDetails')) {
    function getCohortDetails($arg1, $arg2 = null) {
        if ($arg2 === null) {
            global $conn;
            $group_id = $arg1;
        } else {
            $conn = $arg1;
            $group_id = $arg2;
        }
        $gid = mysqli_real_escape_string($conn, strval($group_id));
        
        $dept_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT s.dept_id, s.sem_id, d.department 
            FROM subjects s 
            LEFT JOIN dept d ON s.dept_id = d.dept_id 
            WHERE s.group_id = '$gid' LIMIT 1"));
            
        // Check subject codes and department name to resolve the true academic semester
        $sem_digit = 0;
        $sub_res = mysqli_query($conn, "SELECT sub_code FROM subjects WHERE group_id = '$gid' LIMIT 10");
        if ($sub_res) {
            while ($srow = mysqli_fetch_assoc($sub_res)) {
                $code = $srow['sub_code'];
                if (preg_match('/\b\d?[A-Za-z]+([1-8])\d{2}/', $code, $m)) {
                    $sem_digit = intval($m[1]);
                    break;
                }
            }
        }

        if ($sem_digit === 0 && !empty($dept_row['department'])) {
            if (preg_match('/_sem(\d)/i', $dept_row['department'], $m)) {
                $sem_digit = intval($m[1]);
            } elseif (preg_match('/_(\d)_/i', $dept_row['department'], $m)) {
                $sem_digit = intval($m[1]);
            }
        }

        if ($sem_digit === 0 && !empty($dept_row['sem_id']) && intval($dept_row['sem_id']) > 1) {
            $sem_digit = intval($dept_row['sem_id']);
        }

        if ($sem_digit === 0) {
            $last_char = substr(strval($group_id), -1);
            if (is_numeric($last_char) && intval($last_char) > 0) {
                $sem_digit = intval($last_char);
            } else {
                $sem_digit = 1;
            }
        }

        // Clean branch / department display name
        $raw_dept = !empty($dept_row['department']) ? $dept_row['department'] : 'General Engineering';
        $branch_clean = $raw_dept;
        if ($raw_dept === 'AIML' || $raw_dept === 'AIML_sem3') {
            $branch_clean = 'Artificial Intelligence & Machine Learning (AIML)';
        } elseif ($raw_dept === 'IS') {
            $branch_clean = 'Information Science & Engineering (ISE)';
        } elseif ($raw_dept === 'IS_7_A') {
            $branch_clean = 'Information Science & Engineering (ISE - Sec A)';
        } elseif ($raw_dept === 'IS_7_B') {
            $branch_clean = 'Information Science & Engineering (ISE - Sec B)';
        } elseif ($raw_dept === 'CSE') {
            $branch_clean = 'Computer Science & Engineering (CSE)';
        }

        $sem_suffixes = [
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            4 => '4th',
            5 => '5th',
            6 => '6th',
            7 => '7th',
            8 => '8th'
        ];
        
        $sem_ordinal = isset($sem_suffixes[$sem_digit]) ? $sem_suffixes[$sem_digit] : "Sem {$sem_digit}";
        
        return [
            'group_id' => $group_id,
            'sem_digit' => $sem_digit,
            'sem_label' => "{$sem_ordinal} Semester",
            'branch' => $branch_clean,
            'full_label' => "{$sem_ordinal} Sem &bull; {$branch_clean}",
            'dropdown_label' => "{$sem_ordinal} Sem - {$branch_clean} (Group {$group_id})"
        ];
    }
}
