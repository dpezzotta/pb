<?php ob_start();
session_start();
require(dirname(__FILE__) . '/admin_config.php');
require_once(INC . '/AwardFormPreview.php');
require_once(dirname(__FILE__) . '/inc/AwardEmailWorkflow.php');

$admin_id = $_SESSION['admin_id'];

if (!$admin_id) {
    header("Location: admin_login.php");

}
$award = new Award();
$admin = new Admin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$award_id = $_POST['award_id'];
	$date = $_POST['date'];
	$location_id = $_POST['location_id'];
	$db = connect_database();
	AwardEmailWorkflow::update_award_text($db, $award_id, $_POST['award_title'], $_POST['reason']);
	$message = <<< EOD
Award updated!
EOD;
} else {
	$award_id = $_GET['award_id'];
	$date = $_GET['date'];
	$location_id = $_GET['location_id'];
}

$award_info = $award->get_award($award_id);
$course_location_id = $award_info['course_location_id'];
$course_info = $admin->get_course_by_course_location_id($course_location_id);

show_header();
show_admin_menu();
?>

<?php
$course_teachers = Award::course_teachers($course_location_id, $award_info);
$teacher_name = Award::teacher_list_text($course_teachers, 'full');
if (!$teacher_name) $teacher_name = 'Instructor';
pb_award_form_render(array(
	'action' => $_SERVER['PHP_SELF'],
	'submit_label' => 'Update Award',
	'student_name' => $award_info['student_first_name'] . ' ' . $award_info['student_last_name'],
	'course_name' => $course_info['course_name'],
	'award_title' => $award_info['award_title'],
	'reason' => $award_info['reason'],
	'teacher_name' => $teacher_name,
	'award_date' => !empty($course_info['course_end_date']) ? date('F jS, Y', strtotime($course_info['course_end_date'])) : '',
	'hidden' => array(
		'award_id' => $award_info['award_id'],
		'student_id' => $award_info['student_id'],
		'date' => $date,
		'location_id' => $location_id,
		'course_location_id' => $course_location_id
	),
	'message' => $message
));
?>

<?php
show_footer();
ob_end_flush();
?>
