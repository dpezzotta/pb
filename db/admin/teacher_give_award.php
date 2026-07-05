<?php ob_start();
session_start();
require(dirname(__FILE__) . '/admin_config.php');

require_once(INC . '/Teacher.php');
require_once(INC . '/AwardFormPreview.php');

$teacher_id = $_SESSION['teacher_id'];

if (!$teacher_id) {
	header("Location: teacher_login.php");
}
$cohort_id = $_REQUEST['cohort_id'];
$date = $_REQUEST['date'];
$clid = $_REQUEST['clid'];
$teacher = new Teacher();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$award = new Award();
	if ($cohort_id)	
		$saved = $award->give_award($cohort_id, $_POST);
	else
		$saved = $award->give_award($teacher_id, $_POST);

	$course_location_id = $_POST['course_location_id'];
	$next_student_id = !empty($_POST['next_student_id']) ? intval($_POST['next_student_id']) : 0;
	if (!$saved) {
		$validation_error = $award->get_validation_error();
		if ($validation_error) {
			$_SESSION["message"] = "<div style='margin:12px 0;padding:10px 12px;border:1px solid #f4c095;background:#fff7ed;color:#9a3412;font-weight:bold;'>" . htmlspecialchars($validation_error, ENT_QUOTES, 'UTF-8') . "</div>";
		} else {
			$duplicate = $award->get_duplicate_award();
			$_SESSION["message"] = "<div style='margin:12px 0;padding:10px 12px;border:1px solid #f4c095;background:#fff7ed;color:#9a3412;font-weight:bold;'>This camper already has an award for this camp week. Please edit the existing award instead of creating a second one.";
			if ($duplicate) {
				$_SESSION["message"] .= " Existing award: <a class='blue' href='teacher_view_award_details.php?award_id=" . intval($duplicate['award_id']) . "'>" . $duplicate['award_title'] . "</a>.";
			}
			$_SESSION["message"] .= "</div>";
		}
		header("Location: teacher_give_award.php?sid=" . intval($_POST['student_id']) . "&clid=$course_location_id&date=" . urlencode($date) . "&cohort_id=" . urlencode($cohort_id));
		exit;
	}

	if ($next_student_id) {
		$_SESSION["message"] = "<div style='margin:12px 0;padding:10px 12px;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;font-weight:bold;'>Award saved. Moving to the next camper.</div>";
		header("Location: teacher_give_award.php?sid=$next_student_id&clid=$course_location_id&date=" . urlencode($date) . "&cohort_id=" . urlencode($cohort_id));
		exit;
	}
	if ($cohort_id)
		header("Location: teacher_view_cohort_details_week.php?clid=$course_location_id&date=$date&cohort_id=$cohort_id");
	else
		header("Location: teacher_view_course_details.php?clid=$course_location_id&teacher_id=$teacher_id");

} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$student_id = $_GET['sid'];
	$course_location_id = $_GET['clid'];

	$student_info = $teacher->get_student_by_student_id($student_id);

	$student_first_name = $student_info['student_first_name'];
	$student_last_name = $student_info['student_last_name'];

	$course_info = $teacher->get_course_by_course_location_id($course_location_id);
}
$roster_students = $teacher->get_students_by_course_location_id($course_location_id, 'student_last_name');
$roster_ids = array();
$award_done_count = 0;
$current_position = 0;
$previous_student_id = 0;
$next_student_id = 0;
for ($i = 0; $i < count($roster_students); $i++) {
	$roster_student_id = intval($roster_students[$i]['student_id']);
	$roster_ids[] = $roster_student_id;
	$student_awards = $teacher->get_student_awards($roster_student_id, $course_location_id);
	if (count($student_awards)) $award_done_count++;
	if ($roster_student_id == intval($student_id)) {
		$current_position = $i + 1;
		if ($i > 0) $previous_student_id = intval($roster_students[$i - 1]['student_id']);
		if ($i < count($roster_students) - 1) $next_student_id = intval($roster_students[$i + 1]['student_id']);
	}
}
$roster_total = count($roster_students);
$teacher_info = $teacher->get_teacher_by_teacher_id($teacher_id);
$course_teachers = Award::course_teachers($course_location_id, $teacher_info);
$teacher_name = Award::teacher_list_text($course_teachers, 'full');
if (!$teacher_name) $teacher_name = 'Instructor';

show_headerteacher();
show_teacher_menu();
?>
<style>
.award-nav {
	max-width: 1180px;
	margin: 14px auto;
	padding: 12px 16px;
	border: 1px solid #d8e4f0;
	border-radius: 8px;
	background: #f8fbfe;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	font-family: "Segoe UI", Arial, sans-serif;
}
.award-nav-center {
	text-align: center;
	color: #173b5f;
	font-weight: 900;
	line-height: 1.35;
}
.award-nav-center span {
	display: block;
	color: #526a83;
	font-size: 12px;
	font-weight: 800;
}
.award-nav a,
.award-nav .award-nav-disabled {
	min-width: 96px;
	display: inline-block;
	padding: 8px 10px;
	border-radius: 8px;
	text-align: center;
	text-decoration: none !important;
	font-weight: 900;
}
.award-nav a {
	background: #edf4ff;
	color: #1f5fbf !important;
}
.award-nav a:hover {
	background: #dfeeff;
}
.award-nav a.award-nav-pdf {
	background: #fff7ed;
	color: #c2410c !important;
}
.award-nav a.award-nav-pdf:hover {
	background: #ffedd5;
}
.award-nav .award-nav-disabled {
	background: #f3f4f6;
	color: #9ca3af;
}
</style>

<h3><a class="blue" href="teacher_home.php">Courses</a> :: <a class="blue" href="teacher_view_course_details.php?clid=<?php print $course_location_id ?>"><?php print $course_info['course_name']; ?></a> :: Award</h3>
<?php
if ($_SESSION["message"]) {
	echo $_SESSION["message"];
	unset($_SESSION["message"]);
}
?>
<div class="award-nav">
	<?php if ($previous_student_id) { ?>
		<a href="teacher_give_award.php?sid=<?php echo $previous_student_id; ?>&clid=<?php echo intval($course_location_id); ?>&date=<?php echo urlencode($date); ?>&cohort_id=<?php echo urlencode($cohort_id); ?>">&larr; Previous</a>
	<?php } else { ?>
		<span class="award-nav-disabled">&larr; Previous</span>
	<?php } ?>
	<div class="award-nav-center">
		Award <?php echo intval($current_position); ?> of <?php echo intval($roster_total); ?>
		<span><?php echo intval($award_done_count); ?> of <?php echo intval($roster_total); ?> done</span>
	</div>
	<?php if ($next_student_id) { ?>
		<a href="teacher_give_award.php?sid=<?php echo $next_student_id; ?>&clid=<?php echo intval($course_location_id); ?>&date=<?php echo urlencode($date); ?>&cohort_id=<?php echo urlencode($cohort_id); ?>">Next &rarr;</a>
	<?php } else { ?>
		<span class="award-nav-disabled">Next &rarr;</span>
	<?php } ?>
</div>
<div style="max-width:1180px;margin:-4px auto 14px;text-align:right;font-family:'Segoe UI',Arial,sans-serif;">
	<a class="award-nav-pdf" style="display:inline-block;padding:8px 10px;border-radius:8px;background:#fff7ed;color:#c2410c;text-decoration:none;font-weight:900;" target="_blank" href="teacher_print_awards.php?clid=<?php echo intval($course_location_id); ?>&course_end_date=<?php echo !empty($course_info['course_end_date']) ? urlencode(date('Y-m-d', strtotime($course_info['course_end_date']))) : urlencode($date); ?>">See PDFs</a>
</div>

<?php
pb_award_form_render(array(
	'action' => '/db/admin/teacher_give_award.php?sid=' . urlencode($student_id) . '&clid=' . urlencode($clid) . '&date=' . urlencode($date) . '&cohort_id=' . urlencode($cohort_id),
	'submit_label' => 'Submit Award',
	'student_name' => $student_first_name . ' ' . $student_last_name,
	'course_name' => $course_info['course_name'],
	'teacher_name' => $teacher_name,
	'award_date' => !empty($course_info['course_end_date']) ? date('F jS, Y', strtotime($course_info['course_end_date'])) : '',
	'hidden' => array(
		'student_id' => $student_id,
		'course_location_id' => $course_location_id,
		'cohort_id' => $cohort_id,
		'next_student_id' => $next_student_id
	),
	'show_past_awards' => true
));
?>

<?php
show_footer();
ob_end_flush();
?>
