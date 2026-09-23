<?php
require_once __DIR__ . '/../includes/DBConnection.php';
require_once __DIR__ . '/../scratch/test_strictly_contiguous.php';

$teacher = 31; $group = '91'; $d = 2; $s1 = 3; $s2 = 4;
echo "group_busy s1: " . (isset($group_busy[$group][$d][$s1]) ? 'yes' : 'no') . "\n";
echo "group_busy s2: " . (isset($group_busy[$group][$d][$s2]) ? 'yes' : 'no') . "\n";
echo "teacher_busy s1: " . (isset($teacher_busy[$teacher][$d][$s1]) ? 'yes' : 'no') . "\n";
echo "teacher_busy s2: " . (isset($teacher_busy[$teacher][$d][$s2]) ? 'yes' : 'no') . "\n";
echo "consec s1: " . (isTeacherConsecutiveOk($teacher, $d, $s1, $teacher_busy) ? 'yes' : 'no') . "\n";
echo "consec s2: " . (isTeacherConsecutiveOk($teacher, $d, $s2, $teacher_busy) ? 'yes' : 'no') . "\n";
