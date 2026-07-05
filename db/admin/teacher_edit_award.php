<?php ob_start();
session_start();

require(dirname(__FILE__) . '/admin_config.php');



require_once(INC . '/Teacher.php');
require_once(INC . '/AwardFormPreview.php');



show_headerteacher();

show_teacher_menu();



$award_id = "";



if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$award_id = $_POST['award_id'];



	$award = new Award();

	$award->update_award($award_id, $_POST);

	$message = <<< EOD

Award updated!

EOD;

} else {

	$award_id = $_GET['award_id'];

}



$teacher = new Teacher();

$award = $teacher->get_award($award_id);



$student_first_name = $award['student_first_name'];

$student_last_name = $award['student_last_name'];

$student_id = $award['student_id'];

$course_location_id = $award['course_location_id'];

$award_title = $award['award_title'];

$reason = $award['reason'];

$camper_of_the_week = $award['camper_of_the_week'];

$best_in_class = $award['best_in_class'];

$teacher_id = $award['teacher_id'];
$course_location_id = $award['course_location_id'];
$course_teachers = Award::course_teachers($course_location_id, $award);
$teacher_name = Award::teacher_list_text($course_teachers, 'full');
if (!$teacher_name) $teacher_name = 'Instructor';



$course_info = $teacher->get_course_by_course_location_id($course_location_id);

?>



<h3><a class="blue" href="teacher_home.php">Courses</a> :: <a class="blue" href="teacher_view_course_details.php?clid=<?php print $course_location_id ?>"><?php print $course_info['course_name'] ?></a> :: Edit Award Details</h3>



<?php
pb_award_form_render(array(
	'action' => $_SERVER['PHP_SELF'],
	'submit_label' => 'Update Award',
	'student_name' => $student_first_name . ' ' . $student_last_name,
	'course_name' => $course_info['course_name'],
	'award_title' => $award_title,
	'reason' => $reason,
	'teacher_name' => $teacher_name,
	'award_date' => !empty($course_info['course_end_date']) ? date('F jS, Y', strtotime($course_info['course_end_date'])) : '',
	'hidden' => array(
		'award_id' => $award_id,
		'student_id' => $student_id,
		'course_location_id' => $course_location_id
	),
	'message' => $message,
	'show_past_awards' => true
));
?>





<?php

show_footer();
ob_end_flush();
?>
