<?php ob_start();
error_reporting(0);
session_start();
require(dirname(__FILE__) . '/admin_config.php');
$admin_id = $_SESSION['admin_id'];
if (!$admin_id) {
    header("Location: admin_login.php");
}
$location_id = $_GET['location_id'];
$date = $_GET['date'];
$admin = new Admin();
$students = $admin->get_awards_by_location($location_id,$date);
usort($students, array("Admin", "sort_by_teacher_name_then_student_name"));
require('fpdf153/fpdf.php');
class PDF extends FPDF {
function Header()
{
  $image_ratio = 627 / 475;
  $width = 297;
  $height = 210;
  $this->Image('certificate.png',0, 0, $width, $height);
  $this->SetFont('Arial','',44);
  $this->SetFontSize(44);
}
function body($p) {
	$admin = new Admin();
	$badges = new Badges();
	$student_id = $p['student_id'];
	$badge_username = $badges->get_badge_username($student_id);
// 	$summer_badge_path = $admin->get_student_summer_badge_path($student_id);
	$badge_path = "http://PlanetBravo.com/badges/".$badge_username;
	$award_title = html_entity_decode($p['award_title']);
	$reason = html_entity_decode($p['reason']);
	$reason_array = array();
	if (strlen($reason) > 60) {
		preg_match('/^(.{1,60})\s(.{0,60})/', $reason, $match);
		$reason_array[] = $match[1];
		$reason_array[] = $match[2];
	} else {
		$reason_array[] = $reason;
	}
	$course_location_id = $p['course_location_id'];
	$course_teachers = Award::course_teachers($course_location_id, $p);
	$teacher_full_name = Award::teacher_list_text($course_teachers, 'full');
	$teacher_sign_name = Award::teacher_list_text($course_teachers, 'sign');
	$student_info = $admin->get_student_by_student_id($student_id);
	$student_last_name = $student_info['student_last_name'];
	$student_first_name = $student_info['student_first_name'];
	$student_full_name = $student_first_name . " " . $student_last_name;
	$this->SetY(90);
	$this->SetFont('Arial','B',22);
// 	if ($summer_badge_path) {
	  // $this->Cell(0,10,'                                     The',0,1,'L');
	  // $this->Cell(0,7, "                                   '$award_title' ",0,1,'L');
	  // $this->Cell(0,10,'                                     Award',0,0,'L');
// 	  $this->ImagePngWithAlpha('http://www.PlanetBravo.com/badge/'.$summer_badge_path,172,109,30,20);
// 	}
	$this->Cell(0,10,'The',0,1,'C');
	$this->Cell(0,7," '$award_title' ",0,1,'C');
	$this->Cell(0,10,'Award',0,0,'C');
	$this->Ln(14);
	$this->SetFont('Arial','I',12);
	$this->Cell(0,10,'Presented to:',0,0,'C');
	$this->Ln(8);
	$this->SetFont('Arial','B',28);
	$this->Cell(0,10,$student_full_name,0,0,'C');
	$this->Ln(14);
	$this->SetFont('Arial','I',12);
	$this->Cell(0,10,'For:',0,0,'C');
	$this->Ln(10);
	$this->SetFont('Arial','B',12);
	$this->SetFontSize(12);
	for ($i = 0; $i < count($reason_array); $i++) {
		$reason_line = $reason_array[$i];
		$this->Cell(0,0,$reason_line,0,0,'C');
		$last = count($reason_array) - 1;
		if ($i < $last) {
		  $this->Ln(6);
		}
	}
	if (count($reason_array) > 1) {
	  $this->Ln(3);
	} else {
	  $this->Ln(8);
	}
	$this->SetFont('Arial','',10);
	if ($badge_username)
// 	  $this->Cell(0,4,'visit PlanetBravo.com/badges/'.$badge_username.' to view your accomplishments',0,1,'C',0,$badge_path);
	$this->Ln(-4);
	$this->SetFont('Arial','I',12);
	$this->Cell(0,16,'Issued at:',0,0,'C');
	$this->Ln(10);
	$this->SetFont('Arial','B',12);
	$this->Cell(0,6,'PlanetBravo',0,0,'C');
	$this->SetFont('BulgattiRegular','',strlen($teacher_sign_name) > 28 ? 18 : 24);
	$this->SetY(-38);
	$this->SetX(-162);
	$this->Cell(120,0,$teacher_sign_name,0,0,'R');
	$this->SetFont('Arial','',strlen($teacher_full_name) > 34 ? 9 : 12);
	$this->SetY(-26);
	$this->SetX(-142);
	$this->Cell(120,0,$teacher_full_name,0,0,'R');
	$this->SetY(-21);
	$this->SetX(-142);
	$this->Cell(120,0,'Instructor',0,0,'R');
// 	if ($badge_username)
//	  $this->Image('http://www.PlanetBravo.com/badge/phpqrcode/showimage.php?data='.$badge_username.'.png',237,120,30,30);
	$this->Ln(20);
}
  function Footer() {
	$date = date('F d, Y',$_REQUEST["course_end_date"]);	
	$this->SetY(-40);
	$this->SetFont('Arial','I',12);
	$this->Cell(0,20,'On:',0,0,'C');
	$this->Ln(5);
	$this->SetFont('Arial','B',12);
	$this->SetFontSize(12);
	$this->Cell(0,20,$date,0,0,'C');
  }
}
$pdf=new PDF('L');
$pdf->AliasNbPages();
$pdf->AddFont('BulgattiRegular','','bulgatti_xgmv.php');
foreach ($students as $student) {
  $pdf->AddPage();
  $pdf->body($student);
}
$file = "tmp_pdf/awards_$location_id$date.pdf";
$pdf->Output($file);
echo "<html><head><script>document.location='$file';</script></head></html>";
ob_end_flush();
?>
