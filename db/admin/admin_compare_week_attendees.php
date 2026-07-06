<?php
ob_start();
session_start();
require(dirname(__FILE__) . '/admin_config.php');

$admin_id = $_SESSION['admin_id'];
if (!$admin_id) {
    header("Location: admin_login.php");
    exit;
}

$connection = connect_database();

function pb_compare_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function pb_compare_valid_date($date) {
    return is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
}

function pb_compare_weeks($connection) {
    $year = defined('CURRENT_YEAR') ? (int)CURRENT_YEAR : (int)date('Y');
    $sql = "
        SELECT
            course_location.course_start_date,
            MAX(course_location.course_end_date) AS course_end_date,
            COUNT(DISTINCT course_registration.student_id) AS camper_count
        FROM course_location
        INNER JOIN course ON course.course_id = course_location.course_id
        LEFT JOIN course_registration ON course_registration.course_location_id = course_location.course_location_id
        WHERE course_location.active = 'y'
          AND course.course_id != 466
          AND YEAR(course_location.course_start_date) = '$year'
        GROUP BY course_location.course_start_date
        ORDER BY course_location.course_start_date ASC
    ";
    $rs = mysqli_query($connection, $sql);
    $weeks = array();
    while ($rs && ($row = mysqli_fetch_assoc($rs))) {
        $weeks[] = $row;
    }
    return $weeks;
}

function pb_compare_locations($connection) {
    $sql = "
        SELECT DISTINCT location.location_id, location.school_name
        FROM location
        INNER JOIN course_location ON course_location.location_id = location.location_id
        INNER JOIN course ON course.course_id = course_location.course_id
        WHERE course_location.active = 'y'
          AND course.course_id != 466
        ORDER BY location.school_name ASC
    ";
    $rs = mysqli_query($connection, $sql);
    $locations = array();
    while ($rs && ($row = mysqli_fetch_assoc($rs))) {
        $locations[] = $row;
    }
    return $locations;
}

function pb_compare_default_week($weeks) {
    if (!empty($_GET['week_a']) && pb_compare_valid_date($_GET['week_a'])) {
        return $_GET['week_a'];
    }
    $today = date('Y-m-d');
    foreach ($weeks as $week) {
        if ($week['course_start_date'] <= $today && $week['course_end_date'] >= $today) {
            return $week['course_start_date'];
        }
    }
    foreach ($weeks as $week) {
        if ($week['course_start_date'] > $today) {
            return $week['course_start_date'];
        }
    }
    return count($weeks) ? $weeks[count($weeks) - 1]['course_start_date'] : $today;
}

function pb_compare_default_compare_week($weeks, $week_a) {
    if (!empty($_GET['week_b']) && pb_compare_valid_date($_GET['week_b'])) {
        return $_GET['week_b'];
    }
    $previous = '';
    foreach ($weeks as $week) {
        if ($week['course_start_date'] < $week_a) {
            $previous = $week['course_start_date'];
        }
    }
    if ($previous) return $previous;
    foreach ($weeks as $week) {
        if ($week['course_start_date'] != $week_a) return $week['course_start_date'];
    }
    return $week_a;
}

function pb_compare_default_location($locations) {
    if (!empty($_GET['location_id'])) {
        return (int)$_GET['location_id'];
    }
    foreach ($locations as $location) {
        if (stripos($location['school_name'], 'Eagle') !== false) {
            return (int)$location['location_id'];
        }
    }
    return count($locations) ? (int)$locations[0]['location_id'] : 0;
}

function pb_compare_week_label($weeks, $date) {
    foreach ($weeks as $week) {
        if ($week['course_start_date'] == $date) {
            return date('M j', strtotime($week['course_start_date'])) . ' - ' . date('M j, Y', strtotime($week['course_end_date']));
        }
    }
    return $date;
}

function pb_compare_location_name($locations, $location_id) {
    foreach ($locations as $location) {
        if ((int)$location['location_id'] == (int)$location_id) {
            return $location['school_name'];
        }
    }
    return 'Selected Location';
}

function pb_compare_matching_attendees($connection, $location_id, $week_a, $week_b) {
    $location_id = (int)$location_id;
    $week_a = mysqli_real_escape_string($connection, $week_a);
    $week_b = mysqli_real_escape_string($connection, $week_b);
    if (!$location_id || !$week_a || !$week_b) return array();

    $sql = "
        SELECT
            current_students.student_id,
            current_students.student_first_name,
            current_students.student_last_name,
            current_students.date_of_birth,
            current_students.gender,
            current_students.current_courses,
            current_students.current_course_location_ids,
            previous_students.previous_courses,
            previous_students.previous_course_location_ids
        FROM (
            SELECT
                student_info.student_id,
                student_info.student_first_name,
                student_info.student_last_name,
                student_info.date_of_birth,
                student_info.gender,
                GROUP_CONCAT(DISTINCT course.course_name ORDER BY course.course_name SEPARATOR ', ') AS current_courses,
                GROUP_CONCAT(DISTINCT course_location.course_location_id ORDER BY course.course_name SEPARATOR ',') AS current_course_location_ids
            FROM course_registration
            INNER JOIN course_location ON course_location.course_location_id = course_registration.course_location_id
            INNER JOIN course ON course.course_id = course_location.course_id
            INNER JOIN student_info ON student_info.student_id = course_registration.student_id
            WHERE course_location.location_id = '$location_id'
              AND course_location.course_start_date = '$week_a'
              AND course_location.active = 'y'
              AND course.course_id != 466
              AND IFNULL(course_registration.late_cancelled, 0) = 0
            GROUP BY student_info.student_id, student_info.student_first_name, student_info.student_last_name, student_info.date_of_birth, student_info.gender
        ) current_students
        INNER JOIN (
            SELECT
                course_registration.student_id,
                GROUP_CONCAT(DISTINCT course.course_name ORDER BY course.course_name SEPARATOR ', ') AS previous_courses,
                GROUP_CONCAT(DISTINCT course_location.course_location_id ORDER BY course.course_name SEPARATOR ',') AS previous_course_location_ids
            FROM course_registration
            INNER JOIN course_location ON course_location.course_location_id = course_registration.course_location_id
            INNER JOIN course ON course.course_id = course_location.course_id
            WHERE course_location.location_id = '$location_id'
              AND course_location.course_start_date = '$week_b'
              AND course_location.active = 'y'
              AND course.course_id != 466
              AND IFNULL(course_registration.late_cancelled, 0) = 0
            GROUP BY course_registration.student_id
        ) previous_students ON previous_students.student_id = current_students.student_id
        ORDER BY current_students.student_last_name ASC, current_students.student_first_name ASC
    ";
    $rs = mysqli_query($connection, $sql);
    if (!$rs) die(mysqli_error($connection));
    $rows = array();
    while ($row = mysqli_fetch_assoc($rs)) {
        $rows[] = $row;
    }
    return $rows;
}

function pb_compare_grade($admin, $student_id) {
    return $admin->get_grade_level_by_current_year($student_id);
}

$admin = new Admin();
$weeks = pb_compare_weeks($connection);
$locations = pb_compare_locations($connection);
$week_a = pb_compare_default_week($weeks);
$week_b = pb_compare_default_compare_week($weeks, $week_a);
$location_id = pb_compare_default_location($locations);
$rows = pb_compare_matching_attendees($connection, $location_id, $week_a, $week_b);
$location_name = pb_compare_location_name($locations, $location_id);
$week_a_label = pb_compare_week_label($weeks, $week_a);
$week_b_label = pb_compare_week_label($weeks, $week_b);

if (!empty($_GET['csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="repeat_attendees_' . $location_id . '_' . $week_a . '_vs_' . $week_b . '.csv"');
    $fp = fopen('php://output', 'w');
    fputcsv($fp, array('Last Name', 'First Name', 'Grade', 'Birthdate', 'Current Week Course(s)', 'Compare Week Course(s)', 'Student ID'));
    foreach ($rows as $row) {
        fputcsv($fp, array(
            $row['student_last_name'],
            $row['student_first_name'],
            pb_compare_grade($admin, $row['student_id']),
            $row['date_of_birth'],
            $row['current_courses'],
            $row['previous_courses'],
            $row['student_id']
        ));
    }
    fclose($fp);
    ob_end_flush();
    exit;
}

show_header();
show_admin_menu();
?>
<style>
.compare-wrap { max-width:1120px; margin:14px auto 32px; font-family:Arial, Helvetica, sans-serif; }
.compare-filter { padding:13px; border:1px solid #c8d8e8; background:#f7fbff; margin:12px 0 16px; }
.compare-filter label { display:inline-block; margin:4px 10px 4px 0; font-weight:bold; }
.compare-filter select { min-width:210px; padding:4px; }
.compare-summary { display:flex; flex-wrap:wrap; gap:10px; margin:10px 0 16px; }
.compare-stat { padding:10px 13px; border:1px solid #d8e4f0; background:#fff; border-radius:6px; font-weight:bold; color:#123c69; }
.compare-table { width:100%; border-collapse:collapse; background:#fff; }
.compare-table th, .compare-table td { border:1px solid #d8e4f0; padding:7px 8px; text-align:left; vertical-align:top; }
.compare-table th { background:#123c69; color:#fff; }
.compare-table tr:nth-child(even) td { background:#f3f7fb; }
.compare-small { color:#53657a; font-size:12px; }
.compare-actions { margin:10px 0; }
</style>
<div class="compare-wrap">
    <h3><a class="blue" href="admin_home.php">Admin Home</a> :: Repeat Attendees by Week</h3>
    <p>Select a location and two camp weeks to see campers enrolled in both weeks at that same location.</p>

    <div class="compare-filter">
        <form method="get" action="<?php echo pb_compare_h($_SERVER['PHP_SELF']); ?>">
            <label>Location:
                <select name="location_id" onchange="this.form.submit();">
                    <?php foreach ($locations as $location) { ?>
                        <option value="<?php echo (int)$location['location_id']; ?>"<?php if ((int)$location['location_id'] == $location_id) echo ' selected'; ?>><?php echo pb_compare_h($location['school_name']); ?></option>
                    <?php } ?>
                </select>
            </label>
            <label>Main week:
                <select name="week_a" onchange="this.form.submit();">
                    <?php foreach ($weeks as $week) {
                        $label = date('M j', strtotime($week['course_start_date'])) . ' - ' . date('M j, Y', strtotime($week['course_end_date'])) . ' (' . (int)$week['camper_count'] . ')';
                    ?>
                        <option value="<?php echo pb_compare_h($week['course_start_date']); ?>"<?php if ($week['course_start_date'] == $week_a) echo ' selected'; ?>><?php echo pb_compare_h($label); ?></option>
                    <?php } ?>
                </select>
            </label>
            <label>Compare to:
                <select name="week_b" onchange="this.form.submit();">
                    <?php foreach ($weeks as $week) {
                        $label = date('M j', strtotime($week['course_start_date'])) . ' - ' . date('M j, Y', strtotime($week['course_end_date'])) . ' (' . (int)$week['camper_count'] . ')';
                    ?>
                        <option value="<?php echo pb_compare_h($week['course_start_date']); ?>"<?php if ($week['course_start_date'] == $week_b) echo ' selected'; ?>><?php echo pb_compare_h($label); ?></option>
                    <?php } ?>
                </select>
            </label>
            <input type="submit" value="Compare">
        </form>
    </div>

    <div class="compare-summary">
        <div class="compare-stat"><?php echo pb_compare_h($location_name); ?></div>
        <div class="compare-stat">Main: <?php echo pb_compare_h($week_a_label); ?></div>
        <div class="compare-stat">Also attended: <?php echo pb_compare_h($week_b_label); ?></div>
        <div class="compare-stat"><?php echo count($rows); ?> matching camper<?php if (count($rows) != 1) echo 's'; ?></div>
    </div>

    <div class="compare-actions">
        <a class="blue" href="<?php echo pb_compare_h($_SERVER['PHP_SELF']); ?>?location_id=<?php echo (int)$location_id; ?>&week_a=<?php echo urlencode($week_a); ?>&week_b=<?php echo urlencode($week_b); ?>&csv=1">Download CSV</a>
    </div>

    <table class="compare-table">
        <tr>
            <th>#</th>
            <th>Camper</th>
            <th>Grade</th>
            <th>Birthdate</th>
            <th><?php echo pb_compare_h($week_a_label); ?></th>
            <th><?php echo pb_compare_h($week_b_label); ?></th>
        </tr>
        <?php if (!count($rows)) { ?>
            <tr><td colspan="6">No campers matched both weeks for this location.</td></tr>
        <?php } ?>
        <?php foreach ($rows as $index => $row) {
            $student_id = (int)$row['student_id'];
            $student_name = trim($row['student_first_name'] . ' ' . $row['student_last_name']);
            $grade = pb_compare_grade($admin, $student_id);
        ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <td><a class="blue" target="_blank" href="admin_view_student_details.php?student_id=<?php echo $student_id; ?>"><?php echo pb_compare_h($student_name); ?></a><div class="compare-small">ID <?php echo $student_id; ?></div></td>
                <td><?php echo pb_compare_h($grade); ?></td>
                <td><?php echo pb_compare_h($row['date_of_birth']); ?></td>
                <td><?php echo pb_compare_h($row['current_courses']); ?></td>
                <td><?php echo pb_compare_h($row['previous_courses']); ?></td>
            </tr>
        <?php } ?>
    </table>
</div>
<?php
show_footer();
ob_end_flush();
?>
