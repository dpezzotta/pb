<?php ob_start();
session_start();

require(dirname(__FILE__) . '/admin_config.php');
require_once(INC . '/AwardFormPreview.php');



$admin_id = $_SESSION['admin_id'];



if (!$admin_id) {

    header("Location: admin_login.php");

}



if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$student_id = $_POST['student_id'];

	$course_location_id = $_POST['course_location_id'];

	$teacher_id = $_POST['teacher_id'];



	$award = new Award();

	$saved = $award->give_award($teacher_id, $_POST);
	if (!$saved) {
		$validation_error = $award->get_validation_error();
		if ($validation_error) {
			$_SESSION["message"] = "<div style='margin:12px 0;padding:10px 12px;border:1px solid #f4c095;background:#fff7ed;color:#9a3412;font-weight:bold;'>" . htmlspecialchars($validation_error, ENT_QUOTES, 'UTF-8') . "</div>";
		} else {
			$duplicate = $award->get_duplicate_award();
			$_SESSION["message"] = "<div style='margin:12px 0;padding:10px 12px;border:1px solid #f4c095;background:#fff7ed;color:#9a3412;font-weight:bold;'>This camper already has an award for this camp week. Please edit the existing award instead of creating a second one.";
			if ($duplicate) {
				$_SESSION["message"] .= " Existing award: <a class='blue' href='admin_view_award_details.php?award_id=" . intval($duplicate['award_id']) . "'>" . $duplicate['award_title'] . "</a>.";
			}
			$_SESSION["message"] .= "</div>";
		}
	}

	header("Location: admin_view_course_students.php?course_location_id=$course_location_id");	

} else {

	$student_id = $_GET['student_id'];

	$course_location_id = $_GET['course_location_id'];

}



$admin = new Admin();

$student_info = $admin->get_student_by_student_id($student_id);

$course_info = $admin->get_course_by_course_location_id($course_location_id);

$teacher_info = $admin->get_teacher_by_course_location_id($course_location_id);



show_header();

show_admin_menu();

?>



<h3>

<a class="blue" href="admin_home.php">Admin Home</a> ::

<a class="blue" href="admin_view_course_students.php?course_location_id=<?=$course_location_id ?>"><?=$course_info['course_name'] ?></a> ::

Award

</h3>



<?php
$teacher_name = 'Instructor';
$teacher_display = 'None';
if (is_array($teacher_info)) {
	$course_teachers = Award::course_teachers($course_location_id, $teacher_info);
	$teacher_name = Award::teacher_list_text($course_teachers, 'full');
	$teacher_display = pb_award_form_h($teacher_name);
}
pb_award_form_render(array(
	'action' => $_SERVER['PHP_SELF'],
	'submit_label' => 'Submit Award',
	'student_name' => $student_info['student_first_name'] . ' ' . $student_info['student_last_name'],
	'course_name' => $course_info['course_name'],
	'teacher_name' => $teacher_name,
	'teacher_display' => $teacher_display,
	'award_date' => !empty($course_info['course_end_date']) ? date('F jS, Y', strtotime($course_info['course_end_date'])) : '',
	'hidden' => array(
		'student_id' => $student_id,
		'course_location_id' => $course_location_id,
		'teacher_id' => is_array($teacher_info) ? $teacher_info['teacher_id'] : ''
	)
));
?>





<?php

show_footer();
ob_end_flush();
?>

