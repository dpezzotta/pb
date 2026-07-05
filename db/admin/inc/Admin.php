<?php
if (file_exists(dirname(__FILE__) . '/../../inc/StudentChangeConfirmation.php')) {
    require_once(dirname(__FILE__) . '/../../inc/StudentChangeConfirmation.php');
}
class Admin {
	function authenticate($p) {
		// '=''or'@email.com
		$valid = new Valid();
	  $email = $p['email'];
// IP Blocking Feature
// 	if (0)
// 		if (
//          $_SERVER["REMOTE_ADDR"] != '24.180.27.58'   // Danny
// 			&& $_SERVER["REMOTE_ADDR"] != '172.251.54.151' // Chris
// 			&& $_SERVER["REMOTE_ADDR"] != '68.4.112.160'   // Creighton
// 			&& $_SERVER["REMOTE_ADDR"] != '71.93.159.34'   // Danny
// 			&& $_SERVER["REMOTE_ADDR"] != '47.232.197.17'  // Danny
// 			&& $_SERVER["REMOTE_ADDR"] != '184.102.195.167'  // Danny Mom House
// 			&& $_SERVER["REMOTE_ADDR"] != '41.114.216.140' // Kaal
// 			&& $_SERVER["REMOTE_ADDR"] != '23.243.151.24' // Shanny
// 		) {
// 			$valid->set_error("Invalid IP Address");
// 			$this->set_error($valid->error());
// 			return 0;
// 		}
		if (!$valid->valid_email($email)) {
			$valid->set_error("Please enter a valid email address and/or username");
			$this->set_error($valid->error());
			return 0;
		}
	  $log = $this->log_hit($email);
	  $log_id = $log[0];
	  $log_string = $log[1];
	  $password = $p['password'];
        if (!preg_match('/\@planetbravo\.com$/i', $email)) {
            $email .= '@planetbravo.com';
        }
		$valid = new Valid();
        if (!$valid->valid_email($email)) {
            $valid->set_error("Please enter a valid email address and/or username");
        }
        if ($valid->error) {
            $this->set_error($valid->error());
            return 0;
        }
        /* check that account matches */
        $md5_password = md5($password);
        $connection = connect_database();
        $query = sprintf("SELECT admin_id FROM admin_info WHERE email = '%s' AND password = '%s'", $email, $md5_password);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_assoc($result);
        if ($row['admin_id']) {
	if ($log_id) $this->update_hit($log_id,1);
  $message = "<br/>PlanetBravo: an Administrator logged in<br/><br/>".$log_string;
  $to = "admin@planetbravo.com";
  $subject = "PlanetBravo: an Administrator logged in";
  $headers =
  'From: PlanetBravo <admin@planetbravo.com>' . "\r\n" .
  'Reply-To: PlanetBravo <admin@planetbravo.com>' . "\r\n" .
  'Content-Type: text/html; charset=ISO-8859-1' . "\r\n" .
  'X-Mailer: PHP/' . phpversion();
if ($_SERVER['HTTP_HOST'] == 'www.new.planetbravo.com' || $_SERVER['HTTP_HOST'] == 'new.planetbravo.com' || $_SERVER['HTTP_HOST'] == 'www.planetbravo.com' || $_SERVER['HTTP_HOST'] == 'planetbravo.com')
  mail($to, $subject, $message, $headers);
            return $row['admin_id'];
        } else {
            $valid->set_error("Email address and password do not match");
            $this->set_error($valid->error());
            return 0;
        }
	}
	function authenticate_teacher($p) {
        $email = $p['email'];
        $password = $p['password'];
        $valid = new Valid();
        if (!$valid->valid_email($email)) {
            $valid->set_error("Please enter a valid email address");
        }
        if ($valid->error) {
            $this->set_error($valid->error());
            return 0;
        }
        /* check that account matches */
        $md5_password = md5($password);
        $connection = connect_database();
        $query = sprintf("SELECT teacher_id FROM teacher WHERE email = '%s' AND password = '%s'", $email, $md5_password);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_assoc($result);
        if ($row['teacher_id']) {
            return $row['teacher_id'];
        } else {
            $valid->set_error("Email address and password do not match");
            $this->set_error($valid->error());
            return 0;
        }
    }
	function view_accounts($order_by = "registrar_last_name") {
		$accounts = array();
        $connection = connect_database();
	$query = sprintf("SELECT account_id, registrar_first_name, registrar_last_name, street_address, city, state, zip, best_number, secondary_cell_phone, owner_name, home_phone, work_phone, cell_phone, fax_phone, email, password, security_question, security_question_text, security_answer, date_of_creation, receive_email, receive_brochure, notes FROM account_info ORDER BY $order_by");
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$accounts[] = array(
				'account_id' => $row['account_id'],
				'registrar_first_name' => $row['registrar_first_name'],
				'registrar_last_name' => $row['registrar_last_name'],
				'street_address' => $row['street_address'],
				'city' => $row['city'],
				'state' => $row['state'],
				'zip' => $row['zip'],
				'best_number' => $row['best_number'],
				'secondary_cell_phone' => $row['secondary_cell_phone'],
				'owner_name' => $row['owner_name'],
				'home_phone' => $row['home_phone'],
				'work_phone' => $row['work_phone'],
				'cell_phone' => $row['cell_phone'],
				'fax_phone' => $row['fax_phone'],
				'email' => $row['email'],
				'receive_email' => $row['receive_email'],
				'receive_brochure' => $row['receive_brochure'],
			);			
		}
		return $accounts;
	}
	function view_account($account_id) {
		$account = array();
		$connection = connect_database();
        $query = sprintf("SELECT account_id, registrar_first_name, registrar_last_name, street_address, city, state, zip, best_number, secondary_cell_phone, owner_name, home_phone, work_phone, cell_phone, fax_phone, email, secondary_email, password, security_question, security_question_text, security_answer, date_of_creation, how_hear, receive_email, receive_brochure, notes, date_of_login FROM account_info WHERE account_id = '%s'", $account_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $account = array(
                'account_id' => $row['account_id'],
                'registrar_first_name' => $row['registrar_first_name'],
                'registrar_last_name' => $row['registrar_last_name'],
                'street_address' => $row['street_address'],
                'city' => $row['city'],
                'state' => $row['state'],
                'zip' => $row['zip'],
		'best_number' => $row['best_number'],
		'secondary_cell_phone' => $row['secondary_cell_phone'],
		'owner_name' => $row['owner_name'],
                'home_phone' => $row['home_phone'],
                'work_phone' => $row['work_phone'],
                'cell_phone' => $row['cell_phone'],
                'fax_phone' => $row['fax_phone'],
                'email' => $row['email'],
                'secondary_email' => $row['secondary_email'],
				'security_question' => $row['security_question'],
				'security_question_text' => $row['security_question_text'],
				'security_answer' => $row['security_answer'],
				'how_hear' => $row['how_hear'],
                'receive_email' => $row['receive_email'],
                'receive_brochure' => $row['receive_brochure'],
				'notes' => $row['notes'],
				'date_of_login' => $row['date_of_login']
            );
        }
        return $account;
	}
	function update_account($p) {
		$account_id = $p['account_id'];
		$registrar_first_name = $p['registrar_first_name'];
		$registrar_last_name = $p['registrar_last_name'];
		$street_address = $p['street_address'];
		$city = $p['city'];
		$state = $p['state'];
		$zip = $p['zip'];
		$home_phone = $p['home_phone'];
		$work_phone = $p['work_phone'];
		$cell_phone = $p['cell_phone'];
		$secondary_cell_phone = $p['secondary_cell_phone'];
		$best_number = $p['best_number'];
		$owner_name = $p['owner_name'];
		$fax_phone = $p['fax_phone'];
		$email = $p['email'];
		$secondary_email = $p['secondary_email'];
		$password = $p['password'];
		$security_question = $p['security_question'];
		$security_question_text = $p['security_question_text'];
		$security_answer = $p['security_answer'];
		$receive_email = $p['receive_email'];
		$receive_brochure = $p['receive_brochure'];
		$notes = $p['notes'];
		if (preg_match('/\w+/', $security_question_text, $match)) {
			$security_question = "";
		}
        $connection = connect_database();
		if (!preg_match('/\w+/', $password, $match)) {
			$query = sprintf("UPDATE account_info SET registrar_first_name = '%s', registrar_last_name = '%s', street_address = '%s', city = '%s', state = '%s', zip = '%s', home_phone = '%s', work_phone = '%s', cell_phone = '%s', best_number = '%s', secondary_cell_phone = '%s', owner_name = '%s', fax_phone = '%s', email = '%s', secondary_email = '%s', security_question = '%s', security_question_text = '%s', security_answer = '%s', receive_email = '%s', receive_brochure = '%s', notes = '%s' WHERE account_id = '%s'", $registrar_first_name, $registrar_last_name, $street_address, $city, $state, $zip, $home_phone, $work_phone, $cell_phone, $best_number, $secondary_cell_phone, $owner_name, $fax_phone, $email, $secondary_email, $security_question, $security_question_text, $security_answer, $receive_email, $receive_brochure, $notes, $account_id);
		} else {
			$query = sprintf("UPDATE account_info SET registrar_first_name = '%s', registrar_last_name = '%s', street_address = '%s', city = '%s', state = '%s', zip = '%s', home_phone = '%s', work_phone = '%s', cell_phone = '%s', best_number = '%s', secondary_cell_phone = '%s', owner_name = '%s', fax_phone = '%s', email = '%s', secondary_email = '%s', password = md5('%s'), security_question = '%s', security_question_text = '%s', security_answer = '%s', receive_email = '%s', receive_brochure = '%s', notes = '%s' WHERE account_id = '%s'", $registrar_first_name, $registrar_last_name, $street_address, $city, $state, $zip, $home_phone, $work_phone, $cell_phone, $best_number, $secondary_cell_phone, $owner_name, $fax_phone, $email, $secondary_email, $password, $security_question, $security_question_text, $security_answer, $receive_email, $receive_brochure, $notes, $account_id);
		}
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		return;
	}
	function add_account($p) {
		$account_id = $this->create_account_id();
		$registrar_first_name = $p['registrar_first_name'];
        $registrar_last_name = $p['registrar_last_name'];
        $street_address = $p['street_address'];
        $city = $p['city'];
        $state = $p['state'];
        $zip = $p['zip'];
        $home_phone = $p['home_phone'];
        $work_phone = $p['work_phone'];
        $cell_phone = $p['cell_phone'];
        $fax_phone = $p['fax_phone'];
        $email = $p['email'];
        $password = $p['password'];
        $security_question = $p['security_question'];
        $security_question_text = $p['security_question_text'];
        $security_answer = $p['security_answer'];
        $receive_email = $p['receive_email'];
        $receive_brochure = $p['receive_brochure'];
        $notes = $p['notes'];
        if (preg_match('/\w+/', $security_question_text, $match)) {
            $security_question = "";
        }
        $connection = connect_database();
		$query = sprintf("INSERT INTO account_info (account_id, registrar_first_name, registrar_last_name, street_address, city, state, zip, home_phone, work_phone, cell_phone, fax_phone, email, password, security_question, security_question_text, security_answer, date_of_creation, receive_email, receive_brochure, notes) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', md5('%s'), '%s', '%s', '%s', NOW(), '%s', '%s', '%s')", $account_id, $registrar_first_name, $registrar_last_name, $street_address, $city, $state, $zip, $home_phone, $work_phone, $cell_phone, $fax_phone, $email, $password, $security_question, $security_question_text, $security_answer, $receive_email, $receive_brochure, $notes);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		return $account_id;
	}
	function view_students($order_by = "student_last_name") {
		if (preg_match('/grade_level/', $order_by)) {
			$order_by = "student_last_name";
		}
		$students = array();
        $connection = connect_database();
		$query = sprintf("SELECT student_id, account_id, student_first_name, student_last_name, date_of_birth, gender, pronoun, emerg_name, emerg_phone, allergies, photo, t_shirt_size, friend_to_be_grouped_with FROM student_info ORDER BY $order_by");
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name']);
			$row['student_last_name'] = ucfirst($row['student_last_name']);
			$students[] = $row;
		}
		return $students;
	}
	function view_students_by_surname($surname="A",$start=0,$end=50) {
	  $students = array();
	  $connection = connect_database();
	  $query = "
	    SELECT 
	      student_id, 
	      account_id, 
	      student_first_name, 
	      student_last_name, 
	      date_of_birth, 
	      gender, 
	      emerg_name, 
	      emerg_phone, 
	      allergies, 
	      photo, 
	      t_shirt_size, 
	      friend_to_be_grouped_with 
	    FROM 
	      student_info 
	    where
	      (student_last_name like '".strtoupper($surname)."%'
		or
	      student_last_name like '".strtolower($surname)."%')
	    ORDER BY 
	      student_last_name asc
	    limit
		$start,$end
	    ";
	  $result = mysqli_query($connection,$query);
	  if (!$result) {
            die(mysqli_error($connection));
	  }
	  while ($row = mysqli_fetch_assoc($result)) {
	    $row['student_first_name'] = ucfirst($row['student_first_name']);
	    $row['student_last_name'] = ucfirst($row['student_last_name']);
	    $students[] = $row;
	  }
	  return $students;
	}
	function get_student_count_by_surname($surname="A") {
	  $students = array();
	  $connection = connect_database();
	  $query = "
	    SELECT 
	      count(student_id) as count
	    FROM 
	      student_info 
	    where
	      student_last_name like '".strtoupper($surname)."%'
		or
	      student_last_name like '".strtolower($surname)."%'
	    ";
	  $result = mysqli_query($connection,$query);
	  if (!$result) {
            die(mysqli_error($connection));
	  }
	  while ($row = mysqli_fetch_assoc($result)) {
	    $count = $row['count'];
	  }
	  return $count;
	}
	function get_students_by_account_id($account_id) {
		$students = array();
        $connection = connect_database();
		$query = sprintf("SELECT student_id, student_first_name, student_last_name FROM student_info WHERE account_id = '%s'", $account_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name']);
			$row['student_last_name'] = ucfirst($row['student_last_name']);
			$students[] = $row;
		}
		return $students;
	}
	function get_student_by_student_id($student_id) {
		$student = array();
        $connection = connect_database();
        $query = sprintf("SELECT app, staff_notes, student_info.ecl_balance, student_info.online_balance, student_info.page, student_info.account_id, registrar_first_name, registrar_last_name, student_id, student_first_name, student_last_name, camper_image, account_info.email, school, cteacher, room, uc_waiver, xtd, date_of_birth, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, friend_to_be_grouped_with, hero_reason, hero_url, camper_image as hero_image FROM student_info, account_info WHERE student_id = '%s' AND student_info.account_id = account_info.account_id", $student_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $student['account_id'] = $row['account_id'];
            $student['registrar_first_name'] = $row['registrar_first_name'];
            $student['registrar_last_name'] = $row['registrar_last_name'];
            $student['student_id'] = $row['student_id'];
            $student['student_first_name'] = ucfirst($row['student_first_name']);
            $student['student_last_name'] = ucfirst($row['student_last_name']);
            $student['camper_image'] = $row['camper_image'];
            $student['email'] = $row['email'];
            $student['staff_notes'] = $row['staff_notes'];
            $student['school'] = $row['school'];
            $student['cteacher'] = $row['cteacher'];
            $student['room'] = $row['room'];
	    $student['uc_waiver'] = $row['uc_waiver'];
	    $student['xtd'] = $row['xtd'];
            $student['date_of_birth'] = $row['date_of_birth'];
            $student['gender'] = $row['gender'];
            $student['pronoun'] = $row['pronoun'];
            $student['emerg_name'] = $row['emerg_name'];
            $student['emerg_phone'] = $row['emerg_phone'];
            $student['app'] = $row['app'];
            $student['allergies'] = $row['allergies'];
            $student['t_shirt_size'] = $row['t_shirt_size'];
            $student['friend_to_be_grouped_with'] = $row['friend_to_be_grouped_with'];
	    $student['hero_reason'] = $row['hero_reason'];
            $student['hero_url'] = $row['hero_url'];
            $student['hero_image'] = $row['hero_image'];
            $student['page'] = $row['page'];
            $student['ecl_balance'] = $row['ecl_balance'];
            $student['online_balance'] = $row['online_balance'];
        }
        return $student;
	}
	
	
		function get_student_by_student_photo($student_id) {
		$student = array();
        $connection = connect_database();
        $query = sprintf("SELECT student_info.account_id, registrar_first_name, registrar_last_name, student_id, student_first_name, student_last_name, camper_image FROM student_info, account_info WHERE student_id = '%s' AND student_info.account_id = account_info.account_id", $student_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $student['account_id'] = $row['account_id'];
            $student['registrar_first_name'] = $row['registrar_first_name'];
            $student['registrar_last_name'] = $row['registrar_last_name'];
            $student['student_id'] = $row['student_id'];
            $student['student_first_name'] = ucfirst($row['student_first_name']);
            $student['student_last_name'] = ucfirst($row['student_last_name']);
            $student['camper_image'] = $row['camper_image'];
           
        }
        return $student;
	}
	function update_student($p) {
    $connection = connect_database();
    if (!empty($p['location'])) $in_course_location = mysqli_escape_string($connection,$p['location']);
		$student_id = mysqli_escape_string($connection,$p['student_id']);
		$change_before = array();
		if (function_exists('pb_student_change_capture_snapshot')) {
			$change_before = pb_student_change_capture_snapshot($student_id);
		}
		$student_first_name = mysqli_escape_string($connection,$this->make_proper_case($p['student_first_name']));
		$student_last_name = mysqli_escape_string($connection,$this->make_proper_case_last_name($p['student_last_name']));
		$email = mysqli_escape_string($connection,$p['email']);
    $school = mysqli_escape_string($connection,$p['school']);
    $cteacher = mysqli_escape_string($connection,$p['cteacher']);
    $room = mysqli_escape_string($connection,$p['room']);
    $uc_waiver = mysqli_escape_string($connection,$p['uc_waiver']);
    $xtd = mysqli_escape_string($connection,$p['xtd']);
       if (!empty($p['gender']))
    $gender = mysqli_escape_string($connection,$p['gender']);
       if (!empty($p['pronoun']))
    $pronoun = mysqli_escape_string($connection,$p['pronoun']);
		$emerg_name = mysqli_escape_string($connection,$p['emerg_name']);
		$app = mysqli_escape_string($connection,$p['app']);
		$emerg_phone = mysqli_escape_string($connection,$p['emerg_phone']);
		$allergies = mysqli_escape_string($connection,$p['allergies']);
		$t_shirt_size = mysqli_escape_string($connection,$p['t_shirt_size']);
		$friend_to_be_grouped_with = mysqli_escape_string($connection,$p['friend_to_be_grouped_with']);
		$account_id_temp = mysqli_escape_string($connection,$p['account_id']);
       if (!empty($p['notes']))
		$notes = mysqli_escape_string($connection,$p['notes']);
    $staff_notes = mysqli_escape_string($connection,$p['staff_notes']);
		$grade_level = mysqli_escape_string($connection,$p['grade_level']);
		$hero = mysqli_escape_string($connection,$p['hero']);
		$hero_reason = mysqli_escape_string($connection,$p['hero_reason']);
		$hero_url = mysqli_escape_string($connection,$p['hero_url']);
		$date_of_birth = mysqli_escape_string($connection,sprintf("%04d-%02d-%02d", $p['birth_year'], $p['birth_month'], $p['birth_day']));
		$posted_courses = function_exists('pb_student_change_capture_posted_courses') ? pb_student_change_capture_posted_courses($p) : array();
		$course_changes_requested = true;
		if (function_exists('pb_student_change_courses_match')) {
			$course_changes_requested = !pb_student_change_courses_match(!empty($change_before['courses']) ? $change_before['courses'] : array(), $posted_courses);
		}
		if (!$course_changes_requested) {
			foreach ($p as $posted_key => $posted_value) {
				if (!preg_match('/^course_(\d+)$/', $posted_key, $posted_match)) continue;
				$slot = (int)$posted_match[1];
				$course_location_id_for_ec = (int)$posted_value;
				if ($course_location_id_for_ec <= 0) continue;
				$posted_extended_care = !empty($p["course_ec_$slot"]) ? 1 : 0;
				$query_ec = "SELECT extended_care FROM course_registration WHERE student_id = '$student_id' AND course_location_id = '$course_location_id_for_ec' LIMIT 0,1";
				$result_ec = mysqli_query($connection,$query_ec);
				$row_ec = $result_ec ? mysqli_fetch_assoc($result_ec) : array();
				$current_extended_care = !empty($row_ec['extended_care']) ? 1 : 0;
				if ($posted_extended_care != $current_extended_care) {
					$course_changes_requested = true;
					break;
				}
			}
		}
		$query = sprintf("UPDATE student_info SET app = '%s', student_first_name = '%s', student_last_name = '%s', email = '%s', school = '%s', cteacher = '%s', room = '%s', uc_waiver = '%s', xtd = '%s', date_of_birth = '%s', gender = '%s', pronoun = '%s', emerg_name = '%s', emerg_phone = '%s', allergies = '%s', t_shirt_size = '%s', friend_to_be_grouped_with = '%s', account_id = '%s', notes = '%s', staff_notes = '%s', hero_reason = '%s', hero_url = '%s' WHERE student_id = '%s'", $app, $student_first_name, $student_last_name, $email, $school, $cteacher, $room, $uc_waiver, $xtd, $date_of_birth, $gender, $pronoun, $emerg_name, $emerg_phone, $allergies, $t_shirt_size, $friend_to_be_grouped_with, $account_id_temp, $notes, $staff_notes, $hero_reason, $hero_url, $student_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		$Hero = new Hero();
		if ($hero == 'y') {
			$Hero->set(
				array(
					'student_id' => $student_id
				)
			);
		} else {
			if ($Hero->is_hero($student_id)) {
				$Hero->unset_hero($student_id);
			}	
		}
        $current_year = CURRENT_YEAR;;
		$query = sprintf("SELECT student_grade_id FROM student_grade WHERE student_id = '%s' AND grade_year = '%d'", $student_id, $current_year);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		$row = mysqli_fetch_assoc($result);
		if ($row['student_grade_id']) {
			$query = sprintf("UPDATE student_grade SET grade_level = '%s' WHERE student_grade_id = '%s'", $grade_level, $row['student_grade_id']);
		} else {
			$query = sprintf("INSERT INTO student_grade (student_id, grade_year, grade_level) VALUES ('%s', '%s', '%s')", $student_id, $current_year, $grade_level);
		}
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		$query = sprintf("DELETE FROM student_interest WHERE student_id = '%s'", $student_id);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		/* go through interests */
        $interests = array();
        foreach ($p as $key => $value) {
            if (preg_match('/^interests_(\d+)$/', $key, $match)) {
                /* is it checked? */
                if ($value == 'y') {
                    $interests[] = $match[1];
                }
            }
        }
        foreach ($interests as $interest_id) {
            $query = sprintf("INSERT INTO student_interest (student_id, interest_id) VALUES ('%s', '%s')", $student_id, $interest_id);
            if (!mysqli_query($connection,$query)) {
                die(mysqli_error($connection));
                return 0;
            }
        }	
		if ($course_changes_requested) {
			// get course_registration status
			$course_status = array();
			$query = sprintf("SELECT course_location_id, status FROM course_registration WHERE student_id = '%s'", $student_id);
			$result = mysqli_query($connection,$query);
		    if (!$result) {
		        die(mysqli_error($connection));
		    }
			while ($row = mysqli_fetch_assoc($result)) {
				$s_course_location_id = $row['course_location_id'];
				$s_status = $row['status'];
				$course_status[$s_course_location_id] = $s_status;
			}
			// Student History Fix
		  list($year, $month, $day) = explode("-", date('Y-m-d'));
		  if ($month < 12) {
		    $previous_year = $year-1;
		  } else {
		    $previous_year = $year;
		    $year ++;
		  }	  

	    $query = "
	  Select 
	    course_registration_id 
	  FROM 
	    course_registration,
	    course_location
	  WHERE 
	    student_id = '$student_id'
	  and 
	    course_location.course_start_date > '$previous_year-12-01 00:00:00' 
	  and
	    course_location.course_location_id = course_registration.course_location_id
";	  
	    $course_registration_ids = "";
			$result = mysqli_query($connection,$query);
			while ($row = mysqli_fetch_assoc($result)) {
	      $course_registration_ids .= $row["course_registration_id"].",";
			}
	    $course_registration_ids = substr($course_registration_ids,0,strlen($course_registration_ids)-1);
	    $registration_dates_by_course_location = array();
	    $registration_dates_by_slot = array();
	    if (!empty($course_registration_ids)) {
	      $query = "select course_location_id, registration_date from course_registration where course_registration_id in ($course_registration_ids)";
	      $result = mysqli_query($connection,$query);
	      $x = 1;
	      while ($row = mysqli_fetch_assoc($result)) {
	        $registration_dates_by_course_location[$row['course_location_id']] = $row['registration_date'];
	        $registration_dates_by_slot[$x] = $row['registration_date'];
	        $x++;
	      }
	    }
	    $query = "DELETE FROM course_registration WHERE course_registration.course_registration_id in ($course_registration_ids)";
	//     echo $query;
	//     exit;
	    if ($course_registration_ids) {
	      $result = mysqli_query($connection,$query);
	      if (!$result) die(mysqli_error($connection));
	    }
			/* go through courses */
			$courses = array();
		
			foreach ($p as $key => $value) {
	            if (preg_match('/^course_(\d+)$/', $key)) {
	            	$courses[] = $value;
	            }
			}
			foreach ($courses as $key_2 => $course_location_id) {
				if ($course_location_id > 0) {
					$status = 'p';
					// lookup status
					if (array_key_exists($course_location_id, $course_status)) {
						$status = $course_status[$course_location_id];
					}
					$key_2 ++;
					$late_cancelled = !empty($p["late_cancelled_$key_2"])?$p["late_cancelled_$key_2"]:0;
					$pb_picks = !empty($p["pbpicks_$key_2"])?$p["pbpicks_$key_2"]:0;
					$in_course_location_id = !empty($p["incourse_location_$key_2"])?$p["incourse_location_$key_2"]:0;
					$rent_laptop_value = !empty($p["rent_laptop_$key_2"])?$p["rent_laptop_$key_2"]:0;
					$cohort_id_value = !empty($p["cohort_id_$key_2"])?$p["cohort_id_$key_2"]:0;
					$teacher_id_value = !empty($p["teacher_id_$key_2"])?$p["teacher_id_$key_2"]:0;
	                if (!empty($registration_dates_by_slot[$key_2])) {
	                  $registration_date_sql = "'" . mysqli_real_escape_string($connection, $registration_dates_by_slot[$key_2]) . "'";
	                } else if (!empty($registration_dates_by_course_location[$course_location_id])) {
	                  $registration_date_sql = "'" . mysqli_real_escape_string($connection, $registration_dates_by_course_location[$course_location_id]) . "'";
	                } else {
	                  $registration_date_sql = "NOW()";
	                }
					
					$query = "INSERT INTO course_registration (rent_laptop, cohort_id, teacher_id, location, student_id, course_location_id, status, registration_date, late_cancelled, pb_picks) VALUES ('$rent_laptop_value', '$cohort_id_value', '$teacher_id_value', '$in_course_location_id', '$student_id', '$course_location_id', '$status', ".$registration_date_sql.", '$late_cancelled', '$pb_picks')";
					
	//     echo $query."<br/>";
	//     exit;
					if (!mysqli_query($connection,$query)) {
	                	die(mysqli_error($connection));
	                	return 0;
	            	}
				}
				
			}		

	// 			exit;
	    // Kaal
	    // Extended Care Update
	    // 31 December 2019
			foreach ($courses as $key_2 => $course_location_id) {
				if ($course_location_id <= 0) continue;
				$slot = $key_2 + 1;
				$extended_care_selected = !empty($p["course_ec_$slot"]) ? 1 : 0;
				if ($extended_care_selected) {
					$extended_care_price = $this->get_extended_care_price($course_location_id);
					if ($extended_care_price > 0) {
						$ec_price = !empty($p["course_ec_$slot"]) ? (float)$p["course_ec_$slot"] : $extended_care_price;
						$ecl_balance = $ec_price - $extended_care_price;
						$query_1 = "update course_registration set extended_care = 1 where student_id = '$student_id' and course_location_id = '$course_location_id'";
						$query_2 = "update student_info set ecl_balance = ecl_balance + $ecl_balance where student_id = '$student_id'";
						mysqli_query($connection,$query_1);
						mysqli_query($connection,$query_2);
					}
				} else {
					$query_1 = "update course_registration set extended_care = 0 where student_id = '$student_id' and course_location_id = '$course_location_id'";
					mysqli_query($connection,$query_1);
				}
			}
			$courses_l = array();
			foreach ($p as $key => $value) {
	      if (preg_match('/^course_l_(\d+)$/', $key)) {
	        $courses_l[] = $value;
	      }
			}
			foreach ($courses_l as $key_2 => $l_price) {
	      $lunch_price = $this->get_lunch_price($courses[$key_2]);
	      $ecl_balance = $l_price - $lunch_price;
	      $query_1 = "update course_registration set lunch = 1 where student_id = '$student_id' and course_location_id = '".$courses[$key_2]."'";
	      $query_2 = "update student_info set ecl_balance = ecl_balance + $ecl_balance where student_id = '$student_id'";
	      mysqli_query($connection,$query_1);
	      mysqli_query($connection,$query_2);
			}
			// //////////////////////
			// Online Update
			// 22 March 2020
	    $courses_online = array();
			foreach ($p as $key => $value) {
	      if (preg_match('/^course_online_(\d+)$/', $key)) {
	        $courses_online[] = $value;
	      }
			}
			foreach ($courses_online as $key_2 => $online_price) {
	      $o_price = $this->get_online_price($courses[$key_2]);
	      $online_balance = $online_price - $o_price;
	      $query_1 = "update course_registration set online = 1 where student_id = '$student_id' and course_location_id = '".$courses[$key_2]."'";
	      $query_2 = "update student_info set online_balance = online_balance + $online_balance where student_id = '$student_id'";
	      mysqli_query($connection,$query_1);
	      mysqli_query($connection,$query_2);
			}
		}
		// //////////////////////
	$sql_1 = 'delete from `badges_student` where `student_id` = '.$_REQUEST["student_id"];
	$sql_2 = 'insert into `badges_student` (`student_id`,`badge_id`) values ';
	$sql_3 = 'update `student_info` set `page` = "'.$_REQUEST["page"].'" where `student_id` = '.$_REQUEST["student_id"];
    if (!empty($_REQUEST["badge"])) $count = count($_REQUEST["badge"]);
	if ($count ==1) {
	  foreach ($_REQUEST["badge"] as $key=>$value) {
	    $sql_2 .= '('.$student_id.','.$key.')';
	  }
	} else {
	  $x=0;
	  foreach ($_REQUEST["badge"] as $key=>$value) {
	    $x++;
	    if ($x==$count)
	      $sql_2 .= '('.$student_id.','.$key.')';
	    else
	      $sql_2 .= '('.$student_id.','.$key.'),';
	  }
	}
	// if (mysqli_query($connection,$sql_1)) mysqli_query($connection,$sql_2);
	mysqli_query($connection,$sql_3);
		if ($course_changes_requested && function_exists('pb_student_change_record_event')) {
			$change_after = pb_student_change_capture_snapshot($student_id);
			$change_id = pb_student_change_record_event($change_before, $change_after, 'student_update', 'admin_edit_student_details.php', isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0, 0);
			$_SESSION['student_change_modal'] = array(
				'student_id' => $student_id,
				'change_id' => $change_id,
				'account_id' => !empty($change_after['student']['account_id']) ? $change_after['student']['account_id'] : '',
				'student_name' => trim($change_after['student']['student_first_name'] . ' ' . $change_after['student']['student_last_name']),
				'account_name' => trim($change_after['student']['registrar_first_name'] . ' ' . $change_after['student']['registrar_last_name']),
				'change_timestamp' => date('M j, Y g:ia'),
				'summary_lines' => function_exists('pb_student_change_summary_lines') ? pb_student_change_summary_lines(pb_student_change_diff($change_before, $change_after)) : array(),
				'view_url' => "admin_view_student_details.php?student_id=$student_id",
				'edit_url' => "admin_edit_student_details.php?student_id=$student_id",
			);
			header("Location: admin_edit_student_details.php?student_id=$student_id");
			exit;
		}
	}
	function get_extended_care_price($clid) {
    if (!$clid) return 0;
    $sql = "select extended_care_price from location where location_id = (select location_id from course_location where course_location_id = $clid)";
    $connection = connect_database();
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    return $row["extended_care_price"];
	}
	function get_lunch_price($clid) {
    if (!$clid) return 0;
    $sql = "select lunch_price from location where location_id = (select location_id from course_location where course_location_id = $clid)";
    $connection = connect_database();
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    return $row["lunch_price"];
	}
	function get_online_price($clid) {
    if (!$clid) return 0;
    $sql = "select course_online_cost from course_location where course_location_id = $clid";
    $connection = connect_database();
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    return $row["course_online_cost"];
	}
	function zero_ecl_balance($student_id) {
    if (!$student_id) return 0;
    $sql = "update student_info set ecl_balance = 0 where student_id = '$student_id'";
    $connection = connect_database();
    if (mysqli_query($connection,$sql))
      return 1;
    else
      return 0;
	}
	function zero_online_balance($student_id) {
    if (!$student_id) return 0;
    $sql = "update student_info set online_balance = 0 where student_id = '$student_id'";
    $connection = connect_database();
    if (mysqli_query($connection,$sql))
      return 1;
    else
      return 0;
	}
	function get_extended_care_count($location_id) {
    $query_1 = "
      select 
        count(distinct(course_registration_id)) as count_result 
      from 
        course_registration,
        course_location
      where 
        extended_care = 1 
      and 
        course_location.course_location_id in (
          select course_location.course_location_id from course_location where location_id = $location_id
        )
    ";
//     echo $query_1;
//     exit;
    $connection = connect_database();
    $result = mysqli_query($connection,$query_1);
    $row = mysqli_fetch_assoc($result);
    return $row["count_result"];
	}
	function update_student_photo($p) {
		$student_id = $p['student_id'];
		$student_first_name = $this->make_proper_case($p['student_first_name']);
		$student_last_name = $this->make_proper_case_last_name($p['student_last_name']);
        $connection = connect_database();
		$query = sprintf("UPDATE student_info SET student_first_name = '%s', student_last_name = '%s' WHERE student_id = '%s'", $student_first_name, $student_last_name, $student_id);
        $result = mysqli_query($connection,$query);
	}	
	function add_student($p) {
		$student_id = $this->create_student_id();
		$account_id = $p['account_id'];
        $student_first_name = $this->make_proper_case($p['student_first_name']);
        $student_last_name = $this->make_proper_case_last_name($p['student_last_name']);
        $email = $p['email'];
        $gender = $p['gender'];
        $emerg_name = $p['emerg_name'];
        $emerg_phone = $p['emerg_phone'];
        $allergies = $p['allergies'];
        $t_shirt_size = $p['t_shirt_size'];
        $friend_to_be_grouped_with = $p['friend_to_be_grouped_with'];
        $notes = $p['notes'];
		$date_of_birth = sprintf("%04d-%02d-%02d", $p['birth_year'], $p['birth_month'], $p['birth_day']);
		$grade_level = $p['grade_level'];
		$school = $p['school'];
		$cteacher = $p['cteacher'];
		$room = $p['room'];
		$pronoun = $p['pronoun'];

        $connection = connect_database();
		$query = sprintf("INSERT INTO student_info (student_id, account_id, student_first_name, student_last_name, email, date_of_birth, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, friend_to_be_grouped_with, notes, school, cteacher, room) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $student_id, $account_id, $student_first_name, $student_last_name, $email, $date_of_birth, $gender, $pronoun, $emerg_name, $emerg_phone, $allergies, $t_shirt_size, $friend_to_be_grouped_with, $notes, $school, $cteacher, $room);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		/* go through interests */
        $interests = array();
        foreach ($p as $key => $value) {
            if (preg_match('/^interests_(\d+)$/', $key, $match)) {
                /* is it checked? */
                if ($value == 'y') {
                    $interests[] = $match[1];
                }
            }
        }
		foreach ($interests as $interest_id) {
            $query = sprintf("INSERT INTO student_interest (student_id, interest_id) VALUES ('%s', '%s')", $student_id, $interest_id);
            if (!mysqli_query($connection,$query)) {
                die(mysqli_error($connection));
                return 0;
            }
        }
		if ($grade_level) {
           //$current_year = 2009;
	   $current_year = CURRENT_YEAR;;
//         $current_year = $this->get_current_year();
           $query = sprintf("INSERT INTO student_grade (student_id, grade_year, grade_level) VALUES ('%s', '%s', '%s')", $student_id, $current_year, $grade_level);
            if (!mysqli_query($connection,$query)) {
                die(mysqli_error($connection));
                return 0;
            }
        }
		return $student_id;
	}
	function sort_by_grade_level($a, $b) {
		if ($a['grade_level'] == $b['grade_level']) {
			return 0;
		}
		return ($a['grade_level'] < $b['grade_level']) ? -1 : 1;
	}
	function get_students_by_course_location_id($course_location_id, $order_by = "location, grade_level") {
		$unique_students = array();
		$students = array();
		if (preg_match('/location, grade_level/', $order_by)) {
			$connection = connect_database();
            $query = sprintf("SELECT online, extended_care, school, seating_order, pizza_preference, course_registration_id, student_info.student_id, student_first_name, student_last_name, camper_image, email, date_of_birth, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, friend_to_be_grouped_with, status, grade_level FROM student_info, course_registration, student_grade WHERE course_location_id = '%s' AND course_registration.student_id = student_info.student_id AND course_registration.student_id = student_grade.student_id ORDER BY location asc, date_of_birth ASC", $course_location_id);
            $result = mysqli_query($connection,$query);
            if (!$result) {
                die(mysqli_error($connection));
            }
            while ($row = mysqli_fetch_assoc($result)) {
				$student_id = $row['student_id'];
				if (!array_key_exists($student_id, $unique_students)) {
					$row['student_first_name'] = ucfirst($row['student_first_name']);
					$row['student_last_name'] = ucfirst($row['student_last_name']);
					$students[] = $row;
					$unique_students[$student_id] = 1;
				}
            }
			return $students;
		} else if (preg_match('/grade_level/', $order_by)) {
        	$connection = connect_database();
		//$query = sprintf("SELECT seating_order, pizza_preference, course_registration_id, student_info.student_id, student_first_name, student_last_name, email, date_of_birth, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, friend_to_be_grouped_with, status, grade_level FROM student_info, course_registration, student_grade WHERE course_location_id = '%s' AND course_registration.student_id = student_info.student_id AND course_registration.student_id = student_grade.student_id ORDER BY grade_level ASC", $course_location_id);
		$query = sprintf("SELECT extended_care, school, seating_order, pizza_preference, course_registration_id, student_info.student_id, student_first_name, student_last_name, email, date_of_birth, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, friend_to_be_grouped_with, status, grade_level FROM student_info, course_registration, student_grade WHERE course_location_id = '%s' AND course_registration.student_id = student_info.student_id AND course_registration.student_id = student_grade.student_id ORDER BY grade_level ASC", $course_location_id);
		$result = mysqli_query($connection,$query);
        	if (!$result) {
            	die(mysqli_error($connection));
        	}
        	while ($row = mysqli_fetch_assoc($result)) {
				$student_id = $row['student_id'];
				if (!array_key_exists($student_id, $unique_students)) {
					$row['student_first_name'] = ucfirst($row['student_first_name']);
					$row['student_last_name'] = ucfirst($row['student_last_name']);
            		$students[] = $row;
					$unique_students[$student_id] = 1;
				}
        	}
			for ($i = 0; $i < count($students); $i++) {
				$student_id = $students[$i]['student_id'];
				$grade_level = $this->get_grade_level_by_current_year($student_id);
				$students[$i]['grade_level'];
			}
			return $students;
		} else {
		$connection = connect_database();
		$query = sprintf("SELECT extended_care, school, seating_order, pizza_preference, course_registration_id, student_info.student_id, student_first_name, student_last_name, camper_image, email, date_of_birth, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, friend_to_be_grouped_with, status, grade_level FROM student_info, course_registration, student_grade WHERE course_location_id = '%s' AND course_registration.student_id = student_info.student_id AND course_registration.student_id = student_grade.student_id ORDER BY $order_by ASC, date_of_birth ASC", $course_location_id);
		$result = mysqli_query($connection,$query);
		if (!$result) {
		  die(mysqli_error($connection));
		}
		while ($row = mysqli_fetch_assoc($result)) {
				$student_id = $row['student_id'];
				if (!array_key_exists($student_id, $unique_students)) {
					$row['student_first_name'] = ucfirst($row['student_first_name']);
					$row['student_last_name'] = ucfirst($row['student_last_name']);
					$students[] = $row;
					$unique_students[$student_id] = 1;
				}
            }
			return $students;
	    }

        $connection = connect_database();
        $query = sprintf("SELECT extended_care, school, seating_order, pizza_preference, course_registration_id, student_info.student_id, student_first_name, student_last_name, email, date_of_birth, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, friend_to_be_grouped_with, status FROM student_info, course_registration WHERE course_location_id = '%s' AND course_registration.student_id = student_info.student_id ORDER BY $order_by", $course_location_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name']);
			$row['student_last_name'] = ucfirst($row['student_last_name']);
			$students[] = $row;
		}
		return $students;
	}
	function get_courses_by_student_id($student_id) {
		$courses = array();
        $connection = connect_database();
		$query = sprintf("SELECT course_registration.location, online, course_online_cost, course_registration.extended_care, extended_care_price, lunch, lunch_price, course_registration.late_cancelled, course_registration.pb_picks, school_name, course_location.course_location_id, course_name, course_foc, course_description, course_start_date, course_end_date FROM course, course_location, course_registration, location WHERE student_id = '%s' AND course_registration.course_location_id = course_location.course_location_id AND course.course_id = course_location.course_id AND course_location.location_id = location.location_id ORDER BY course_start_date ASC", $student_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$courses[] = $row;
		}
		return $courses;
	}
	function get_courses_by_student_id_current_year($student_id) {
	  list($year, $month, $day) = explode("-", date('Y-m-d'));
	  if ($month < 9) {
	    $previous_year = $year-1;
	  } else {
	    $previous_year = $year;
	    $year ++;
	  }
	  $courses = array();
	  $connection = connect_database();
	  $query = "SELECT school_name, course_location.course_location_id, course_name, course_foc, course_description, course_start_date, course_end_date FROM course, course_location, course_registration, location WHERE student_id = '$student_id' AND course_registration.course_location_id = course_location.course_location_id AND course.course_id = course_location.course_id AND course_location.location_id = location.location_id 
	and course_start_date like '$year-%-%'
	ORDER BY course_start_date ASC";
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$courses[] = $row;
		}
		return $courses;
	}
	function get_grade_level_by_current_year($student_id) {
		//$current_year = 2009;
//  	$current_year = $this->get_current_year(); 
//      $current_year = 2012;
        list($year, $month, $day) = explode("-", date('Y-m-d'));
        if ($month < 9) {
            $year_to_display = $year;
        } else {
            $year_to_display = $year+1;
        }
        $current_year = $year_to_display;
//         $current_year = CURRENT_YEAR;
        $connection = connect_database();
        $query = sprintf("SELECT grade_level FROM student_grade WHERE student_id = '%s' AND grade_year = '%s'", $student_id, $current_year);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_assoc($result);
        if ($row['grade_level']) {
            return $row['grade_level'];
        } else {
            /* try and find older years */
            $query = sprintf("SELECT grade_level, grade_year FROM student_grade WHERE student_id = '%s' ORDER BY grade_year DESC LIMIT 1", $student_id);
            $result = mysqli_query($connection,$query);
            if (!$result) {
                die(mysqli_error($connection));
            }
            $row = mysqli_fetch_assoc($result);
            if ($row['grade_level']) {
                if (preg_match('/^2\d{3}$/', $row['grade_year'])) {
                    $diff = $current_year - $row['grade_year'];
                    $updated_grade_level = $row['grade_level'];
                    for ($i = 0; $i < $diff; $i++) {
                        if ($row['grade_level'] == 'Pre-K') {
                            $updated_grade_level = 'K';
                        } else if ($row['grade_level'] == 'K') {
                            $updated_grade_level = 1;
                        } else if ($row['grade_level'] == 12) {
                            $updated_grade_level = 12;
                        } else {
                            $updated_grade_level++;
                        }
                    }
                    return $updated_grade_level;
                }
            } else {
                return NULL;
            }
        }
	}
	function get_interests_by_student_id($student_id) {
		$interests = array();
		$connection = connect_database();
		$query = sprintf("SELECT interest_id FROM student_interest WHERE student_id = '%s'", $student_id);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$key = $row['interest_id'];
			$interests[$key] = 1;
		}
		return $interests;
	}
	function view_teachers($order_by = "last_name") {
		$teachers = array();
        $connection = connect_database();
		$query = sprintf("SELECT teacher_id, first_name, last_name, street_address, city, state, zip, home_phone, cell_phone, fax_phone, other_phone, email, personal_url, educational_status, pay_rate, nickname, date_of_birth FROM teacher ORDER BY $order_by");
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$teachers[] = $row;
		}
		return $teachers;
	}
	function get_teacher_by_teacher_id($teacher_id) {
		$teacher = array();
        $connection = connect_database();
		$query = sprintf("SELECT teacher_id, first_name, last_name, street_address, city, state, zip, home_phone, cell_phone, fax_phone, other_phone, email, personal_url, educational_status, pay_rate, date_of_birth, nickname FROM teacher WHERE teacher_id = '%s'", $teacher_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		$row = mysqli_fetch_assoc($result);
		return $row;
	}
	function update_teacher($p) {
		$teacher_location_id = $p['teacher_location'];
		$teacher_id = $p['teacher_id'];
		$first_name = $p['first_name'];
		$last_name = $p['last_name'];
		$street_address = $p['street_address'];
		$city = $p['city'];
		$state = $p['state'];
		$zip = $p['zip'];
		$home_phone = $p['home_phone'];
		$cell_phone = $p['cell_phone'];
		$fax_phone = $p['fax_phone'];
		$other_phone = $p['other_phone'];
		$email = $p['email'];
		$personal_url = $p['personal_url'];
		$educational_status = $p['educational_status'];
		$pay_rate = !empty($p['pay_rate'])?$p['pay_rate']:0;
		$password = $p['password'];
		$nickname = $p['nickname'];
		$birth_year = $p['birth_year'];
		$birth_month = $p['birth_month'];
		$birth_day = $p['birth_day'];
		$date_of_birth = sprintf("%04d-%02d-%02d", $birth_year, $birth_month, $birth_day);
		$connection = connect_database();
		if (preg_match('/\w+/', $password)) {
			$query = sprintf("UPDATE teacher SET first_name = '%s', last_name = '%s', street_address = '%s', city = '%s', state = '%s', zip = '%s', home_phone = '%s', cell_phone = '%s', fax_phone = '%s', other_phone = '%s', email = '%s', personal_url = '%s', educational_status = '%s', pay_rate = '%s', password = md5('%s'), nickname = '%s', date_of_birth = '%s' WHERE teacher_id = '%s'", $first_name, $last_name, $street_address, $city, $state, $zip, $home_phone, $cell_phone, $fax_phone, $other_phone, $email, $personal_url, $educational_status, $pay_rate, $password, $nickname, $date_of_birth, $teacher_id);
		} else {
			$query = sprintf("UPDATE teacher SET first_name = '%s', last_name = '%s', street_address = '%s', city = '%s', state = '%s', zip = '%s', home_phone = '%s', cell_phone = '%s', fax_phone = '%s', other_phone = '%s', email = '%s', personal_url = '%s', educational_status = '%s', pay_rate = '%s', nickname = '%s', date_of_birth = '%s' WHERE teacher_id = '%s'", $first_name, $last_name, $street_address, $city, $state, $zip, $home_phone, $cell_phone, $fax_phone, $other_phone, $email, $personal_url, $educational_status, $pay_rate, $nickname, $date_of_birth, $teacher_id);
		}
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		$query = sprintf("DELETE FROM teacher_course_location WHERE teacher_id= '%s'", $teacher_id);
		$result = mysqli_query($connection,$query);
		if (!$result) {
			die(mysqli_error($connection));
		}
		/* go through courses */
		$courses = array();
		foreach ($p as $key => $value) {
			if (preg_match('/^course_(\d+)$/', $key)) {
				$courses[] = $value;
			}
		}
		foreach ($courses as $course_location_id) {
			if ($course_location_id > 0) {
				$query = sprintf("INSERT INTO teacher_course_location (teacher_id, course_location_id) VALUES ('%s', '%s')", $teacher_id, $course_location_id);
				if (!mysqli_query($connection,$query)) {
					die(mysqli_error($connection));
					return 0;
				}
			}
		}
		$query = "select teacher_location_id from teacher_location where teacher_id = $teacher_id order by teacher_location_id asc limit 0,1";
		$result = mysqli_query($connection,$query);
		$row = mysqli_fetch_assoc($result);
		if ($row["teacher_location_id"]) {
			$query = "update teacher_location set location_id = $teacher_location_id where teacher_location_id = ".$row["teacher_location_id"];
		} else {
			$query = "insert into teacher_location values (NULL, $teacher_id, $teacher_location_id)";
		}
		mysqli_query($connection,$query);
	}
	function add_teacher($p) {
		$nickname = $p['nickname'];
		$first_name = $p['first_name'];
        $last_name = $p['last_name'];
        $street_address = $p['street_address'];
        $city = $p['city'];
        $state = $p['state'];
        $zip = $p['zip'];
        $home_phone = $p['home_phone'];
        $cell_phone = $p['cell_phone'];
        $fax_phone = $p['fax_phone'];
        $other_phone = $p['other_phone'];
        $email = $p['email'];
        $personal_url = $p['personal_url'];
        $educational_status = $p['educational_status'];
        $pay_rate = !empty($p['pay_rate'])?$p['pay_rate']:0;
        $password = $p['password'];
		$connection = connect_database();
		$query = sprintf("INSERT INTO teacher (nickname, first_name, last_name, street_address, city, state, zip, home_phone, cell_phone, fax_phone, other_phone, email, personal_url, educational_status, pay_rate, password) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', md5('%s'))", $nickname, $first_name, $last_name, $street_address, $city, $state, $zip, $home_phone, $cell_phone, $fax_phone, $other_phone, $email, $personal_url, $educational_status, $pay_rate, $password);	
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		$query = sprintf("SELECT teacher_id FROM teacher WHERE first_name = '%s' AND last_name = '%s' AND email = '%s' AND password = md5('%s')", $first_name, $last_name, $email, $password);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		$row = mysqli_fetch_assoc($result);
		return $row['teacher_id'];
	}
	function get_teacher_by_course_location_id($course_location_id) {
		$teacher = array();
		$connection = connect_database();
		$query = sprintf("SELECT teacher.teacher_id, first_name, last_name, nickname FROM teacher, teacher_course_location WHERE course_location_id = '%s' AND teacher.teacher_id = teacher_course_location.teacher_id", $course_location_id);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_assoc($result);
        return $row;
	}
	function get_teachers_by_course_location_id($course_location_id) {
		$teacher = array();
		$connection = connect_database();
		$query = sprintf("SELECT teacher.teacher_id, first_name, last_name, nickname, email FROM teacher, teacher_course_location WHERE course_location_id = '%s' AND teacher.teacher_id = teacher_course_location.teacher_id", $course_location_id);
		$result = mysqli_query($connection,$query);
		while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
		return $rows;
	}
	function get_courses_by_teacher_id($teacher_id) {
		$courses = array();		
        $connection = connect_database();
		$query = sprintf("SELECT course.course_id, course_name, course_description, course_location.course_location_id, course_location.location_id, location.school_name, max_enrollment, course_start_date, course_end_date, course_cost, course_type, start_time, end_time, start_grade, end_grade FROM teacher_course_location, course, course_location, location WHERE teacher_id = '%s' AND teacher_course_location.course_location_id = course_location.course_location_id AND course.course_id = course_location.course_id AND course_location.location_id = location.location_id ORDER BY course_start_date ASC, location.school_name ASC, course_name ASC", $teacher_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
/*
			$courses[] = array(
				'course_id'	=> $row['course_id'],
				'course_name' => $row['course_name'],
				'course_description' => $row['course_description'],
				'course_location_id' => $row['course_location_id'],
				'location_id' => $row['location_id'],
				'max_enrollment' => $row['max_enrollment'],
				'course_start_date' => $row['course_start_date'],
				'course_end_date' => $row['course_end_date'],
				'course_cost' => $row['course_cost'],
				'course_type' => $row['course_type'],
			);
*/
			$courses[] = $row;
		}
		return $courses;
	}
	function view_courses($order_by = "course_name") {
		$courses = array();
        $connection = connect_database();
		$query = sprintf("SELECT course.course_id, course_name, course_description, course_location_id, location.location_id, course_type, max_enrollment, course_cost, course_start_date, course_end_date, course_room, course_day, school_name, course_location.active, start_time, end_time, start_grade, end_grade FROM course, course_location, location WHERE course.course_id = course_location.course_id AND course_location.location_id = location.location_id ORDER BY %s, course_start_date", $order_by);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$courses[] = $row;
		}
		return $courses;
	}
	function get_course_options() {
		$course_options = array();
		$connection = connect_database();
		$query = sprintf("SELECT course_id, course_name, course_foc, course_ab, moodle_id, course_description, course_image, active FROM course");
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$course_options[] = $row;
		}
		return $course_options;
	}
	function get_course_by_course_id($course_id) {
		$connection = connect_database();
        $query = sprintf("SELECT course_name, course_foc, course_ab, moodle_id, course_image, course_description, active FROM course WHERE course_id = '%s'", $course_id);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		$row = mysqli_fetch_assoc($result);
		return $row;
	}
	function get_course_by_course_location_id($course_location_id) {
        $connection = connect_database();
		$query = sprintf("SELECT lab_fee, course_day, start_time, end_time, course.course_id, course_name, course_foc, course_description, course_location_id, location_id, course_type, max_enrollment, course_cost, course_online_cost, course_start_date, course_end_date, course_room, course_day, add_to_cart, registration_info, course_location.active, start_time, end_time, start_grade, end_grade, camp_id FROM course, course_location WHERE course_location.course_location_id = '%s' AND course.course_id = course_location.course_id", $course_location_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		$row = mysqli_fetch_assoc($result);
		return $row;
	}
	function update_course_by_course_location_id($p) {
		$course_start_time = !empty($p['course_start_time'])?$p['course_start_time']:'00:00:00';
		$course_end_time = !empty($p['course_end_time'])?$p['course_end_time']:'00:00:00';
		$course_location_id = $p['course_location_id'];
		$location_id = $p['location_id'];
		//$course_name = $p['course_name'];
		//$course_description = $p['course_description'];
		$course_type = $p['course_type'];
		$max_enrollment = $p['max_enrollment'];
		$course_cost = $p['course_cost'];
		$lab_fee = $p['lab_fee'];
		if (!$lab_fee) $lab_fee = 0;
		$course_online_cost = !empty($p['course_online_cost'])?$p['course_online_cost']:0;
		$start_date_year = $p['start_date_year'];
		$start_date_month = $p['start_date_month'];
		$start_date_day = $p['start_date_day'];
		$end_date_year = $p['end_date_year'];
		$end_date_month = $p['end_date_month'];
		$end_date_day = $p['end_date_day'];
        $course_room = $p['course_room'];
        $course_day = $p['course_day'];
		$add_to_cart = $p['add_to_cart'];
		$registration_info = $p['registration_info'];
		$active = $p['active'];
		$start_time_hour = $p['start_time_hour'];
		$start_time_minute = $p['start_time_minute'];
		$start_time_am_pm = $p['start_time_am_pm'];
		$end_time_hour = $p['end_time_hour'];
		$end_time_minute = $p['end_time_minute'];
		$end_time_am_pm = $p['end_time_am_pm'];
		
		$start_grade = isset($p['start_grade']) ? $p['start_grade'] : "NULL";
        $end_grade = isset($p['end_grade']) ? $p['end_grade'] : "NULL";
// 		if ($start_time_am_pm == 'pm') {
// 			$start_time_hour += 12;
// 		}
// 		if ($end_time_am_pm == 'pm') {
// 			$end_time_hour += 12;
// 		}
// 		$start_time = sprintf("%02d%02d00", $start_time_hour, $start_time_minute);
// 		$end_time = sprintf("%02d%02d00", $end_time_hour, $end_time_minute);
		$course_start_date = $start_date_year . "-" . $start_date_month . "-" . $start_date_day;
		$course_end_date = $end_date_year . "-" . $end_date_month . "-" . $end_date_day;
		$camp_id = $p['camp_id'];
		$connection = connect_database();
		$query = sprintf("UPDATE course_location SET location_id = '%s', course_type = '%s', max_enrollment = '%s', course_cost = '%s', lab_fee = '%s', course_online_cost = '%s', course_start_date = '%s', course_end_date = '%s', course_room = '%s', course_day = '%s', add_to_cart = '%s', registration_info = '%s', active = '%s', camp_id = '%s', start_time = '%s', end_time = '%s', start_grade = %s, end_grade = %s WHERE course_location_id = '%s'",
    mysqli_real_escape_string($connection, $location_id),
    mysqli_real_escape_string($connection, $course_type),
    mysqli_real_escape_string($connection, $max_enrollment),
    mysqli_real_escape_string($connection, $course_cost),
    mysqli_real_escape_string($connection, $lab_fee),
    mysqli_real_escape_string($connection, $course_online_cost),
    mysqli_real_escape_string($connection, $course_start_date),
    mysqli_real_escape_string($connection, $course_end_date),
    mysqli_real_escape_string($connection, $course_room),
    mysqli_real_escape_string($connection, $course_day),
    mysqli_real_escape_string($connection, $add_to_cart),
    mysqli_real_escape_string($connection, $registration_info),
    mysqli_real_escape_string($connection, $active),
    mysqli_real_escape_string($connection, $camp_id),
    mysqli_real_escape_string($connection, $course_start_time),
    mysqli_real_escape_string($connection, $course_end_time),
    ($start_grade !== "NULL") ? "'" . mysqli_real_escape_string($connection, $start_grade) . "'" : "NULL",
    ($end_grade !== "NULL") ? "'" . mysqli_real_escape_string($connection, $end_grade) . "'" : "NULL",
    mysqli_real_escape_string($connection, $course_location_id));

		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
/*
		$query = sprintf("DELETE FROM grade WHERE course_location_id = '%s'", $course_location_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $grades = array();
        foreach ($p as $key => $value) {
            if (preg_match('/^grade_(\d+)$/', $key)) {
                $grades[] = $value;
            }
        }
        foreach ($grades as $grade_level) {
            if ($grade_level > 0) {
                $query = sprintf("INSERT INTO grade (course_location_id, grade_level) VALUES ('%s', '%s')", $course_location_id, $grade_level);
                if (!mysqli_query($connection,$query)) {
                    die(mysqli_error($connection));
                    return 0;
                }
            }
        }
*/
	}
	function add_course_location($p) {
		$location_id = $p['location_id'];
		$course_id = $p['course_id'];
        //$course_name = $p['course_name'];
        //$course_description = $p['course_description'];
        $course_type = $p['course_type'];
        $max_enrollment = !empty($p['max_enrollment'])?$p['max_enrollment']:0;
        $course_cost = !empty($p['course_cost'])?$p['course_cost']:0;
        $start_date_year = $p['start_date_year'];
        $start_date_month = $p['start_date_month'];
        $start_date_day = $p['start_date_day'];
        $end_date_year = $p['end_date_year'];
        $end_date_month = $p['end_date_month'];
        $end_date_day = $p['end_date_day'];
        $course_room = $p['course_room'];
        $course_day = $p['course_day'];
        $add_to_cart = $p['add_to_cart'];
        $registration_info = $p['registration_info'];
   		$camp_id = !empty($p['camp_id'])?$p['camp_id']:0;
		$course_start_date = $start_date_year . "-" . $start_date_month . "-" . $start_date_day;
        $course_end_date = $end_date_year . "-" . $end_date_month . "-" . $end_date_day;
        $connection = connect_database();
		$query = sprintf("INSERT INTO course_location (location_id, camp_id, course_id, course_type, max_enrollment, course_cost, course_start_date, course_end_date, course_room, course_day, add_to_cart, registration_info) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $location_id, $camp_id, $course_id, $course_type, $max_enrollment, $course_cost, $course_start_date, $course_end_date, $course_room, $course_day, $add_to_cart, $registration_info);
        echo $query;
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
	}
	function update_course_option($p) {
		$course_id = $p['course_id'];
		$course_name = $p['course_name'];
		$course_foc = $p['course_foc'];
		$course_ab = $p['course_ab'];
		$moodle_id = !empty($p['moodle_id']) ? (int)$p['moodle_id'] : 0;
		$course_description = $p['course_description'];
		$course_image = $p['course_image'];
		$active = $p['active'];
		
		$connection = connect_database();
		$query = sprintf("UPDATE course SET course_name = '%s', course_foc = '%s', course_ab = '%s', moodle_id = %s, course_description = '%s', course_image = '%s', active = '%s' WHERE course_id = '%s'", mysqli_escape_string($connection,$course_name), mysqli_escape_string($connection,$course_foc), mysqli_escape_string($connection,$course_ab), $moodle_id ? "'".mysqli_escape_string($connection,$moodle_id)."'" : "NULL", mysqli_escape_string($connection,$course_description), mysqli_escape_string($connection,$course_image), mysqli_escape_string($connection,$active), mysqli_escape_string($connection,$course_id));
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
	}
	function add_course_option($p) {
		$course_name = $p['course_name'];
		$course_foc = $p['course_foc'];
		$course_ab = $p['course_ab'];
        $course_description = $p['course_description'];
        $course_image = $p['course_image'];
		$connection = connect_database();
        if (!empty($course_name)) $course_name_escaped = mysqli_escape_string($connection,$course_name);
        if (!empty($course_foc_escaped)) $course_foc_escaped = mysqli_escape_string($connection,$course_foc);
        if (!empty($course_ab_escaped)) $course_ab_escaped = mysqli_escape_string($connection,$course_ab);
        if (!empty($course_description_escaped)) $course_description_escaped = mysqli_escape_string($connection,$course_description);
        if (!empty($course_image_escaped)) $course_image_escaped = mysqli_escape_string($connection,$course_image);
		$query  = 'INSERT INTO course (course_name, course_foc, course_ab, course_description, course_image) VALUES ("'.
        $course_name_escaped.'", "'.
        $course_foc_escaped.'", "'.
        $course_ab_escaped.'", "'.
        $course_description_escaped.'", "'.
        $course_image_escaped.'")';
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
	}
	function view_locations($order_by="school_name") {
		$locations = array();
		$connection = connect_database();
		$query = sprintf("SELECT location_id, school_name, active FROM location ORDER BY $order_by");
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$locations[] = array(
				'location_id' => $row['location_id'],
				'school_name' => $row['school_name'],
				'active' => $row['active'],
			);
		}
		return $locations;
	}
	function get_courses_by_location_id($location_id, $order_by = "course_name") {
		$courses = array();		
		$connection = connect_database();
		$query = sprintf("SELECT course_location.course_id, course_location_id, course_name, course_description, course_type, max_enrollment, course_cost, course_duration, course_start_date, course_end_date, start_time, end_time, start_grade, end_grade FROM course, course_location WHERE location_id = '%s' AND course.course_id = course_location.course_id ORDER BY $order_by", $location_id);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$courses[] = $row;
		}
		return $courses;
	}
	function get_courses_by_location_id_for_adminhome($location_id, $order_by = "course_name") {
		$courses = array();		
		$connection = connect_database();
		$query = sprintf("SELECT course_location.course_id, course_location_id, course_name, course_description, course_type, max_enrollment, course_location.active, course_cost, course_duration, camp_id, course_start_date, course_end_date, start_grade, end_grade FROM course, course_location WHERE location_id = '%s' AND course.course_id = course_location.course_id ORDER BY course_start_date", $location_id);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$courses[] = $row;
		}
		return $courses;
	}
	function get_courses_by_location_id_and_start_date($location_id, $course_start_date) {
        $courses = array();
        $connection = connect_database();
        $one = strtotime($course_start_date);
        $one_week_ago = date('Y-m-d', strtotime("-1 week",$one)); //1 week ago
        $query = "SELECT course_location.course_id, course_location_id, course_name, course_description, course_type, max_enrollment, course_location.active, course_cost, course_duration, course_start_date, course_end_date, start_grade, end_grade FROM course, course_location 
        WHERE location_id = '$location_id' 
        AND (
            course_start_date = '$course_start_date' 
          OR
            (
              course_name like '%2-Week%'
              AND course_start_date = '$one_week_ago' 
            )
        )
        AND course.course_id = course_location.course_id 
        ORDER BY course_name";        
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $courses[] = $row;
        }
        return $courses;
    }
    function ensure_location_director_columns($connection) {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        $result = mysqli_query($connection, "SHOW COLUMNS FROM location LIKE 'director_name'");
        if (!mysqli_fetch_assoc($result)) {
            mysqli_query($connection, "ALTER TABLE location ADD director_name varchar(100) NOT NULL DEFAULT '' AFTER administrative_contact");
        }
        $result = mysqli_query($connection, "SHOW COLUMNS FROM location LIKE 'director_email'");
        if (!mysqli_fetch_assoc($result)) {
            mysqli_query($connection, "ALTER TABLE location ADD director_email varchar(100) NOT NULL DEFAULT '' AFTER director_name");
        }
        $welcome_columns = array(
            'welcome_dropoff_instructions' => "ALTER TABLE location ADD welcome_dropoff_instructions TEXT NULL AFTER director_email",
            'welcome_pickup_instructions' => "ALTER TABLE location ADD welcome_pickup_instructions TEXT NULL AFTER welcome_dropoff_instructions",
            'welcome_parking_instructions' => "ALTER TABLE location ADD welcome_parking_instructions TEXT NULL AFTER welcome_pickup_instructions",
            'welcome_extended_care_notes' => "ALTER TABLE location ADD welcome_extended_care_notes TEXT NULL AFTER welcome_parking_instructions",
            'welcome_location_notes' => "ALTER TABLE location ADD welcome_location_notes TEXT NULL AFTER welcome_extended_care_notes",
            'welcome_map_url' => "ALTER TABLE location ADD welcome_map_url varchar(255) NOT NULL DEFAULT '' AFTER welcome_location_notes",
            'welcome_photo_url' => "ALTER TABLE location ADD welcome_photo_url varchar(255) NOT NULL DEFAULT '' AFTER welcome_map_url",
            'sunday_theme' => "ALTER TABLE location ADD sunday_theme TEXT NULL AFTER welcome_photo_url",
            'sunday_photo_url' => "ALTER TABLE location ADD sunday_photo_url varchar(255) NOT NULL DEFAULT '' AFTER sunday_theme"
        );
        foreach ($welcome_columns as $column => $alter_sql) {
            $result = mysqli_query($connection, "SHOW COLUMNS FROM location LIKE '$column'");
            if (!mysqli_fetch_assoc($result)) {
                mysqli_query($connection, $alter_sql);
            }
        }
        $this->seed_location_report_contacts($connection);
    }

    function seed_location_report_contacts($connection) {
        $contacts = array(
            array("beverly|good shepherd", "310-294-1605", "bh@planetbravo.com"),
            array("burbank|finbar", "818-835-5125", "burbank@planetbravo.com"),
            array("eagle|pasadena|dominic", "818-659-8569", "pas@planetbravo.com"),
            array("encino|hesby", "818-639-2159", "en@planetbravo.com"),
            array("irvine|pacific academy", "949-229-0495", "irvine@planetbravo.com"),
            array("manhattan", "424-218-6004", "mbms@planetbravo.com"),
            array("mar vista|santa monica", "424-259-2133", "sm@planetbravo.com"),
            array("westchester|visitation", "424-277-0692", "pv@planetbravo.com")
        );

        foreach ($contacts as $contact) {
            $clauses = array();
            foreach (explode('|', $contact[0]) as $needle) {
                $safe_needle = mysqli_real_escape_string($connection, $needle);
                $clauses[] = "LOWER(school_name) LIKE '%$safe_needle%'";
            }
            $where = implode(' OR ', $clauses);
            $phone = mysqli_real_escape_string($connection, $contact[1]);
            $email = mysqli_real_escape_string($connection, $contact[2]);
            mysqli_query($connection, "UPDATE location SET director_email = IF(director_email = '' OR director_email IS NULL, '$email', director_email), business_phone = IF(business_phone = '' OR business_phone IS NULL, '$phone', business_phone) WHERE ($where)");
        }
    }

	function get_location_by_location_id($location_id) {
        $connection = connect_database();
        $this->ensure_location_director_columns($connection);
        $query = sprintf("SELECT referrals, extended_care_price, lunch_price, command_center_id, school_name, planetbravo_id_name, street, city, state, zip, business_phone, fax_phone, url_of_school, welcome_path, administrative_contact, director_name, director_email, welcome_dropoff_instructions, welcome_pickup_instructions, welcome_parking_instructions, welcome_extended_care_notes, welcome_location_notes, welcome_map_url, welcome_photo_url, sunday_theme, sunday_photo_url, offer_after_school, offer_summer_camp, notes, active FROM location WHERE location_id = '%s'", $location_id);
        $result = mysqli_query($connection,$query);// foreach($students as $key
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_assoc($result);
        return $row;
    }
	function get_location_by_course_location_id($course_location_id) {
        $connection = connect_database();
        $this->ensure_location_director_columns($connection);
        $query = sprintf("SELECT location.location_id, command_center_id, school_name, planetbravo_id_name, street, city, state, zip, business_phone, fax_phone, url_of_school, welcome_path, administrative_contact, director_name, director_email, welcome_dropoff_instructions, welcome_pickup_instructions, welcome_parking_instructions, welcome_extended_care_notes, welcome_location_notes, welcome_map_url, welcome_photo_url, sunday_theme, sunday_photo_url, offer_after_school, offer_summer_camp, notes, location.active FROM location,course_location WHERE location.location_id = course_location.location_id and course_location.course_location_id = '%s'", $course_location_id);
        $result = mysqli_query($connection,$query);// foreach($students as $key
        if (!$result) {
	  die(mysqli_error($connection));
        }
        $row = mysqli_fetch_assoc($result);
        return $row;
    }
    function delete_welcome_location_photo_file($photo_url) {
        $photo_url = trim((string)$photo_url);
        if ($photo_url == '' || strpos($photo_url, '/img/locations/welcome/') !== 0) {
            return;
        }
        $path = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . str_replace('/', DIRECTORY_SEPARATOR, $photo_url);
        if (is_file($path)) {
            @unlink($path);
        }
    }
    function save_welcome_location_photo($location_id, $current_photo_url) {
        $current_photo_url = trim((string)$current_photo_url);
        if (!empty($_POST['remove_welcome_photo'])) {
            $this->delete_welcome_location_photo_file($current_photo_url);
            $current_photo_url = '';
        }
        if (empty($_FILES['welcome_photo']) || empty($_FILES['welcome_photo']['tmp_name']) || $_FILES['welcome_photo']['error'] == UPLOAD_ERR_NO_FILE) {
            return $current_photo_url;
        }
        if ($_FILES['welcome_photo']['error'] != UPLOAD_ERR_OK) {
            return $current_photo_url;
        }
        $allowed = array(
            'jpg' => 'jpg',
            'jpeg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp'
        );
        $extension = strtolower(pathinfo($_FILES['welcome_photo']['name'], PATHINFO_EXTENSION));
        if (empty($allowed[$extension])) {
            return $current_photo_url;
        }
        $upload_dir = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'locations' . DIRECTORY_SEPARATOR . 'welcome';
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0755, true);
        }
        if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
            return $current_photo_url;
        }
        $filename = 'location-' . (int)$location_id . '-' . date('YmdHis') . '.' . $allowed[$extension];
        $target = $upload_dir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($_FILES['welcome_photo']['tmp_name'], $target)) {
            return $current_photo_url;
        }
        $this->delete_welcome_location_photo_file($current_photo_url);
        return '/img/locations/welcome/' . $filename;
    }
	function update_location($p) {
		$location_id = $p['location_id'];
		$extended_care = $p['extended_care'];
		$command_center_id = !empty($p['command_center_id'])?$p['command_center_id']:0;
		$school_name = $p['school_name'];
		$planetbravo_id_name = $p['planetbravo_id_name'];
		$street = $p['street'];
		$city = $p['city'];
		$state = $p['state'];
		$zip = $p['zip'];
		$business_phone = $p['business_phone'];
		$fax_phone = $p['fax_phone'];
		$url_of_school = $p['url_of_school'];
		$administrative_contact = $p['administrative_contact'];
		$director_name = isset($p['director_name']) ? $p['director_name'] : '';
		$director_email = isset($p['director_email']) ? $p['director_email'] : '';
		$welcome_dropoff_instructions = isset($p['welcome_dropoff_instructions']) ? $p['welcome_dropoff_instructions'] : '';
		$welcome_pickup_instructions = isset($p['welcome_pickup_instructions']) ? $p['welcome_pickup_instructions'] : '';
		$welcome_parking_instructions = isset($p['welcome_parking_instructions']) ? $p['welcome_parking_instructions'] : '';
		$welcome_extended_care_notes = isset($p['welcome_extended_care_notes']) ? $p['welcome_extended_care_notes'] : '';
		$welcome_location_notes = isset($p['welcome_location_notes']) ? $p['welcome_location_notes'] : '';
		$welcome_map_url = isset($p['welcome_map_url']) ? $p['welcome_map_url'] : '';
		$welcome_photo_url = $this->save_welcome_location_photo($location_id, isset($p['current_welcome_photo_url']) ? $p['current_welcome_photo_url'] : '');
		$sunday_theme = isset($p['sunday_theme']) ? $p['sunday_theme'] : '';
		$sunday_photo_url = isset($p['sunday_photo_url']) ? $p['sunday_photo_url'] : '';
		$offer_after_school = $p['offer_after_school'];
		$offer_summer_camp = $p['offer_summer_camp'];
        $extended_care_price = !empty($p['extended_care_price'])?$p['extended_care_price']:0;
        $lunch_price = !empty($p['lunch_price'])?$p['lunch_price']:0;
    $referrals = $p['referrals'];
    $notes = $p['notes'];
		$active = $p['active'];
		$msch = $p['msch'];
        $connection = connect_database();
        $this->ensure_location_director_columns($connection);
        $director_name = mysqli_real_escape_string($connection, $director_name);
        $director_email = mysqli_real_escape_string($connection, $director_email);
        $welcome_dropoff_instructions = mysqli_real_escape_string($connection, $welcome_dropoff_instructions);
        $welcome_pickup_instructions = mysqli_real_escape_string($connection, $welcome_pickup_instructions);
        $welcome_parking_instructions = mysqli_real_escape_string($connection, $welcome_parking_instructions);
        $welcome_extended_care_notes = mysqli_real_escape_string($connection, $welcome_extended_care_notes);
        $welcome_location_notes = mysqli_real_escape_string($connection, $welcome_location_notes);
        $welcome_map_url = mysqli_real_escape_string($connection, $welcome_map_url);
        $welcome_photo_url = mysqli_real_escape_string($connection, $welcome_photo_url);
        $sunday_theme = mysqli_real_escape_string($connection, $sunday_theme);
        $sunday_photo_url = mysqli_real_escape_string($connection, $sunday_photo_url);
		$query = sprintf("UPDATE location SET extended_care = $extended_care, referrals = $referrals, extended_care_price = '%s', lunch_price = '%s', command_center_id = '%s', school_name = '%s', planetbravo_id_name = '%s', street = '%s', city = '%s', state = '%s', zip = '%s', business_phone = '%s', fax_phone = '%s', url_of_school = '%s', administrative_contact = '%s', director_name = '%s', director_email = '%s', welcome_dropoff_instructions = '%s', welcome_pickup_instructions = '%s', welcome_parking_instructions = '%s', welcome_extended_care_notes = '%s', welcome_location_notes = '%s', welcome_map_url = '%s', welcome_photo_url = '%s', sunday_theme = '%s', sunday_photo_url = '%s', offer_after_school = '%s', offer_summer_camp = '%s', notes = '%s', active = '%s' WHERE location_id = '%s'", $extended_care_price, $lunch_price, $command_center_id, $school_name, $planetbravo_id_name, $street, $city, $state, $zip, $business_phone, $fax_phone, $url_of_school, $administrative_contact, $director_name, $director_email, $welcome_dropoff_instructions, $welcome_pickup_instructions, $welcome_parking_instructions, $welcome_extended_care_notes, $welcome_location_notes, $welcome_map_url, $welcome_photo_url, $sunday_theme, $sunday_photo_url, $offer_after_school, $offer_summer_camp, $notes, $active, $location_id);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        /* check for existing entry */
        if ($msch) {
            $query = sprintf("SELECT * FROM location_bravome_network WHERE location_id = '%s'", $location_id);
            $result = mysqli_query($connection,$query);
            $row = mysqli_fetch_assoc($result);
            if ($row['id']) {
                $query = sprintf("UPDATE location_bravome_network SET bravome_network_id = '%s' WHERE id = '%s'", $msch, $row['id']);
                mysqli_query($connection,$query);
            } else {
                $query = sprintf("INSERT INTO location_bravome_network (location_id, bravome_network_id) VALUES ('%s', '%s')", $location_id, $msch);
                mysqli_query($connection,$query);
            }
        }
	}
	function add_location($p) {
		$command_center_id = !empty($p['command_center_id'])?$p['command_center_id']:0;
        $school_name = $p['school_name'];
        $planetbravo_id_name = $p['planetbravo_id_name'];
        $street = $p['street'];
        $city = $p['city'];
        $state = $p['state'];
        $zip = $p['zip'];
        $business_phone = $p['business_phone'];
        $fax_phone = $p['fax_phone'];
        $url_of_school = $p['url_of_school'];
        $administrative_contact = $p['administrative_contact'];
        $director_name = isset($p['director_name']) ? $p['director_name'] : '';
        $director_email = isset($p['director_email']) ? $p['director_email'] : '';
        $welcome_dropoff_instructions = isset($p['welcome_dropoff_instructions']) ? $p['welcome_dropoff_instructions'] : '';
        $welcome_pickup_instructions = isset($p['welcome_pickup_instructions']) ? $p['welcome_pickup_instructions'] : '';
        $welcome_parking_instructions = isset($p['welcome_parking_instructions']) ? $p['welcome_parking_instructions'] : '';
        $welcome_extended_care_notes = isset($p['welcome_extended_care_notes']) ? $p['welcome_extended_care_notes'] : '';
        $welcome_location_notes = isset($p['welcome_location_notes']) ? $p['welcome_location_notes'] : '';
        $welcome_map_url = isset($p['welcome_map_url']) ? $p['welcome_map_url'] : '';
        $offer_after_school = $p['offer_after_school'];
        $offer_summer_camp = $p['offer_summer_camp'];
        $extended_care_price = !empty($p['extended_care_price'])?$p['extended_care_price']:0;
        $lunch_price = !empty($p['lunch_price'])?$p['lunch_price']:0;
        $notes = $p['notes'];
        $msch = $p['msch'];
		$connection = connect_database();
        $this->ensure_location_director_columns($connection);
        $director_name = mysqli_real_escape_string($connection, $director_name);
        $director_email = mysqli_real_escape_string($connection, $director_email);
        $welcome_dropoff_instructions = mysqli_real_escape_string($connection, $welcome_dropoff_instructions);
        $welcome_pickup_instructions = mysqli_real_escape_string($connection, $welcome_pickup_instructions);
        $welcome_parking_instructions = mysqli_real_escape_string($connection, $welcome_parking_instructions);
        $welcome_extended_care_notes = mysqli_real_escape_string($connection, $welcome_extended_care_notes);
        $welcome_location_notes = mysqli_real_escape_string($connection, $welcome_location_notes);
        $welcome_map_url = mysqli_real_escape_string($connection, $welcome_map_url);
		$query = sprintf("INSERT INTO location (extended_care_price, lunch_price, command_center_id, school_name, planetbravo_id_name, street, city, state, zip, business_phone, fax_phone, url_of_school, administrative_contact, director_name, director_email, welcome_dropoff_instructions, welcome_pickup_instructions, welcome_parking_instructions, welcome_extended_care_notes, welcome_location_notes, welcome_map_url, offer_after_school, offer_summer_camp, notes) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $extended_care_price, $lunch_price, $command_center_id, $school_name, $planetbravo_id_name, $street, $city, $state, $zip, $business_phone, $fax_phone, $url_of_school, $administrative_contact, $director_name, $director_email, $welcome_dropoff_instructions, $welcome_pickup_instructions, $welcome_parking_instructions, $welcome_extended_care_notes, $welcome_location_notes, $welcome_map_url, $offer_after_school, $offer_summer_camp, $notes);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        if ($msch) {
            $query = sprintf("INSERT INTO location_bravome_network (location_id, bravome_network_id) VALUES ('%s', '%s')", mysqli_insert_id($connection), $msch);
            mysqli_query($connection,$query);
        }
	}
	function get_enrolled($course_location_id) {
        $connection = connect_database();
        $query = sprintf("SELECT COUNT(*) FROM course_registration WHERE course_location_id = '%s'", $course_location_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_row($result);
        return $row[0];
    }
	function get_enrolled_online($course_location_id) {
        $connection = connect_database();
        $query = sprintf("SELECT COUNT(*) FROM course_registration WHERE course_location_id = '%s' and online = 1", $course_location_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_row($result);
        return $row[0];
    }
	function get_grades($course_location_id) {
        $grades = array();
        $connection = connect_database();
        $query = sprintf("SELECT grade_level FROM grade WHERE course_location_id = '%s' ORDER BY grade_level", $course_location_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $grades[] = $row['grade_level'];
        }
        return $grades;
    }
	
	function get_start_dates() {
		$start_dates = array();
        $connection = connect_database();
		$query = sprintf("SELECT DISTINCT course_start_date FROM course_location WHERE course_location.active = 'y' ORDER BY course_start_date");	
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$start_dates[] = $row;
		}
		return $start_dates;
	}

	function get_auto_admin_start_date($start_dates = array()) {
		if (!count($start_dates)) {
			$start_dates = $this->get_start_dates();
		}

		$now = time();
		$first_upcoming = '';
		$last_valid = '';
		$today = strtotime(date('Y-m-d'));

		foreach ($start_dates as $row) {
			$course_start_date = is_array($row) ? $row['course_start_date'] : $row;
			if (!$course_start_date) continue;

			$start_time = strtotime($course_start_date);
			if (!$start_time) continue;

			$last_valid = $course_start_date;
			$activation_time = strtotime($course_start_date . ' -2 days +1 minute');
			$rollover_time = strtotime($course_start_date . ' +5 days +1 minute');

			if ($now >= $activation_time && $now < $rollover_time) {
				return $course_start_date;
			}

			if (!$first_upcoming && $start_time >= $today) {
				$first_upcoming = $course_start_date;
			}
		}

		if ($first_upcoming) return $first_upcoming;
		if ($last_valid) return $last_valid;
		return date('Y-m-d');
	}
	function get_total_camp_report_by_contact($location_id, $start_date = "") {
	$year = date("Y");
        $students = array();
        $connection = connect_database();
        if ($start_date) {
            $query = "SELECT student_info.student_id, student_last_name, student_first_name, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_ab, course_start_date, course_end_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_location.course_start_date = '$start_date' AND course_location.location_id = '$location_id' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id and course_start_date like '%$year%' ORDER by student_last_name ASC";
        } else {
            $query = "SELECT student_info.student_id, student_last_name, student_first_name, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_ab, course_start_date, course_end_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_location.location_id = '$location_id' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id and course_start_date like '%$year%' ORDER by student_last_name ASC";
        }
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name']);
			$row['student_last_name'] = ucfirst($row['student_last_name']);
            $students[] = $row;
        }
        return $students;
    }
	function get_total_camp_report_by_school($start_date = "") {
        $students = array();
        $connection = connect_database();
        if ($start_date) {
            $query = sprintf("SELECT student_info.student_id, student_last_name, student_first_name, school, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_location.course_start_date = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id ORDER BY school, date_of_birth", $start_date);
        } else {
            $query = sprintf("SELECT student_info.student_id, student_last_name, student_first_name, school, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id ORDER BY school, date_of_birth", $start_date);
        }
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $students[] = $row;
        }
        return $students;
    }
      function get_total_camp_report_by_school2($how_hear, $location_id, $start_date = "", $start = 0, $end = 0, $sort_name = "") {
	if (!$end) $end = $start + 300;
        $students = array();
        $connection = connect_database();
        if ($start_date) {
            $query = sprintf("SELECT account_info.how_hear, student_info.student_id, student_last_name, student_first_name, school, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, course_location.course_location_id FROM account_info, course, course_location, student_info, course_registration WHERE account_info.how_hear = '%s' AND course_location.course_start_date = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id", $how_hear, $start_date);
        } else {
            $query = sprintf("SELECT account_info.how_hear, student_info.student_id, student_last_name, student_first_name, school, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, course_location.course_location_id FROM account_info, course, course_location, student_info, course_registration WHERE account_info.how_hear = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id", $how_hear);
        }
	if ($location_id) $query .= " AND course_location.location_id = '$location_id'";
        $query .= " and course_location.camp_id = (select `value` from `settings` where `key` = 'current_camp')";
        $query .= " group by school, date_of_birth";
        if ($sort_name) $query .= " order by $sort_name asc";
	// if (!$location_id) $query .= " limit $start,$end";
// 	echo $query;
// 	die;
        $result = mysqli_query($connection,$query);
        if (!$result) die(mysqli_error($connection));
        while ($row = mysqli_fetch_assoc($result)) {
	  $query_2 = "select street_address, city, state, date_of_creation from account_info where account_id = (
	  select account_id from student_info where student_id = ".$row["student_id"]."
	 )";
	  $result_2 = mysqli_query($connection,$query_2);
	  $row_2 = mysqli_fetch_assoc($result_2);
	  $row["street_address"] = $row_2["street_address"];
	  $row["city"] = $row_2["city"];
	  $row["state"] = $row_2["state"];
	  $row["date_of_creation"] = $row_2["date_of_creation"];
	  $students[] = $row;
        }
        return $students;
    }
	function get_total_camp_report_by_start_date($start_date = "") {
	  $students = array();
	  $connection = connect_database();
		if ($start_date) {
			//$query = sprintf("SELECT student_info.student_id, student_last_name, student_first_name, cteacher, room, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, registration_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_start_date > '2009-09-01' AND course_location.course_start_date = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id ORDER BY gender, date_of_birth", $start_date);
			$query = sprintf("SELECT extended_care, camper_image, student_info.student_id, student_last_name, student_first_name, cteacher, room, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, registration_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_location.active = '%s' AND course_location.course_start_date = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id
            and course.course_id != 466
			ORDER BY gender, date_of_birth", "y", $start_date);
		} else {
			//$query = sprintf("SELECT student_info.student_id, student_last_name, student_first_name, cteacher, room, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, registration_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_start_date > '2009-09-01' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id ORDER BY gender, date_of_birth", $start_date);
			$query = sprintf("SELECT extended_care, camper_image, student_info.student_id, student_last_name, student_first_name, cteacher, room, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, registration_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_location.active = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id
            and course.course_id != 466
			ORDER BY gender, date_of_birth", "y");
		}
		$result = mysqli_query($connection,$query);
        while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name']);
			$row['student_last_name'] = ucfirst($row['student_last_name']);
			$students[] = $row;
		}
		return $students;
	}
	function sort_by_t_shirt_size($a, $b) {
		if ($a['t_shirt_size'] == $b['t_shirt_size']) {
			return 0;
		}
		return ($a['t_shirt_size'] < $b['t_shirt_size']) ? -1 : 1;
	}
	function sort_by_teacher_name($a, $b) {
		if ($a['teacher_last_name'] == $b['teacher_last_name']) {
			if ($a['teacher_first_name'] == $b['teacher_first_name']) {
				return 0;
			} else {
				return ($a['teacher_first_name'] < $b['teacher_first_name']) ? -1 : 1;
			}
		}	
		return ($a['teacher_last_name'] < $b['teacher_last_name']) ? -1 : 1;
	}
	function sort_by_teacher_name_then_student_name($a, $b) {
        if ($a['teacher_last_name'] == $b['teacher_last_name']) {
            if ($a['teacher_first_name'] == $b['teacher_first_name']) {
				if ($a['student_last_name'] == $b['student_last_name']) {
					if ($a['student_first_name'] == $b['student_first_name']) {	
						return 0;
					} else {
						return ($a['student_first_name'] < $b['student_first_name']) ? -1 : 1;
					}
				} else {
					return ($a['student_last_name'] < $b['student_last_name']) ? -1 : 1;
				}
            } else {
                return ($a['teacher_first_name'] < $b['teacher_first_name']) ? -1 : 1;
            }
        }
        return ($a['teacher_last_name'] < $b['teacher_last_name']) ? -1 : 1;
    }
	function sort_by_teacher_name_then_t_shirt($a, $b) {
        if ($a['teacher_last_name'] == $b['teacher_last_name']) {
            if ($a['teacher_first_name'] == $b['teacher_first_name']) {
				if ($a['t_shirt_size'] == $b['t_shirt_size']) {
                	return 0;
				} else {
					return ($a['t_shirt_size'] < $b['t_shirt_size']) ? -1 : 1;
				}
            } else {
                return ($a['teacher_first_name'] < $b['teacher_first_name']) ? -1 : 1;
            }
        }
        return ($a['teacher_last_name'] < $b['teacher_last_name']) ? -1 : 1;
    }
	function sort_by_parent_name($a, $b) {
		if ($a['account_last_name'] == $b['account_last_name']) {
            if ($a['account_first_name'] == $b['account_first_name']) {
                return 0;
            } else {
                return ($a['account_first_name'] < $b['account_first_name']) ? -1 : 1;
            }
        }
        return ($a['account_last_name'] < $b['account_last_name']) ? -1 : 1;
	}
	function sort_by_student_first_name($a, $b) {
        if ($a['student_first_name'] == $b['student_first_name']) {
            if ($a['student_first_name'] == $b['student_first_name']) {
                return 0;
            } else {
                return ($a['student_first_name'] < $b['student_first_name']) ? -1 : 1;
            }
        }
        return ($a['student_first_name'] < $b['student_first_name']) ? -1 : 1;
    }
	function get_total_camp_report_by_location($location_id, $start_date = "") {
        $students = array();
        $connection = connect_database();
        if ($start_date) {
            $query = sprintf("SELECT course_registration.late_cancelled, course_registration.rent_laptop, course_registration.cohort_id, teacher_course_location.teacher_id, extended_care, seating_order, pb_picks, student_info.student_id, student_last_name, student_first_name, camper_image, cteacher, room, uc_waiver, xtd, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_ab, course_start_date, course_end_date, course_room, course_location.course_location_id, course_location.course_type FROM teacher_course_location, course, course_location, student_info, course_registration WHERE course_location.active = '%s' AND course_location.course_start_date = '%s' AND course_location.location_id = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id
            and course.course_id != 466
            and teacher_course_location.course_location_id = course_location.course_location_id
            
            union
            
SELECT 
  course_registration.late_cancelled, course_registration.rent_laptop, course_registration.cohort_id, 0 as teacher_id, extended_care, seating_order, pb_picks, student_info.student_id, student_last_name, student_first_name, camper_image, cteacher, room, uc_waiver, xtd, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_ab, course_start_date, course_end_date, course_room, course_location.course_location_id, course_location.course_type
FROM 
  course, course_location, 
  student_info, 
  course_registration 
WHERE 
  course_location.active = '%s' 
AND 
  course_location.course_start_date = '%s' 
AND 
  course_location.location_id = '%s' 
AND 
  course.course_id = course_location.course_id 
AND 
  course_registration.course_location_id = course_location.course_location_id 
AND 
  course_registration.student_id = student_info.student_id 
and 
  course.course_id != 466
-- and
--   course_location.course_id = 425
ORDER BY 
  student_last_name ASC,
	teacher_id DESC

            ", "y", $start_date, $location_id, "y", $start_date, $location_id);
        } else {
            $query = sprintf("SELECT course_registration.rent_laptop, course_registration.cohort_id, teacher_course_location.teacher_id, extended_care,seating_order, pb_picks, student_info.student_id, student_last_name, student_first_name, camper_image, cteacher, room, uc_waiver, xtd, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_ab, course_start_date, course_end_date, course_room, course_location.course_location_id, course_location.course_type FROM teacher_course_location, course, course_location, student_info, course_registration WHERE course_location.active = '%s' AND course_location.location_id = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id
            and course.course_id != 466
            and teacher_course_location.course_location_id = course_location.course_location_id
            ORDER BY student_last_name ASC, teacher_id DESC;", "y", $location_id);
        }
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name']);
			$row['student_last_name'] = ucfirst($row['student_last_name']);
            $students[] = $row;
        }
		return $students;
    }
	function get_total_camp_report_by_location_ec_count($location_id, $start_date = "") {
    $students = array();
    $connection = connect_database();
    $query = "SELECT count(`course_registration_id`) as count_result FROM `course_registration` WHERE `extended_care` = 1 and `course_location_id` in (SELECT `course_location_id` FROM `course_location` where course_location.`location_id` = $location_id and course_location.`active` = 'y'";
    if ($start_date) $query .= " and course_location.course_start_date = '$start_date'";
    $query .= ")";
//     echo $query;exit;
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row['count_result'];
  }
	function get_total_camp_report_by_locations($location_id, $start_date = "") {
        $students = array();
        $connection = connect_database();
        if ($start_date) {
            $query = sprintf("SELECT seating_order, pb_picks, student_info.student_id, student_last_name, student_first_name, camper_image, cteacher, room, uc_waiver, xtd, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_ab, course_start_date, course_end_date, course_room, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_location.active = '%s' AND course_location.course_start_date = '%s' AND course_location.location_id = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id 
            ORDER BY student_last_name", "y", $start_date, $location_id);
        } else {
            $query = sprintf("SELECT seating_order, pb_picks, student_info.student_id, student_last_name, student_first_name, camper_image, cteacher, room, uc_waiver, xtd, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_ab, course_start_date, course_end_date, course_room, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_location.active = '%s' AND course_location.location_id = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id 
            ORDER BY student_last_name", "y", $location_id);
        }
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name']);
			$row['student_last_name'] = ucfirst($row['student_last_name']);
            $students[] = $row;
        }
		return $students;
    }    
	function get_total_camp_report_by_location_gender($location_id, $start_date = "", $course_location = 0) {
		$students = array();
		$connection = connect_database();
		if ($course_location)
			$course_location_text = "course_location_id";
		else
			$course_location_text = "location_id";
        if ($start_date) {
            $query = sprintf("SELECT extended_care, teacher_id, cohort_id, student_info.student_id, student_last_name, student_first_name, camper_image, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_location.active = '%s' AND course_location.course_start_date = '%s' AND course_location.$course_location_text = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id
            and course.course_id != 466
            ORDER BY teacher_id ASC, student_last_name ASC", "y", $start_date, $location_id);
        } else {
            $query = sprintf("SELECT extended_care, teacher_id, cohort_id, student_info.student_id, student_last_name, student_first_name, camper_image, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_location.active = '%s' AND course_location.$course_location_text = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id
            and course.course_id != 466
            ORDER BY teacher_id ASC, student_last_name ASC", "y", $location_id);
        }
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name']);
			$row['student_last_name'] = ucfirst($row['student_last_name']);
            $students[] = $row;
        }
        return $students;
    }
	function get_student_awards($student_id, $course_location_id) {
        $awards = array();
		$award_obj = new Award();
        $connection = connect_database();
        $query = sprintf("SELECT award_id, student_id, course_location_id, award_title, reason, camper_of_the_week, best_in_class FROM award WHERE student_id = '%s' AND course_location_id = '%s'", $student_id, $course_location_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$row['award_title'] = $award_obj->to_html($row['award_title']);
			$row['reason'] = $award_obj->to_html($row['reason']);
            $awards[] = $row;
        }
        return $awards;
    }
	function get_award($award_id) {
        $award = array();
		$award_obj = new Award();
        $connection = connect_database();
        $query = sprintf("SELECT student_first_name, student_last_name, award.student_id, course_location_id, award_title, reason, camper_of_the_week, best_in_class FROM award, student_info WHERE award_id = '%s' AND student_info.student_id = award.student_id", $award_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_assoc($result);
		$row['student_first_name'] = ucfirst($row['student_first_name'])
;
		$row['award_title'] = $award_obj->to_html($row['award_title']);
		$row['reason'] = $award_obj->to_html($row['reason']);
        return $row;
    }
	function get_total_camp_report_bravome($location_id, $start_date = "") {
        $students = array();
        $connection = connect_database();
        if ($start_date) {
            $query = sprintf("SELECT DISTINCT student_info.student_id, student_last_name, student_first_name, gender, pronoun, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_location.course_start_date = '%s' AND course_location.location_id = '%s' 
            and course.course_id != 466
            AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id GROUP BY student_id", $start_date, $location_id);
        } else {
            $query = sprintf("SELECT DISTINCT student_info.student_id, student_last_name, student_first_name, gender, pronoun, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_location.location_id = '%s' 
            and course.course_id != 466
            AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id GROUP BY student_id", $location_id);
        }
        if ($start_date == 0)
	  $query = "
SELECT 
  DISTINCT student_info.student_id, 
  student_last_name, 
  student_first_name, 
  gender, 
  t_shirt_size, 
  date_of_birth, 
  course_name, 
  course_start_date, 
  course_end_date, 
  course_location.course_location_id 
FROM 
  course, 
  course_location, 
  student_info, 
  course_registration 
WHERE 
  course_location.location_id = '$location_id' 
AND 
  course.course_id = course_location.course_id 
AND 
  course_registration.course_location_id = course_location.course_location_id 
AND 
  course_registration.student_id = student_info.student_id 
AND
  course_registration.registration_date < now()
AND
  course_registration.registration_date > '2015-11-30 00:00:00'
and
  course.active = 'y'
and
  course_location.active = 'y'
GROUP BY 
  student_id
";
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name'])
;
            $students[] = $row;
        }
        return $students;
    }
	function get_awards_by_start_date($start_date = "") {
		$awards = array();
		$award_obj = new Award();
		$connection = connect_database();
		if ($start_date) {
			$query = sprintf("SELECT course_name, course_start_date, course_end_date, student_id, award_title, reason, camper_of_the_week, best_in_class, teacher.first_name, teacher.last_name FROM award, course, course_location, teacher WHERE course_start_date = '%s' AND award.course_location_id = course_location.course_location_id AND course_location.course_id = course.course_id AND award.teacher_id = teacher.teacher_id", $start_date);
        } else {
			$query = sprintf("SELECT course_name, course_start_date, course_end_date, student_id, award_title, reason, camper_of_the_week, best_in_class, teacher.first_name, teacher.last_name FROM award, course, course_location, teacher WHERE award.course_location_id = course_location.course_location_id AND course_location.course_id = course.course_id AND award.teacher_id = teacher.teacher_id");
        }
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$row['award_title'] = $award_obj->to_html($row['award_title']);
			$row['reason'] = $award_obj->to_html($row['reason']);
            $awards[] = $row;
        }
        return $awards;	
	}
	function get_awards_by_location($location_id, $start_date = "") {
        $awards = array();
		$award_obj = new Award();
        $connection = connect_database();
        if ($start_date) {
			$query = sprintf("SELECT award.course_location_id, course_name, course_start_date, course_end_date, student_id, award_id, award_title, reason, camper_of_the_week, best_in_class, teacher.first_name, teacher.last_name, teacher.nickname FROM award INNER JOIN course_location ON award.course_location_id = course_location.course_location_id INNER JOIN course ON course_location.course_id = course.course_id LEFT JOIN teacher ON award.teacher_id = teacher.teacher_id WHERE course_location.location_id = '%s' AND course_start_date = '%s'
			order by teacher.nickname asc
			", $location_id, $start_date);
        } else {
			$query = sprintf("SELECT award.course_location_id, course_name, course_start_date, course_end_date, student_id, award_id, award_title, reason, camper_of_the_week, best_in_class, teacher.first_name, teacher.last_name, teacher.nickname FROM award INNER JOIN course_location ON award.course_location_id = course_location.course_location_id INNER JOIN course ON course_location.course_id = course.course_id LEFT JOIN teacher ON award.teacher_id = teacher.teacher_id WHERE course_location.location_id = '%s'
			order by teacher.nickname asc
			", $location_id);
        }
//         echo $query; exit;
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$row['award_title'] = $award_obj->to_html($row['award_title']);
            $row['reason'] = $award_obj->to_html($row['reason']);
			$row['first_name'] = ucfirst($row['first_name']);
			$row['last_name'] = ucfirst($row['last_name']);
			$row['teacher_first_name'] = $row['first_name'];
			$row['teacher_last_name'] = $row['last_name'];
            $awards[] = $row;
        }
        return $awards;
    }
	function get_awards_by_clid($clid, $teacher_id = 0, $cohort_id = 0) {
		$awards = array();
		$award_obj = new Award();
		$connection = connect_database();
		if ($teacher_id)
			$query = "SELECT award.course_location_id, course_name, course_start_date, course_end_date, student_id, award_id, award_title, reason, camper_of_the_week, best_in_class, teacher.first_name, teacher.last_name, teacher.nickname FROM award INNER JOIN course_location ON award.course_location_id = course_location.course_location_id INNER JOIN course ON course_location.course_id = course.course_id LEFT JOIN teacher ON award.teacher_id = teacher.teacher_id WHERE award.course_location_id = '$clid'
			and
			student_id in (
				select course_registration.student_id from course_registration where course_registration.course_location_id = $clid and course_registration.teacher_id = $teacher_id
			)
			";
		elseif ($cohort_id)
			$query = "SELECT award.course_location_id, course_name, course_start_date, course_end_date, student_id, award_id, award_title, reason, camper_of_the_week, best_in_class, teacher.first_name, teacher.last_name, teacher.nickname FROM award INNER JOIN course_location ON award.course_location_id = course_location.course_location_id INNER JOIN course ON course_location.course_id = course.course_id LEFT JOIN teacher ON award.teacher_id = teacher.teacher_id WHERE award.course_location_id = '$clid'
			and
			student_id in (
				select course_registration.student_id from course_registration where course_registration.course_location_id = $clid and course_registration.cohort_id = $cohort_id
			)
			";
		else
			$query = "SELECT award.course_location_id, course_name, course_start_date, course_end_date, student_id, award_id, award_title, reason, camper_of_the_week, best_in_class, teacher.first_name, teacher.last_name, teacher.nickname FROM award INNER JOIN course_location ON award.course_location_id = course_location.course_location_id INNER JOIN course ON course_location.course_id = course.course_id LEFT JOIN teacher ON award.teacher_id = teacher.teacher_id WHERE award.course_location_id = '$clid'";
		$result = mysqli_query($connection,$query);
		if (!$result) {
				die(mysqli_error($connection));
		}
		while ($row = mysqli_fetch_assoc($result)) {
			$row['award_title'] = $award_obj->to_html($row['award_title']);
			$row['reason'] = $award_obj->to_html($row['reason']);
			$row['first_name'] = ucfirst($row['first_name']);
			$row['last_name'] = ucfirst($row['last_name']);
			$row['teacher_first_name'] = $row['first_name'];
			$row['teacher_last_name'] = $row['last_name'];
			$row['course_end_date'] = $row['course_end_date'];
			$awards[] = $row;
		}
			return $awards;
	}
	function get_awards_by_location_and_student_id($location_id, $student_id, $start_date = "") {
      $new_1 = $start_date;
      $start_date = $student_id;
      $student_id = $new_1;
        $awards = array();
		$award_obj = new Award();
        $connection = connect_database();
        if ($start_date) {
			$query = sprintf("SELECT award.course_location_id, course_name, course_start_date, course_end_date, student_id, award_id, award_title, reason, camper_of_the_week, best_in_class, teacher.first_name, teacher.last_name, teacher.nickname FROM award INNER JOIN course_location ON award.course_location_id = course_location.course_location_id INNER JOIN course ON course_location.course_id = course.course_id LEFT JOIN teacher ON award.teacher_id = teacher.teacher_id WHERE course_location.location_id = '%s' AND course_start_date = '%s'", $location_id, $start_date);
        } else {
			$query = sprintf("SELECT award.course_location_id, course_name, course_start_date, course_end_date, student_id, award_id, award_title, reason, camper_of_the_week, best_in_class, teacher.first_name, teacher.last_name, teacher.nickname FROM award INNER JOIN course_location ON award.course_location_id = course_location.course_location_id INNER JOIN course ON course_location.course_id = course.course_id LEFT JOIN teacher ON award.teacher_id = teacher.teacher_id WHERE course_location.location_id = '%s'", $location_id);
        }
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
          if ($row['student_id'] != $student_id) continue;
          $row['award_title'] = $award_obj->to_html($row['award_title']);
          $row['reason'] = $award_obj->to_html($row['reason']);
          $row['first_name'] = ucfirst($row['first_name']);
          $row['last_name'] = ucfirst($row['last_name']);
          $row['teacher_first_name'] = $row['first_name'];
          $row['teacher_last_name'] = $row['last_name'];
          $awards[] = $row;
        }
        return $awards;
    }
	function get_account_by_student_id($student_id) {
		$account = array();
		$connection = connect_database();
		$query = sprintf("SELECT registrar_last_name, registrar_first_name, account_info.email, account_info.secondary_email, account_info.account_id, account_info.city, account_info.best_number, account_info.secondary_cell_phone, account_info.owner_name, account_info.cell_phone, account_info.work_phone, account_info.how_hear, home_phone FROM account_info, student_info WHERE student_id = '%s' AND student_info.account_id = account_info.account_id", $student_id);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		$row = mysqli_fetch_assoc($result);
		return $row;
	}
	function get_age($date_of_birth) {
		$current_date = date('Y-m-d');
        if (!empty($current_date))
          $cd = explode("-", $current_date);
        else
          $cd = array(0,0,0);
        if (!empty($date_of_birth))
          $db = explode("-", $date_of_birth);
        else
          $db = array(0,0,0);
		// has birthday passed this year?
		if ($cd[1] >= $db[1] && $cd[2] >= $db[2]) {
			return ($cd[0] - $db[0]);
		} else {
			return ($cd[0] - $db[0] - 1);
		}
	}
	function student_withdraw($course_registration_id) {
	  $connection = connect_database();
	  $query = sprintf("SELECT student_id FROM course_registration WHERE course_registration_id = %s", $course_registration_id);
	  $result = mysqli_query($connection,$query);
	  $row = mysqli_fetch_assoc($result);
	  $student_id = $row["student_id"];
	  $change_before = array();
	  if ($student_id && function_exists('pb_student_change_capture_snapshot')) {
	    $change_before = pb_student_change_capture_snapshot($student_id);
	  }
	  if ($student_id) {
	    $query = sprintf("update `points` set `points` = `points` - 20 where `student_id` = %s", $student_id);
	    $result = mysqli_query($connection,$query);
	  }
	  $query = sprintf("DELETE FROM course_registration WHERE course_registration_id = %s", $course_registration_id);
	  $result = mysqli_query($connection,$query);
	  if ($student_id && function_exists('pb_student_change_record_event')) {
	    $change_after = pb_student_change_capture_snapshot($student_id);
	    pb_student_change_record_event($change_before, $change_after, 'course_withdraw', 'admin_student_withdraw.php', isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0, $course_registration_id);
	  }
	}
	function create_account_id() {
        /* create a 9-digit random number */
        $random_number = rand(100000000, 999999999);
        $connection = connect_database();
        $query = sprintf("SELECT COUNT(*) FROM account_info WHERE account_id = '%s'", $random_number);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_row($result);
        if ($row[0] >= 1) {
            create_account_id();
        }
        return $random_number;
    }
	function create_student_id() {
        /* create a 9-digit random number */
        $random_number = rand(100000000, 999999999);
        $connection = connect_database();
        $query = sprintf("SELECT COUNT(*) FROM student_info WHERE student_id = '%s'", $random_number);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_row($result);
        if ($row[0] >= 1) {
            create_student_id();
        }
        return $random_number;
    }
	function format_date($date) {
		if (preg_match('/^0000-00-00$/', $date)) {
			return "";
		}
        list($year, $month, $day) = explode("-", $date);
        $month += 0;
        $months = array('', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $month_name = $months[$month];
        $month_abbr = substr($month_name, 0, 3);
        return sprintf("%s %d, %04d", $month_abbr, $day, $year);
    }
	function format_datetime($datetime) {
        if (preg_match('/^0000-00-00/', $datetime)) {
            return "";
        }
        if (preg_match('/^(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)$/', $datetime, $match)) {
            $year = $match[1];
            $month = $match[2];
            $day = $match[3];
            $hour = $match[4];
            $minute = $match[5];
            $second = $match[6];
		} else if (preg_match('/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/', $datetime, $match)) {
			$year = $match[1];
			$month = $match[2];
			$day = $match[3];
			$hour = $match[4];
			$minute = $match[5];
			$second = $match[6];
		}
        $month += 0;
        $months = array('', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $month_name = $months[$month];
        $month_abbr = substr($month_name, 0, 3);
        return sprintf("%s %d, %04d %02d:%02d", $month_abbr, $day, $year, $hour, $minute);
    }
	function location_active($location_id) {
		$connection = connect_database();
		$query = sprintf("SELECT active FROM location WHERE location_id = '%s'", $location_id);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_row($result);
		if ($row[0] == 'y') {
			return 1;
		} else {
			return 0;
		}
	}
	function get_receive_email() {
		$emails = array();
		$connection = connect_database();
		$query = sprintf("SELECT registrar_first_name, registrar_last_name, email FROM account_info WHERE receive_email = 'y' ORDER BY email");
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $emails[] = $row;
        }
		return $emails;
	}
	function get_receive_brochure() {
		$addresses = array();
		$connection = connect_database();
		$query = sprintf("SELECT registrar_first_name, registrar_last_name, street_address, city, state, zip FROM account_info WHERE receive_brochure = 'y' ORDER BY registrar_last_name");
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$addresses[] = $row;
		}
		return $addresses;
	}
	function update_course_registration($course_registration_id, $status) {
		$connection = connect_database();
		$query = sprintf("UPDATE course_registration SET status = '%s' WHERE course_registration_id = '%s'", $status, $course_registration_id);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
	}
	function get_pending($order_by = "student_last_name") {
		$pending = array();
		$connection = connect_database();
		$query = sprintf("SELECT course_location.course_location_id, course_location.course_start_date, course_registration.student_id, course_registration.course_registration_id, student_first_name, student_last_name, course_name, status FROM course_registration, student_info, course_location, course WHERE status = 'p' AND course_registration.student_id = student_info.student_id AND course_registration.course_location_id = course_location.course_location_id AND course_location.course_id = course.course_id ORDER BY $order_by");
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name'])
;
			$pending[] = $row;
		}
		return $pending;	
	}
	function get_transactions($account_id) {
        $transactions = array();
        $connection = connect_database();
//        $query = sprintf("SELECT transaction_id, transaction_date FROM transaction WHERE account_id = '%s'", $account_id);
        $query = "SELECT transaction_id, DATE_FORMAT(transaction_date, '%Y%m%d%H%i%S') AS `date` FROM transaction WHERE account_id = $account_id order by `date` desc";
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $transactions[] = array(
                'transaction_id' => $row['transaction_id'],
                'transaction_date' => $row['date'],
            );
        }
        for ($i = 0; $i < count($transactions); $i++) {
            $transaction_id = $transactions[$i]['transaction_id'];
            $items = array();
            $query = sprintf("SELECT course_location_id, student_id, course_cost FROM transaction_item WHERE transaction_id = '%s'", $transaction_id);
            $result = mysqli_query($connection,$query);
            if (!$result) {
                die(mysqli_error($connection));
            }
            while ($row = mysqli_fetch_assoc($result)) {
                $items[] = array(
                    'course_location_id' => $row['course_location_id'],
                    'student_id' => $row['student_id'],
                    'course_cost' => $row['course_cost'],
                );
            }
            $transactions[$i]['items'] = $items;
        }
        return $transactions;
    }
	function get_course_registration_status($course_location_id, $student_id) {
        $connection = connect_database();
        $query = sprintf("SELECT status FROM course_registration WHERE course_location_id = '%s' AND student_id = '%s'", $course_location_id, $student_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_assoc($result);
        return $row['status'];
    }
	function get_course_registration_extended_care($course_location_id, $student_id) {
        $connection = connect_database();
        $query = sprintf("SELECT extended_care FROM course_registration WHERE course_location_id = '%s' AND student_id = '%s'", $course_location_id, $student_id);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        $row = mysqli_fetch_assoc($result);
        return $row['extended_care'];
    }
	function get_student_week($student_id, $course_location_id, $course_start_date = "") {
		$week = 0;
		$connection = connect_database();
		// determine the camp 
		$query = sprintf("SELECT camp_id FROM course_location WHERE course_location_id = '%s'", $course_location_id);
		$result = mysqli_query($connection,$query);
		if (!$result) {
            die(mysqli_error($connection));
        }
		$row = mysqli_fetch_assoc($result);
		$camp_id = $row['camp_id'];
		if ($course_start_date) {
			$query = sprintf("SELECT DISTINCT(course_start_date) FROM course_registration, course_location WHERE student_id = '%s' AND course_location.camp_id = '%s' AND course_registration.course_location_id = course_location.course_location_id AND course_start_date <= '%s'", $student_id, $camp_id, $course_start_date);
		} else {
			$query = sprintf("SELECT DISTINCT(course_start_date) FROM course_registration, course_location WHERE student_id = '%s' AND course_location.camp_id = '%s' AND course_registration.course_location_id = course_location.course_location_id AND course_start_date <= NOW()", $student_id, $camp_id);
		}
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$week++;
		}
		return $week;	
	}
	function get_course_location_camp($course_location_id) {
		$connection = connect_database();
		$query = sprintf("SELECT camp.name, camp.camp_id FROM camp, course_location WHERE course_location_id = '%s' AND course_location.camp_id = camp.camp_id", $course_location_id);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		$row = mysqli_fetch_assoc($result);
		return $row;
	}
	function get_camp_by_camp_id($camp_id) {
		$connection = connect_database();
		$query = sprintf("SELECT camp_id, name FROM camp WHERE camp_id = '%s'", $camp_id);
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		$row = mysqli_fetch_assoc($result);
		return $row;
	}
	function get_camps() {
		$camps = array();
		$connection = connect_database();
		$query = sprintf("SELECT camp_id, name FROM camp");
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		while ($row = mysqli_fetch_assoc($result)) {
			$camps[] = $row;
		}
	
		return $camps;
	}
	function add_camp($p) {
		$name = $p['name'];
		$connection = connect_database();
		$query = sprintf("INSERT INTO camp (name) VALUES ('%s')", $name);
		if (!mysqli_query($connection,$query)) {
			die(mysqli_error($connection));
		}
		return 1;
	}
	function update_camp($p) {
		$camp_id = $p['camp_id'];
		$name = $p['name'];
		$connection = connect_database();
		$query = sprintf("UPDATE camp SET name = '%s' WHERE camp_id = '%s'", $name, $camp_id);
		if (!mysqli_query($connection,$query)) {
            die(mysqli_error($connection));
        }
        return 1;
	}
	function format_grade_range($start_grade, $end_grade) {
        $grade_string = "";
        if ($start_grade != NULL) {
            if ($start_grade == -1) {
                $start_grade = 'Pre-K';
            } else if ($start_grade == 0) {
                $start_grade = 'K';
            }
        }
        if ($end_grade != NULL) {
            if ($end_grade == -1) {
                $end_grade = 'Pre-K';
            } else if ($end_grade == 0) {
                $end_grade = 'K';
            }
        }
        if ($start_grade != NULL) {
            if ($start_grade == $end_grade) {
                $grade_string = $start_grade;
            } else {
                $grade_string = "$start_grade - $end_grade";
            }
        }
        return $grade_string;
    }
	function format_time_range($start_time, $end_time) {
		$time_string = "";
		list($start_time_hour, $start_time_minute) = explode(":", $start_time);
		list($end_time_hour, $end_time_minute) = explode(":", $end_time);
		$start_time_am_pm = ($start_time_hour >= 13) ? 'pm' : 'am';
		$end_time_am_pm = ($end_time_hour >= 13) ? 'pm' : 'am';
		if ($start_time_am_pm == 'pm') {
			$start_time_hour -= 12;
		}
		if ($end_time_am_pm == 'pm') {
			$end_time_hour -= 12;
		}
    	if ($start_time != NULL && $start_time != '00:00:00') {
        	$time_string = sprintf("%02d:%02d %s - %02d:%02d %s", $start_time_hour, $start_time_minute, $start_time_am_pm, $end_time_hour, $end_time_minute, $end_time_am_pm);
    	}
		return $time_string;
	}
	function set_error($error) {
        $this->error = $error;
    }
    function error() {
        return $this->error;
    }
    // Kaal
    function make_proper_case($name) {
      if (strlen($name)==2) return $name;
      $name = preg_replace('/^\s+/', '', $name);
      $name = preg_replace('/\s+/', ' ', $name);
      $name = ucfirst(strtolower($name));
      $updated_name = '';
      if (preg_match('/^(.+)\-(.+)/', $name, $match)) {
	  $name = ucfirst($match[1]) . "-" . ucfirst($match[2]);
      }
      if (preg_match('/\s/', $name, $match)) {
	  $name_parts = explode(" ", $name);
	  $updated_name = '';
	  for ($i = 0; $i < count($name_parts); $i++) {
	      $updated_name .= ucfirst($name_parts[$i]);
	      if (($i + 1) < count($name_parts)) {
		  $updated_name .= " ";
	      }
	  }
	  $name = $updated_name;
      }
      // if (strlen($name)==2) $name = ucfirst($name);
      if (preg_match('/\'/', $name, $match)) {
	$one = explode("'",$name);
	foreach ($one as $key => $value) {
	  $one[$key] = ucfirst($value);
	}
	$name = implode("'",$one);
      }
      if (substr(strtoupper($name),0,2)=="MC") $name = "Mc".ucfirst(substr($name,2,strlen($name)));
      return $name;
    }
    // Kaal
    function make_proper_case_last_name($name) {
       if (strlen($name)==2) return $name;
       $name = preg_replace('/^\s+/', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        $name = ucfirst(strtolower($name));
        $updated_name = '';
        if (preg_match('/^(.+)\-(.+)/', $name, $match)) {
            $name = ucfirst($match[1]) . "-" . ucfirst($match[2]);
        }
        if (preg_match('/\s/', $name, $match)) {
            $name_parts = explode(" ", $name);
            $updated_name = '';
            for ($i = 0; $i < count($name_parts); $i++) {
                $updated_name .= ucfirst($name_parts[$i]);
                if (($i + 1) < count($name_parts)) {
                    $updated_name .= " ";
                }
            }
            $name = $updated_name;
        }
	// if (strlen($name)==2) $name = strtoupper($name);
	if (preg_match('/\'/', $name, $match)) {
	  $one = explode("'",$name);
	  foreach ($one as $key => $value) {
	    $one[$key] = ucfirst($value);
	  }
	  $name = implode("'",$one);
	}
	if (substr(strtoupper($name),0,2)=="MC") $name = "Mc".ucfirst(substr($name,2,strlen($name)));
        return $name;
    }
    function get_current_year() {
        list($year, $month, $day) = explode("-", date('Y-m-d'));
        if ($month < 8) {
//          $year_to_display = $year;
            $year_to_display = $year - 1;
        } else if ($month == 8) {
//          $year_to_display = $year;
            $year_to_display = $year - 1;
        } else {
//          $year_to_display = $year + 1;
            $year_to_display = $year;
        }
        return $year_to_display;
    }
    function get_year_to_display() {
        list($year, $month, $day) = explode("-", date('Y-m-d'));
        if ($month < 8) {
            $year_to_display = $year;
        } else if ($month == 8) {
            $year_to_display = $year;
        } else {
            $year_to_display = $year + 1;
        }
        return $year_to_display;
    }
    function increment_grade_level($grade_level) {
        if ($grade_level == 'Pre-K') {
            $grade_level = 'K';
        } else if ($grade_level == 'K') {
            $grade_level = 1;
        } else if ($grade_level == '12') {
            $grade_level = 12;
        } else {
            $grade_level++;
        }
        return $grade_level;
    }
    function get_transaction_pending() {
        $transaction_pending = array();
        $connection = connect_database();
        $query = sprintf("SELECT * FROM transaction_pending ORDER BY transaction_pending_date DESC"); 
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $transaction_pending[] = $row;
        }
        return $transaction_pending;
    }
    function get_transaction_pending_by_status($status) {
        $transaction_pending = array();
        $connection = connect_database();
        $query = sprintf("
            SELECT
                tp.*,
                ai.registrar_first_name,
                ai.registrar_last_name,
                ai.email,
                ai.secondary_email
            FROM transaction_pending tp
            LEFT JOIN account_info ai ON ai.account_id = tp.account_id
            WHERE tp.status = '%s'
            ORDER BY tp.transaction_pending_date DESC
        ", $status);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $transaction_pending[] = $row;
        }
        return $transaction_pending;
    }
    function set_transaction_pending($transaction_id, $status) {
        $connection = connect_database();
        $query = sprintf("UPDATE transaction_pending SET status = '%s' WHERE id = '%s'", $status, $transaction_id);
        mysqli_query($connection,$query);
    }
    // Kaal
    
    function get_courses_by_camp_id($camp_id) {
    $courses = array();		
    $connection = connect_database();
    $query = sprintf("
      SELECT 
	`course`.`course_id` as `course_id`, 
	start_grade, 
	end_grade, 
	location.school_name,
	`course_location`.`course_location_id`,
	`course_location`.`course_start_date`,
	`course_location`.`course_end_date`,
	`course`.`course_name`
      FROM 
	`course_location`,
	`course`,
	location 
      where 
	`course_location`.`camp_id` = '%s' 
      and 
	`course`.`course_id`=`course_location`.`course_id` 
      and 
	location.location_id=course_location.location_id 
      and
	course_location.active = 'y'
      and 
	course.active = 'y'
      and 
	location.active = 'y'
      ORDER BY 
	`course_name`,`course_start_date` asc
      ", $camp_id);
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) {
	    $courses[] = $row;
    }
    return $courses;
    }
    
    function get_courses_by_school_id($school_id) {
      $courses = array();
      $connection = connect_database();
      $query = sprintf("SELECT course_cost, course_image, course_description, course_day, start_time, end_time, start_grade, end_grade, location.school_name,`course_location`.`course_location_id`,`course_location`.`course_start_date`,`course_location`.`course_end_date`,`course`.`course_name` FROM `course_location`,`course`,location where `location`.`location_id` = '%s' and `course`.`course_id`=`course_location`.`course_id` and location.location_id=course_location.location_id ORDER BY `course_name`,`course_start_date` asc", $school_id);
      $result = mysqli_query($connection,$query);
      while ($row = mysqli_fetch_assoc($result)) {
	      $courses[] = $row;
      }
      return $courses;
    }
    
    function get_students_by_camp_id($camp_id) {
    $students = array();
    $connection = connect_database();
    $query = sprintf("
    select 
      distinct course_registration.student_id,
--       course_location.course_id,
--       course.course_name,
      student_info.student_first_name,student_info.student_last_name
    from
      course_registration,course_location,course,student_info
    where
      course_registration.student_id = student_info.student_id
      and course_registration.course_location_id = course_location.location_id
      and course_location.course_id = course.course_id
      and course_location.camp_id = 27
    group by
      course_registration.student_id
    order by
      student_info.student_last_name asc", $camp_id);
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) {
	    $students[] = $row;
    }
    return $students;
    }
    function get_students_by_course_id($course_id,$camp_id) {
    $students = array();		
    $connection = connect_database();
    $query = "
    select course_registration.student_id,student_info.student_last_name,student_info.student_first_name from course_registration,student_info where course_location_id in (
    SELECT distinct location_id FROM `course_location` where course_id=$course_id and camp_id=$camp_id)
    and
    student_info.student_id=course_registration.student_id
    ORDER BY student_info.student_last_name asc
    ";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) {
	    $students[] = $row;
    }
    return $students;
    }
    function get_student_experience($student_id, $course_location_id) {
      if (empty($course_location_id)) return 0;
      $experience_count = 0;
      $connection = connect_database();
      $query = sprintf("SELECT `course_id` FROM `course_location` WHERE `course_location_id` = $course_location_id");
      $result = mysqli_query($connection,$query);
      $row = mysqli_fetch_assoc($result);
      if ($row["course_id"]) $course_id = $row["course_id"]; else $course_id = 0;
      if ($course_id) {
	$query = sprintf("SELECT `course_foc` FROM `course` WHERE `course_id` = $course_id");
	$result = mysqli_query($connection,$query);
	$row = mysqli_fetch_assoc($result);
	if ($row["course_foc"]) $course_foc = $row["course_foc"]; else $course_foc = 0;
      }
      
      if ($course_foc) {
	if (preg_match("/,/",$course_foc)) {
	  $one = explode(",",$course_foc);
	  foreach ($one as $value) $course_foc_array[] = trim($value);
	} else {
	  $course_foc_array[] = trim($course_foc);
	}
      }
      
      if ($student_id && $course_foc) {
	foreach ($course_foc_array as $course_foc_value) {
	  $query = "
	  SELECT 
	    count(*) as `focus_count` 
	  FROM 
	    course, 
	    course_location, 
	    course_registration, 
	    location 
	  WHERE 
	    student_id = '$student_id' 
	  AND 
	    course_registration.course_location_id = course_location.course_location_id 
	  AND 
	    course.course_id = course_location.course_id 
	  AND 
	    course_location.location_id = location.location_id 
	  and 
	    `course_foc` like '%$course_foc_value%'
	  and
	    `course_end_date` < now()
	    ";
	  $result = mysqli_query($connection,$query);
	  $row = mysqli_fetch_assoc($result);
	  if ($row["focus_count"]) $experience_count = $row["focus_count"];
	}
      }
      return $experience_count;
    }
    function get_student_overall_experience($student_id) {
      $experience_count = 0;
      $connection = connect_database();
      if ($student_id) {
	$query = "
	SELECT 
	  count(*) as `focus_count` 
	FROM course, 
	  course_location, 
	  course_registration, 
	  location 
	WHERE 
	  student_id = '$student_id' 
	AND 
	  course_registration.course_location_id = course_location.course_location_id 
	AND 
	  course.course_id = course_location.course_id 
	AND 
	  course_location.location_id = location.location_id
	and
	  `course_end_date` < now()
	";
	$result = mysqli_query($connection,$query);
	$row = mysqli_fetch_assoc($result);
	if ($row["focus_count"]) $experience_count = $row["focus_count"];
      }
      return $experience_count;
    }
    
    function get_student_weekly_badge($student_id) {
      $rwd = new rwd();
      $camp_id = $rwd->get_current_camp_id();
      $admin = new Admin();
      $courses = $admin->get_courses_by_camp_id($camp_id);
      foreach ($courses as $value) $course_location_ids[] = $value["course_location_id"];
      $weekly_count = 0;
      $connection = connect_database();
      if ($student_id) {
	$query = "
	select
	  count(*) as `weekly_count` 
	from
	  course_location, 
	  course_registration
	where
	  course_registration.student_id = '$student_id' 
	and 
	  course_registration.course_location_id = course_location.course_location_id
	and
	  course_location.course_start_date < now()
	and
	  course_location.course_location_id in (
	  ";
	$x = 1;
	foreach ($course_location_ids as $value) {
	  if (count($course_location_ids)==$x)
	    $query .= "$value";
	  else
	    $query .= "$value,";
	  $x++;
	}
	$query .= "
	  )
	";
	$result = mysqli_query($connection,$query);
	$row = mysqli_fetch_assoc($result);
	if ($row["weekly_count"]) $weekly_count = 0 - $row["weekly_count"];
      }
      return $weekly_count;
    }    

    function get_badge_path($course_id) {
      $badge_path = "";
      $connection = connect_database();
      if ($course_id) {
	$query = "
	select
	  path as `badge_path`
	from
	  badges
	where
	  course_id = $course_id 
	and
	  published = 1
	";
	$result = mysqli_query($connection,$query);
	$row = mysqli_fetch_assoc($result);
	if ($row["badge_path"]) $badge_path = $row["badge_path"];
      }
      return $badge_path;
    }

    function get_badge_id($course_id) {
      $badge_id = "";
      $connection = connect_database();
      if ($course_id) {
	$query = "
	select
	  id as `badge_id`
	from
	  badges
	where
	  course_id = $course_id 
	and
	  published = 1
	";
	$result = mysqli_query($connection,$query);
	$row = mysqli_fetch_assoc($result);
	if ($row["badge_id"]) $badge_id = $row["badge_id"];
      }
      return $badge_id;
    }

    function get_badge_title($course_id) {
      $badge_title = "";
      $connection = connect_database();
      if ($course_id) {
	$query = "
	select
	  title as `badge_title`
	from
	  badges
	where
	  course_id = $course_id
	and
	  published = 1
	";
	$result = mysqli_query($connection,$query);
	$row = mysqli_fetch_assoc($result);
	if ($row["badge_title"]) $badge_title = $row["badge_title"];
      }
      return $badge_title;
    }

    function get_student_current_course_badge($student_id) {
      $rwd = new rwd();
      $camp_id = $rwd->get_current_camp_id();
      $admin = new Admin();
      $courses = $admin->get_courses_by_camp_id($camp_id);
      foreach ($courses as $value) $course_location_ids[] = $value["course_location_id"];
      $course_id = 0;
      $connection = connect_database();
      if ($student_id) {
	$query = "
	select
	  course_location.course_id as `course_id`
	from
	  course_location, 
	  course_registration
	where
	  course_registration.student_id = '$student_id' 
	and 
	  course_registration.course_location_id = course_location.course_location_id
	and
	  course_location.course_start_date < now()
	and
	  now() < course_location.course_end_date 
	and
	  course_location.course_location_id in (
	  ";
	$x = 1;
	foreach ($course_location_ids as $value) {
	  if (count($course_location_ids)==$x)
	    $query .= "$value";
	  else
	    $query .= "$value,";
	  $x++;
	}
	$query .= "
	  )
	 order by
	  course_location.course_start_date desc
	 limit 0,1;
	";
	$result = mysqli_query($connection,$query);
	$row = mysqli_fetch_assoc($result);
	if ($row["course_id"]) $course_id = $row["course_id"];
      }
      return $course_id;
    }    
      
    function award_student_weekly_badge($student_id,$student_weekly_badge_id) {
      $badges_student_id = "";
      $connection = connect_database();
      $sql_1 = '
	select 
	  `badges_student`.`id` as `badges_student_id`
	from 
	  `badges_student`,
	  `badges` 
	where 
	  `badges_student`.`student_id` = '.$student_id.' 
	and
	  `badges`.`id` = `badges_student`.badge_id 
	and
	  `badges`.`course_id` < 0
	and
	  `badges`.`published` = 1
	  ';
      $result = mysqli_query($connection,$sql_1);
      $row = mysqli_fetch_assoc($result);
      if ($row["badges_student_id"]) $badges_student_id = $row["badges_student_id"];
      if ($badges_student_id) {
	$sql_2 = 'delete from `badges_student` where `id` = '.$badges_student_id;
	mysqli_query($connection,$sql_2);
      }
      $sql_3 = 'insert into `badges_student` (`student_id`,`badge_id`) values ('.$student_id.','.$student_weekly_badge_id.')';
      if (mysqli_query($connection,$sql_3))
	return 1;
      else
	return 0;
    }
      
    function award_student_course_badge($student_id,$badge_id) {
      $count = 0;
      $connection = connect_database();
      $sql_1 = '
	select
	  count(*) as `count`
	from 
	  `badges_student`
	where 
	  `badges_student`.`student_id` = '.$student_id.'
	and
	  `badges_student`.badge_id = '.$badge_id.'
	  ';
      $result = mysqli_query($connection,$sql_1);
      $row = mysqli_fetch_assoc($result);
      if ($row["count"]) $count = $row["count"];
      if (!$count) {
	$sql_2 = 'insert into `badges_student` (`student_id`,`badge_id`) values ('.$student_id.','.$badge_id.')';
	if (mysqli_query($connection,$sql_2))
	  return 1;
	else
	  return 0;
      } else {
	return 1;
      }
    }
    
    function get_student_summer_badge_path($student_id) {
      $badge_id = 0;
      $path = "";
      $connection = connect_database();
      $sql_1 = '
	select 
	  `badges_student`.`badge_id` as `badge_id`
	from 
	  `badges_student`,
	  `badges` 
	where 
	  `badges_student`.`student_id` = "'.$student_id.'" 
	and
	  `badges`.`id` = `badges_student`.badge_id 
	and
	  `badges`.`course_id` < 0
	  ';
      $result = mysqli_query($connection,$sql_1);
      $row = mysqli_fetch_assoc($result);
      if ($row["badge_id"]) $badge_id = $row["badge_id"];
      if ($badge_id) {
	$sql_1 = '
	  select 
	    `path`
	  from 
	    `badges` 
	  where 
	    `id` = '.$badge_id;
	$result = mysqli_query($connection,$sql_1);
	$row = mysqli_fetch_assoc($result);
	if ($row["path"]) $path = $row["path"];
	if ($path)
	  return $path;
	else
	  return "";
      } else {
	return "";
      }
    }
    function get_transaction_pending_by_status_limited($status,$x,$y) {
      if (!$x) $x = "0";
      if (!$y) $y = "50";
        $transaction_pending = array();
        $connection = connect_database();
        $x = (int)$x;
        $y = (int)$y;
        $query = sprintf("
            SELECT
                tp.*,
                ai.registrar_first_name,
                ai.registrar_last_name,
                ai.email,
                ai.secondary_email
            FROM transaction_pending tp
            LEFT JOIN account_info ai ON ai.account_id = tp.account_id
            WHERE tp.status = '%s'
            ORDER BY tp.transaction_pending_date DESC
            LIMIT $x,$y
        ", $status);
        $result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $transaction_pending[] = $row;
        }
        return $transaction_pending;
    }
    function get_wait_list_number($student_id,$course_location_id) {
      $connection = connect_database();
      $sql = "SELECT student_id FROM `wait_list` WHERE `course_location_id` = $course_location_id order by wait_list_id asc";
      $result = mysqli_query($connection,$sql);
      $count = 0;
      while ($row = mysqli_fetch_assoc($result)) {
	$count ++;
	if ($row["student_id"] == $student_id) { $return = $count; break; }
      }
      return $count;
    }
  function log_hit($email) {
		// '=''or'@email.com
    $connection = connect_database();
    if (isset($_SERVER["HTTP_REFERER"])&&$_SERVER["HTTP_REFERER"])
      $referer = $_SERVER["HTTP_REFERER"];
    else
      $referer = "";
    $sql = "
    insert into
      `logs` (`username`,`ip`,`hostname`,`site`,`url`,`language`,`browser`";
    $sql .= ",`referer`)
    values
      (
      '".$email ."',
      '".$_SERVER["REMOTE_ADDR"]."',
      '".gethostbyaddr($_SERVER['REMOTE_ADDR'])."',
      '".$_SERVER['HTTP_HOST']."',
      '".$_SERVER["REQUEST_URI"]."',
      '".$_SERVER["HTTP_ACCEPT_LANGUAGE"]."',
      '".$_SERVER["HTTP_USER_AGENT"]."',";
    $sql .= "'".$referer."'
      );";
    if (mysqli_query($connection,$sql)) {
      $string  = "Email: <b>".$email."</b><br/><br/>";
      $string .= "IP: <b>".$_SERVER["REMOTE_ADDR"]."</b><br/>";
      $string .= "Language: <b>".$_SERVER["HTTP_ACCEPT_LANGUAGE"]."</b><br/>";
      $string .= "Hostname: <b>".gethostbyaddr($_SERVER['REMOTE_ADDR'])."</b><br/><br/>";
      $string .= "Site: <b>".$_SERVER['HTTP_HOST']."</b><br/>";
      $string .= "URL: <b>".$_SERVER["REQUEST_URI"]."</b><br/>";
      // $string .= "Referer: <b>".$referer."</b><br/>";
      return array(mysqli_insert_id($connection),$string);
    } else 
      return 0;
  }
  function update_hit($id,$x=1) {
    $connection = connect_database();
    $sql = "update `logs` set logged_in = $x where id = $id";
    mysqli_query($connection,$sql);
    return 1;
  }
  function mark_registration_cancelled ($course_registration_id) {
    $connection = connect_database();
    $sql = "update `course_registration` set late_cancelled = 1 where course_registration_id = $course_registration_id";
    if (mysqli_query($connection,$sql))
      return 1;
    else
      return 0;
  }
  function get_late_cancelled($course_registration_id) {
    $connection = connect_database();
    $sql = "select late_cancelled from `course_registration` where course_registration_id = $course_registration_id";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $late_cancelled = $row["late_cancelled"];
    if ($late_cancelled)
      return $late_cancelled;
    else
      return 0;
  }
  function mark_pb_picks($course_registration_id) {
    $connection = connect_database();
    $sql = "update `course_registration` set pb_picks = 1 where course_registration_id = $course_registration_id";
    if (mysqli_query($connection,$sql))
      return 1;
    else
      return 0;
  }
  function mark_extended_care($course_registration_id) {
		$course_registration_id = $course_registration_id + 1;
		$course_registration_id = $course_registration_id - 1;
    $connection = connect_database();
    $sql = "update `course_registration` set extended_care = 0 where course_registration_id = $course_registration_id";
    if (is_int($course_registration_id) && mysqli_query($connection,$sql))
      return 1;
    else
      return 0;
  }
  function add_extended_care($course_registration_id) {
		$course_registration_id = $course_registration_id + 1;
		$course_registration_id = $course_registration_id - 1;
    $connection = connect_database();
    $sql = "update `course_registration` set extended_care = 1 where course_registration_id = $course_registration_id";
    if (is_int($course_registration_id) && mysqli_query($connection,$sql))
      return 1;
    else
      return 0;
  }
  function mark_pick_up($student_id,$pickedup) {
    $connection = connect_database();
    if ($pickedup)
			$sql = "update `student_info` set prizes_picked_up_2022 = now(), prizes_picked_up = now() where student_id = '$student_id'";
		else
			$sql = "update `student_info` set prizes_picked_up_2022 = NULL, prizes_picked_up = NULL where student_id = '$student_id'";
    if (mysqli_query($connection,$sql))
      return 1;
    else
      return 0;
  }
  function get_pb_picks($course_registration_id) {
    $connection = connect_database();
    $sql = "select pb_picks from `course_registration` where course_registration_id = $course_registration_id";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $pb_picks = $row["pb_picks"];
    if ($pb_picks)
      return $pb_picks;
    else
      return 0;
  }

	function get_pick_up_persons($student_id, $date, $location_id) {
    $connection = connect_database();
    $sql = "SELECT app FROM `student_info` where student_id = '$student_id'";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $app = $row["app"];
    if ($app)
      return $app;
    else
      return "";
	}

  function get_student_pizza_preference($student_id,$date,$course_location_id) {
    if (!$date) return "";
    $connection = connect_database();
    // $sql = "select pizza_preference from `student_info` where student_id = $student_id";
    $sql = "select `type` from `pizza_count` where student_id = $student_id and `date` = '$date' and `location` = '$course_location_id'";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $pizza_preference = $row["type"];
    if ($pizza_preference)
      return $pizza_preference;
    else
      return "";
  }
  function update_student_pizza_preference($student_id, $pizza_preference) {
    $connection = connect_database();
    $sql = "update `student_info` set pizza_preference = '$pizza_preference' where student_id = $student_id";
    if (mysqli_query($connection,$sql)) return 1;
  }
  function get_pizza_count($type,$date,$location_string, $admin_stem_tech) {
    $connection = connect_database();
    $sql = "
select
	count(id) as count_result
from
	pizza_count,
	course_location
where
	course_location.course_location_id = pizza_count.location
and
	`type` = '$type'
and
	`date` = '$date'
and
	`location` in ($location_string)
";
	if ($admin_stem_tech != 0) {
		if ($admin_stem_tech == 1) $sql .= " and course_type = 'j'";
		if ($admin_stem_tech == 2) $sql .= " and course_type = 's'";
	}
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $count_result = $row["count_result"];
    if ($count_result)
      return $count_result;
    else
      return 0;
  }
  function insert_pizza_count($student_id,$type,$date,$location) {
    $connection = connect_database();
    $sql = "
  select
    `id`
  from
    pizza_count
  where
    student_id = $student_id
  and
    date = '$date'
  ";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $id = $row['id'];
    if ($id) {
      $sql = "
      update
	pizza_count
      set 
	`type` = '$type'
      where
	`id` = $id
      ";
      if (mysqli_query($connection,$sql)) return 1;
    } else {
      $sql = "
      insert into 
	pizza_count (`student_id`,`type`,`date`,`location`)
      values
	($student_id,'$type','$date','$location')
      ";
      if (mysqli_query($connection,$sql)) return 1;
    }
  }
  function unset_student_pizza_preference($student_id, $pizza_preference) {
    $connection = connect_database();
    $sql = "update `student_info` set pizza_preference = '' where student_id = $student_id";
    if (mysqli_query($connection,$sql)) return 1;
  }
  function unset_pizza_count($student_id,$type,$date,$location) {
    $connection = connect_database();
    $sql = "
  select
    `id`
  from
    pizza_count
  where
    student_id = $student_id
  and
    date = '$date'
  ";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $id = $row['id'];
    if ($id) {
      $sql = "
      delete from
	pizza_count
      where
	`id` = $id
      ";
      if (mysqli_query($connection,$sql)) return 1;
    }
  }
  function get_teacher_pizza($course_location_id,$teacher_id,$date) {
    if (!$teacher_id) return "";
    $connection = connect_database();
    $sql = "select `type` from `pizza_count` where teacher_id = $teacher_id and `date` = '$date' and `location` = '$course_location_id'";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    return $row["type"];
  }
  function get_pizzas($course_location_ids,$date) {
    if (!is_array($course_location_ids)) return 0;
    $connection = connect_database();
    foreach ($course_location_ids as $course_location_id) {
      $teacher = $this->get_teacher_by_course_location_id($course_location_id);
      $students = $this->get_students_by_course_location_id($course_location_id);
      $teacher_pizza = $this->get_teacher_pizza($course_location_id,$teacher["teacher_id"],$date);
      $result[$teacher["teacher_id"]]["name"] = $teacher["first_name"]." ".$teacher["last_name"];
      if ($teacher["nickname"]) $result[$teacher["teacher_id"]]["name"] .= " (".$teacher["nickname"].")";
      $result[$teacher["teacher_id"]]["pizza"] = $teacher_pizza;
      foreach ($students as $student)
	$result[$teacher["teacher_id"]]["kids"][$student["student_id"]] = array("name"=>$student["student_first_name"]." ".$student["student_last_name"],"pizza"=>$student["pizza_preference"]);
    }      
    if ($result)
      return $result;
    else
      return 0;
  }
  function insert_pizza_count_teacher($teacher_id,$type,$date,$location) {
    $connection = connect_database();
    $sql = "
  select
    `id`
  from
    pizza_count
  where
    teacher_id = $teacher_id
  and
    date = '$date'
  ";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $id = $row['id'];
    if ($id) {
      $sql = "
      update
	pizza_count
      set 
	`type` = '$type'
      where
	`id` = $id
      ";
      if (mysqli_query($connection,$sql)) return 1;
    } else {
      $sql = "
      insert into 
	pizza_count (`teacher_id`,`type`,`date`,`location`)
      values
	($teacher_id,'$type','$date','$location')
      ";
      if (mysqli_query($connection,$sql)) return 1;
    }
  }
  function unset_pizza_count_teacher($teacher_id,$type,$date,$location) {
    $connection = connect_database();
    $sql = "
  select
    `id`
  from
    pizza_count
  where
    teacher_id = $teacher_id
  and
    date = '$date'
  ";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $id = $row['id'];
    if ($id) {
      $sql = "
      delete from
	pizza_count
      where
	`id` = $id
      ";
      if (mysqli_query($connection,$sql)) return 1;
    }
  }
  function get_teacher_pizza_preference($teacher_id,$date,$course_location_id) {
    if (!$date) return "";
    $connection = connect_database();
		if (empty($teacher_id))
			$sql = "select `type` from `pizza_count` where `date` = '$date' and `location` = '$course_location_id'";
		else
			$sql = "select `type` from `pizza_count` where teacher_id = $teacher_id and `date` = '$date' and `location` = '$course_location_id'";
		// echo $sql;exit;
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $pizza_preference = $row["type"];
    if ($pizza_preference)
      return $pizza_preference;
    else
      return "";
  }
  function get_course_location($course_location_id) {
    $connection = connect_database();
    $sql = "select `school_name` from location, course_location where location.location_id = course_location.location_id and course_location.course_location_id = '$course_location_id'";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $school_name = $row["school_name"];
    if (preg_match('/Berkeley/i',$school_name)) return 'bk';
    if (preg_match('/Beverly Hills/i',$school_name)) return 'bh';
    if (preg_match('/Burbank/i',$school_name)) return 'bur';
    if (preg_match('/Encino/i',$school_name)) return 'en';
    if (preg_match('/Irvine/i',$school_name)) return 'ir';
    if (preg_match('/Manhattan Beach/i',$school_name)) return 'mb';
    if (preg_match('/Marin/i',$school_name)) return 'rs';
    if (preg_match('/Santa Monica/i',$school_name)) return 'sm';
    if (preg_match('/Westchester/i',$school_name)) return 'wc';    
    if (preg_match('/Pasadena/i',$school_name)) return 'pas';    
    if ($school_name)
      return $school_name;
    else
      return "";
  }
  function get_no_awards_by_location($location_id, $start_date = "") {
    $connection = connect_database();
    if ($start_date) {      
$query = "
SELECT 
  DISTINCT student_info.student_id, 
  student_last_name,
  student_first_name,
  course_name,
  course_start_date, 
  course_end_date, 
  course_location.course_location_id,
  teacher_course_location.teacher_id
FROM 
  course, 
  course_location, 
  student_info, 
  course_registration,
  teacher_course_location
WHERE
  course_location.course_start_date = '$start_date' 
AND 
  course_location.location_id = '$location_id' 
and 
  course.course_id != 466
AND 
  course.course_id = course_location.course_id 
AND 
  course_registration.course_location_id = course_location.course_location_id 
AND 
  course_registration.student_id = student_info.student_id 
and
  teacher_course_location.course_location_id = course_location.course_location_id
and student_info.student_id not in (      
  SELECT 
    distinct(award.student_id)
  FROM 
    award, 
    course_location
  WHERE 
    course_location.location_id = '$location_id' 
  AND 
    award.course_location_id = course_location.course_location_id 
  AND 
    course_start_date = '$start_date'
)
";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    }
    return $rows;
  }
  function already_attended($student_id,$date) {
    $connection = connect_database();
//     student_id
//     camp_id
//     course_registration
//     settings
//     $start_date = $_GET["date"];

    $year = date('Y',strtotime($date));
    $this_year = $date;
    $last_year = date("$year-01-01");
//     echo $last_year;
//     exit;

  $sql = "select distinct(student_id) from student_info where account_id = (select account_id from student_info where student_id = $student_id)";    
  $rs6 = mysqli_query($connection,$sql);
  while ($row = mysqli_fetch_assoc($rs6)) $student_ids[] = $row["student_id"];
  $student_ids_text = implode(",",$student_ids);
  
    $query = "
select 
  count(course_registration_id) as count_result
from 
  course_registration,
  course_location
where 
  course_location.course_location_id = course_registration.course_location_id  
and 
  course_location.camp_id = (select `value` from `settings` where `key` = 'current_camp')
and
  student_id in ($student_ids_text)
and
  student_id not in (
      select distinct(student_id) from course_registration where course_location.course_start_date < '$this_year' and course_location.course_start_date > '$last_year' 
	and
      student_id in ($student_ids_text)
  )
  
";

//   echo "<pre>".$query;
//   exit;
// 	if ($location_id) $query .= " AND course_location.location_id = '$location_id'";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    
//     return 0;
//     echo $row["count_result"];
//     exit;
    if ($row["count_result"] > 1)
//     if ($row["count_result"])
      return $row["count_result"];
    else
    return 0;    
  }
  function get_payment_method($transaction_id) {
    $connection = connect_database();
    $sql = "select `status` from `report_registrations` where transaction_pending_id = $transaction_id order by `id` desc limit 0,1";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $status = $row["status"];
    if ($status)
      return $status;
    else
      return "";
  }
  function get_last_four_digits($transaction_id) {
    $connection = connect_database();
    $sql = "select `last_four_digits` from `report_registrations` where transaction_pending_id = $transaction_id order by `id` desc limit 0,1";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $last_four_digits = $row["last_four_digits"];
    if ($row["last_four_digits"]=="0000") $last_four_digits = "0000";
    elseif (strlen($last_four_digits) == 1) $last_four_digits = "000".$last_four_digits;
    elseif (strlen($last_four_digits) == 2) $last_four_digits = "00".$last_four_digits;
    elseif (strlen($last_four_digits) == 3) $last_four_digits = "0".$last_four_digits;
    if ($last_four_digits)
      return $last_four_digits;
    else
      return "";
  }
  function get_paypal_transaction_id($transaction_id) {
    $connection = connect_database();
    $sql = "select `paypal_transaction_id` from `report_registrations` where transaction_pending_id = $transaction_id order by `id` desc limit 0,1";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $paypal_transaction_id = $row["paypal_transaction_id"];
    if ($paypal_transaction_id)
      return $paypal_transaction_id;
    else
      return "";
  }
  function get_next_transaction_item_id($transaction_id,$student_id_string) {
    $connection = connect_database();
    $sql = "
      select
	transaction_item_id
      from
	transaction_item
      where
	transaction_id = $transaction_id
      and
	student_id not in ($student_id_string)
      order by
	transaction_item_id asc
      limit 
	0,1
    ";  
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $transaction_item_id = $row['transaction_item_id'];
    if ($transaction_item_id) return $transaction_item_id; else return 0;
  }
  function get_next_transaction_item_id_cost($transaction_item_id) {
    $connection = connect_database();
    $sql = "
      select
	course_cost
      from
	transaction_item
      where
	transaction_item_id = $transaction_item_id
      order by
	transaction_item_id asc
      limit 
	0,1
    ";  
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $course_cost = $row['course_cost'];
    if ($course_cost) return $course_cost; else return 0;
  }
  function update_transaction_item_id_cost($transaction_item_id, $difference_result) {
    $connection = connect_database();
    $sql = "
      update
	transaction_item
      set
	course_cost = $difference_result
      where
	transaction_item_id = $transaction_item_id
    ";
    if (mysqli_query($connection,$sql))
      return 1;
    else
      return 0;
  }
  function unset_student_state($student_id, $course_location_id) {
    $connection = connect_database();
    $sql = "update `course_registration` set state = NULL where student_id = $student_id and course_location_id = $course_location_id";
    if (mysqli_query($connection,$sql)) return 1;
  }
  function update_student_state($set, $student_id, $course_location_id) {
    $connection = connect_database();
    $sql = "update `course_registration` set state = '$set' where student_id = $student_id and course_location_id = $course_location_id";
    if (mysqli_query($connection,$sql)) return 1;
  }
  function get_student_state($student_id, $course_location_id) {
    $connection = connect_database();
    $sql = "select `state` from `course_registration` where student_id = $student_id and course_location_id = $course_location_id";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    $state = $row["state"];
    if ($state)
      return $state;
    else
      return "";
  }
	
	function get_students_by_state($course_location_id = null, $order_by = "gender, grade_level") {
    $unique_students = array();
    $students = array();

    // Enrollment for each summer opens Dec 1 of the prior year.
    // If we're in Dec or later, current season started this Dec 1.
    // If before Dec, current season started last Dec 1.
    $current_month = (int) date('n');
    $current_year = (int) date('Y');
    if ($current_month >= 12) {
        $season_start = "$current_year-12-01 00:00:00";
    } else {
        $prev_year = $current_year - 1;
        $season_start = "$prev_year-12-01 00:00:00";
    }

    $connection = connect_database();

    $clid_filter = "";
    if ($course_location_id) {
        $course_location_id = mysqli_real_escape_string($connection, $course_location_id);
        $clid_filter = "AND course_registration.course_location_id = '$course_location_id'";
    }

    $base_query = "SELECT `state`, course_location_id, seating_order, pizza_preference, 
        course_registration_id, student_info.student_id, student_first_name, 
        student_last_name, camper_image, email, date_of_birth, gender, pronoun, 
        emerg_name, emerg_phone, allergies, t_shirt_size, 
        friend_to_be_grouped_with, status 
        FROM student_info, course_registration 
        WHERE `state` <> 'NULL' 
        AND course_registration.student_id = student_info.student_id
        AND course_registration.registration_date > '$season_start'
        $clid_filter
        ORDER BY gender DESC, date_of_birth ASC";

    $result = mysqli_query($connection, $base_query);
    if (!$result) {
        die(mysqli_error($connection));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $student_id = $row['student_id'];
        if (!array_key_exists($student_id, $unique_students)) {
            $row['student_first_name'] = ucfirst($row['student_first_name']);
            $row['student_last_name'] = ucfirst($row['student_last_name']);
            $students[] = $row;
            $unique_students[$student_id] = 1;
        }
    }

    // If sorting by grade level, fetch and attach grade info
    if (preg_match('/grade_level/', $order_by)) {
        for ($i = 0; $i < count($students); $i++) {
            $student_id = $students[$i]['student_id'];
            $grade_level = $this->get_grade_level_by_current_year($student_id);
            $students[$i]['grade_level'] = $grade_level;
        }
    }

    return $students;
}
	
	function get_total_camp_report_by_start_date_sorted($start_date = "") {
	  $students = array();
	  $connection = connect_database();
		if ($start_date) {
			//$query = sprintf("SELECT student_info.student_id, student_last_name, student_first_name, cteacher, room, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, registration_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_start_date > '2009-09-01' AND course_location.course_start_date = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id ORDER BY gender, date_of_birth", $start_date);
			$query = sprintf("SELECT camper_image, student_info.student_id, student_last_name, student_first_name, cteacher, room, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_inst, course_start_date, course_end_date, registration_date, course_location.course_location_id, course_location.location_id, location.school_name FROM course, location, course_location, student_info, course_registration WHERE course_location.location_id = location.location_id AND course_location.active = '%s' AND course_location.course_start_date = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id 
            and course.course_id != 466
			ORDER BY student_last_name asc", "y", $start_date);
		} else {
			//$query = sprintf("SELECT student_info.student_id, student_last_name, student_first_name, cteacher, room, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_start_date, course_end_date, registration_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_start_date > '2009-09-01' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id ORDER BY gender, date_of_birth", $start_date);
			$query = sprintf("SELECT camper_image, student_info.student_id, student_last_name, student_first_name, cteacher, room, gender, pronoun, emerg_name, emerg_phone, allergies, t_shirt_size, date_of_birth, course_name, course_inst, course_start_date, course_end_date, registration_date, course_location.course_location_id FROM course, course_location, student_info, course_registration WHERE course_location.active = '%s' AND course.course_id = course_location.course_id AND course_registration.course_location_id = course_location.course_location_id AND course_registration.student_id = student_info.student_id 
            and course.course_id != 466
			ORDER BY student_last_name asc", "y");
		}
		$result = mysqli_query($connection,$query);
        while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name']);
			$row['student_last_name'] = ucfirst($row['student_last_name']);
			$students[] = $row;
		}
		return $students;
	}
  function get_no_awards($start_date = "") {
    $connection = connect_database();
    if ($start_date) {
$query = "
SELECT 
  DISTINCT student_info.student_id, 
  student_last_name, 
  student_first_name, 
  course_name, 
  course_start_date, 
  course_end_date, 
  course_location.course_location_id 
FROM 
  course, 
  course_location, 
  student_info, 
  course_registration 
WHERE 
  course_location.course_start_date = '$start_date' 
and 
  course.course_id != 466
AND 
  course.course_id = course_location.course_id 
AND 
  course_registration.course_location_id = course_location.course_location_id 
AND 
  course_registration.student_id = student_info.student_id 

and student_info.student_id not in (      
  SELECT 
    distinct(award.student_id)
  FROM 
    award, 
    course_location
  WHERE 
    award.course_location_id = course_location.course_location_id 
  AND 
    course_start_date = '$start_date'
)
";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    }
    return $rows;
  }
  function get_students_not_done_for_summer($start_date = "") {
    $connection = connect_database();
    if ($start_date) {
$query = "
SELECT 
  DISTINCT student_info.student_id, 
  student_last_name, 
  student_first_name, 
  course_name, 
  course_start_date, 
  course_end_date, 
  course_location.course_location_id 
FROM 
  course, 
  course_location, 
  student_info, 
  course_registration 
WHERE 
  course_location.course_start_date > '$start_date' 
and 
  course.course_id != 466
AND 
  course.course_id = course_location.course_id 
AND 
  course_registration.course_location_id = course_location.course_location_id 
AND 
  course_registration.student_id = student_info.student_id 
-- and student_info.student_id not in (      
--   SELECT 
--     distinct(award.student_id)
--   FROM 
--     award, 
--     course_location
--   WHERE 
--     award.course_location_id = course_location.course_location_id 
--   AND 
--     course_start_date = '$start_date'
-- )
-- and student_info.student_id not in (      
--   SELECT 
--     course_registration.student_id
--   FROM 
--     course_registration, 
--     course_location
--   WHERE 
--     course_location.course_location_id = course_registration.course_location_id
-- 
--   AND 
--     course_location.course_start_date >= '$start_date'
-- )
";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    }
    return $rows;
  }
  function is_account_done_for_summer($account_id, $date) {
    $connection = connect_database();
    if ($date) {
      $query = "
select 
  count(course_registration_id) as count_result
from
  course_registration,
  course_location
where
  course_registration.course_location_id = course_location.course_location_id 
and
  course_location.course_start_date > '$date' 
and
  course_registration.student_id in (
    SELECT 
      student_id 
    FROM 
      `student_info` 
    where 
      account_id = '$account_id'
  )
";
      $result = mysqli_query($connection,$query);
      $row = mysqli_fetch_assoc($result);
      $count_result = $row["count_result"];
      return $count_result;
    }
  }
  function get_logins($email) {
    $connection = connect_database();
    if ($email) {
      $query = "
SELECT 
  time, hostname
FROM 
  `logs`
where 
  username like '$email' 
ORDER BY 
  `logs`.`id` DESC 
limit
 0, 10
";
      $result = mysqli_query($connection,$query);
      while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
      foreach($rows as $key_1 => $value_1) {
        foreach($value_1 as $key_2 => $value_2) {
          if ($key_2 == "time")
            $results .= "<font color=blue>".$value_2."</font>";
          else if ($key_2 == "hostname")
            $results .= "&nbsp;&nbsp;&nbsp;<font color=green>".$value_2."</font><br/>";
        }
      }
      return $results;
    }
  }
  function get_students_not_done_for_summer_by_location($location_id,$start_date = "") {
    $connection = connect_database();
    if ($start_date) {
$query = "
SELECT 
  DISTINCT student_info.student_id, 
  student_last_name, 
  student_first_name, 
  course_name, 
  course_start_date, 
  course_end_date, 
  course_location.course_location_id 
FROM 
  course, 
  course_location, 
  student_info, 
  course_registration 
WHERE 
  course_location.course_start_date > '$start_date' 
and 
  course.course_id != 466
AND 
  course.course_id = course_location.course_id 
AND 
  course_registration.course_location_id = course_location.course_location_id 
AND 
  course_registration.student_id = student_info.student_id 
and
  location_id = $location_id
";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    }
    return $rows;
  }
  function is_online($student_id, $course_location_id) {
    $connection = connect_database();
    if (!$student_id) return 0;
    if (!$course_location_id) return 0;
    $query = "
select 
  count(course_registration_id) as count_result
from
  course_registration
where
  student_id = $student_id
and 
  course_location_id = $course_location_id
and
  online = 1
";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    $count_result = $row["count_result"];
    return $count_result;
  }
  function get_in_course_options($location_id) {
    $connection = connect_database();
    if (!$location_id) return '';
    $query = "SELECT course_registration.course_location_id, course_start_date, school_name, location, location.location_id FROM course_registration, course_location, location WHERE course_location.course_location_id = course_registration.course_location_id and location.location_id = course_location.location_id and location <> 0 and location = $location_id order by course_start_date asc";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    return $rows;
  }
  function get_in_course_options_by_location($location_id, $date, $order_by = 'cohort_id') {
    $connection = connect_database();
    if (!$date) return '';
    if (!$location_id) return '';
    $query = "SELECT student_info.school, course_registration.cohort_id, course_registration.teacher_id, date_of_birth,gender,pronoun,t_shirt_size,friend_to_be_grouped_with, course.course_name, student_info.student_id, student_first_name, student_last_name, course_registration.course_location_id, course_start_date, school_name, location, location.location_id FROM course, student_info, course_registration, course_location, location WHERE course_location.course_location_id = course_registration.course_location_id and location.location_id = course_location.location_id and location <> 0 and location = $location_id and course_start_date = '$date' and student_info.student_id = course_registration.student_id and course.course_id = course_location.course_id order by $order_by desc";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    return $rows;
  }
  function get_laptop_rental($course_location_id, $student_id) {
    $connection = connect_database();
    if (!$course_location_id) return 0;
    if (!$student_id) return 0;
    $query = "SELECT rent_laptop FROM `course_registration` WHERE course_location_id = '$course_location_id' and student_id = '$student_id' order by course_registration_id desc limit 0,1 ";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row["rent_laptop"];
  }
  function get_registration_location($course_location_id, $student_id) {
    $connection = connect_database();
    if (!$course_location_id) return 0;
    if (!$student_id) return 0;
    $query = "SELECT location, city FROM `course_registration`, location WHERE location.location_id = course_registration.location and course_location_id = '$course_location_id' and student_id = '$student_id' order by course_registration_id desc limit 0,1 ";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    if (!$row["location"]) return array("ONLINE",100);
    return array($row["city"], $row["location"]);
  }
  function get_location_capacity($location_id, $course_start_date) {
    $connection = connect_database();
    if (!$location_id) return 0;
    if (!$course_start_date) return 0;
    $query = "SELECT capacity FROM `course_capacity` WHERE location_id = $location_id and course_start_date = '$course_start_date' order by id asc limit 0,1 ";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row["capacity"];
  }
  function update_location_capacity($location_id, $course_start_date, $capacity) {
    $connection = connect_database();
    if (!$location_id) return 0;
    if (!$course_start_date) return 0;
    $query = "update `course_capacity` set capacity = '$capacity' WHERE location_id = $location_id and course_start_date = '$course_start_date'";
    if (mysqli_query($connection,$query)) return 1; else return 0;
  }
  function get_teacher_location_id($teacher_id) {
    $connection = connect_database();
    if (!$teacher_id) return 0;
    $query = "SELECT location_id FROM `teacher_location` WHERE teacher_id = $teacher_id order by teacher_location_id asc limit 0,1";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row["location_id"];
  }
  function get_teacher_location_name($teacher_id) {
    $connection = connect_database();
    if (!$teacher_id) return '';
    $query = "SELECT school_name FROM `teacher_location`, location WHERE location.location_id = teacher_location.location_id and teacher_id = $teacher_id order by teacher_location_id asc limit 0,1";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row["school_name"];
  }
  function get_teacher_location_details($location_id) {
    $connection = connect_database();
    // if (!$location_id) return '';
    $query = "select 
	teacher_location.teacher_id,
	nickname,
	teacher_course_location.course_location_id,
	course_location.course_id,
	course_start_date,
	course_name
from
	teacher_location,
	teacher,
	teacher_course_location,
	course_location,
	course
where
	course_start_date > '2021-05-17'
and
	teacher_location.location_id = $location_id
and
	teacher_location.teacher_id = teacher.teacher_id
and
	teacher_location.teacher_id = teacher_course_location.teacher_id
and
	teacher_course_location.course_location_id = course_location.course_location_id
and
	course_location.course_id = course.course_id";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    return $rows;
  }
  function get_teacher_location_students($location_id, $course_location_id, $teacher_id) {
    $connection = connect_database();
    if (!$location_id) return 0;
    if (!$course_location_id) return 0;
    if (!$teacher_id) return 0;
    $query = "SELECT count(course_registration_id) as count_result FROM `course_registration` WHERE course_location_id = '$course_location_id' and teacher_id = '$teacher_id'";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row["count_result"];
  }
  function get_course_registration_teacher_id($student_id, $course_location_id) {
    $connection = connect_database();
    if (!$student_id) return 0;
    if (!$course_location_id) return 0;
    $query = "SELECT teacher_id FROM `course_registration` WHERE course_location_id = $course_location_id and student_id = $student_id";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row["teacher_id"];
  }
  function get_course_registration_cohort_id($student_id, $course_location_id) {
    $connection = connect_database();
    if (!$student_id) return 0;
    if (!$course_location_id) return 0;
    $query = "SELECT cohort_id FROM `course_registration` WHERE course_location_id = $course_location_id and student_id = $student_id";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row["cohort_id"];
  }
  function get_teacher_ids($location_id_converted = 0) {
    $connection = connect_database();
    if ($location_id_converted)
			$query = "SELECT 
	teacher.teacher_id, 
    teacher.nickname 
FROM 
	`teacher`,teacher_location 
WHERE 
-- 	teacher.email <> '' 
-- and
	teacher.teacher_id = teacher_location.teacher_id
and
	teacher_location.location_id = $location_id_converted
order by
	teacher.nickname asc";
		else
			$query = "SELECT teacher_id, nickname FROM `teacher` WHERE email <> '' order by nickname asc";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result))
			$rows[] = $row;
    return $rows;
  }
  function assign_teacher($teacher_id, $student_id, $course_location_id) {
    $connection = connect_database();
    $query = "update course_registration set teacher_id = '$teacher_id' where student_id = $student_id and course_location_id = $course_location_id";
    if (mysqli_query($connection,$query)) return 1; else return 0;
  }
  function assign_cohort($cohort_id, $student_id, $course_location_id) {
    $connection = connect_database();
    $query = "update course_registration set cohort_id = '$cohort_id' where student_id = $student_id and course_location_id = $course_location_id";
    if (mysqli_query($connection,$query)) return 1; else return 0;
  }
  function is_cohort_teacher($cohort_id, $course_location_id) {
    $connection = connect_database();
    $query = "select count(teacher_course_location_id) as count_result from teacher_course_location where teacher_id = $cohort_id and course_location_id = $course_location_id";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row['count_result'];
  }
  function get_summer_laptop_rentals($location_id, $date) {
    $connection = connect_database();
    $query = "
SELECT 
	count(course_registration_id) as count_result 
FROM 
	course_registration,
    course_location
WHERE 
	course_registration.location = $location_id
and
	course_registration.course_location_id = course_location.course_location_id
and
	course_location.course_start_date = '$date'
and
	course_registration.rent_laptop = 1    
    ";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row['count_result'];
  }
  function get_teacher_ids_by_clid($clid) {
    $connection = connect_database();
    if ($clid)
			$query = "
SELECT 
	teacher.teacher_id, 
	teacher.nickname 
FROM 
	`teacher`,teacher_course_location 
WHERE 
--	teacher.email <> '' 
-- and
	teacher.teacher_id = teacher_course_location.teacher_id
and
	teacher_course_location.course_location_id = $clid
order by
	teacher.nickname asc";
		else
			$query = "SELECT teacher_id, nickname FROM `teacher` WHERE email <> '' order by nickname asc";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result))
			$rows[] = $row;
    return $rows;
  }
	function get_online_campers($date='') {
    $sql = "
select
	count(course_registration_id) as count_result
from
	course_registration, course_location    
where
	course_location.course_location_id = course_registration.course_location_id";
		if ($date) $sql .= " and course_start_date = '$date'";
		$sql .= " and course_location.active = 'y'";
		$sql .= " and course_registration.location = '0'";
    $connection = connect_database();
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    return $row["count_result"];
	}
  function get_in_course_options_by_date($date = '') {
    $connection = connect_database();
		$query = "SELECT 
			cohort_id,
			camper_image,
			emerg_name ,
			emerg_phone ,
			allergies,
			course_end_date,
			course_registration.teacher_id, date_of_birth,gender,pronoun,t_shirt_size,friend_to_be_grouped_with, course.course_name, student_info.student_id, student_first_name, student_last_name, course_registration.course_location_id, course_start_date, school_name, location, location.location_id FROM course, student_info, course_registration, course_location, location WHERE course_location.course_location_id = course_registration.course_location_id and location.location_id = course_location.location_id and course_start_date = '$date' and student_info.student_id = course_registration.student_id and course.course_id = course_location.course_id and course_registration.location = '0' order by course_name asc";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    return $rows;
  }
  function get_city($location_id) {
		if (!$location_id) return "";
    $connection = connect_database();
    $query = "SELECT city FROM `location` WHERE location_id = $location_id";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row["city"];
  }
	function get_total_camp_report_by_start_date_sorted_csv($start_date = "") {
	  $students = array();
	  $connection = connect_database();
		$query = "
SELECT 
	camper_image, 
	student_info.student_id, 
	student_last_name, 
	student_first_name, 
	cteacher, 
	room, 
	gender, 
	pronoun, 
	emerg_name, 
	emerg_phone, 
	allergies, 
	t_shirt_size, 
	date_of_birth, 
	course_name, 
	course_inst, 
	course_start_date, 
	course_end_date, 
	registration_date, 
	course_location.course_location_id,
	course_location.location_id as location,
	course_registration.teacher_id,
	course_registration.cohort_id,
	course_registration.rent_laptop,
	location.welcome_path
FROM 
	course, 
	course_location, 
	student_info, 
	course_registration,
	location
WHERE 
	course_location.active = 'y' 
AND 
	course_location.course_start_date = '$start_date' 
-- AND 
-- 	location.location_id = course_registration.location
and
  location.location_id = course_location.location_id
and
  course_location.course_location_id = course_registration.course_location_id
AND 
	course.course_id = course_location.course_id 
AND 
	course_registration.course_location_id = course_location.course_location_id 
AND 
	course_registration.student_id = student_info.student_id 
AND
	course.course_id != 466
ORDER BY 
	student_last_name asc
";
		$result = mysqli_query($connection,$query);
      if (!empty($result))
		while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name']);
			$row['student_last_name'] = ucfirst($row['student_last_name']);
			$students[] = $row;
		}
		return $students;
	}
  function get_pizzas_by_location($location_id, $date) {
    if (!$location_id) return 0;
    $connection = connect_database();
    $sql = "
SELECT
student_first_name as fname,
student_last_name as lname,
pizza_count.type as pizza_type,
student_info.student_id
from
student_info,
pizza_count
where
student_info.student_id = pizza_count.student_id
and 
pizza_count.location = $location_id

union

SELECT
first_name as fname,
last_name as lname,
pizza_count.type as pizza_type,
pizza_count.student_id
from
teacher,
pizza_count
where
teacher.teacher_id = pizza_count.teacher_id
and 
pizza_count.location = $location_id

order by
	fname asc
";
		$result = mysqli_query($connection,$sql);
		while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
		return $rows;
  }
function get_awards_by_location_100($location_id, $start_date = "") {
			$awards = array();
			$award_obj = new Award();
			$connection = connect_database();
		$query = "
SELECT 
	award.course_location_id, 
	course_name, 
	course_start_date, 
	course_end_date, 
	award.student_id, 
	award_id, 
	award_title, 
	reason, 
	camper_of_the_week, 
	best_in_class, 
	teacher.first_name, 
	teacher.last_name, 
	teacher.nickname 
FROM 
	award, 
	course, 
	course_location, 
	teacher, 
	course_registration 
WHERE 
	course_location.location_id = '$location_id' 
AND 
	award.course_location_id = course_location.course_location_id 
AND 
	course_location.course_id = course.course_id 
AND 
	award.teacher_id = teacher.teacher_id 
AND 
	course_start_date = '$start_date'
and
	award.student_id = course_registration.student_id 
";
			$result = mysqli_query($connection,$query);
			if (!$result) {
					die(mysqli_error($connection));
			}
			while ($row = mysqli_fetch_assoc($result)) {
		$row['award_title'] = $award_obj->to_html($row['award_title']);
					$row['reason'] = $award_obj->to_html($row['reason']);
		$row['first_name'] = ucfirst($row['first_name']);
		$row['last_name'] = ucfirst($row['last_name']);
		$row['teacher_first_name'] = $row['first_name'];
		$row['teacher_last_name'] = $row['last_name'];
					$awards[$row['award_id']] = $row;
			}
			return $awards;
	}
  function get_no_awards_by_location_100 ($location_id, $start_date = "") {
    $connection = connect_database();
    if ($start_date) {      
$query = "
SELECT 
  DISTINCT student_info.student_id, 
  student_last_name, 
  student_first_name, 
  course_name, 
  course_start_date, 
  course_end_date, 
  course_location.course_location_id 
FROM 
  course, 
  course_location, 
  student_info, 
  course_registration 
WHERE 
	course_location.location_id = '$location_id' 
and
  course_location.course_start_date = '$start_date' 
and 
  course.course_id != 466
AND 
  course.course_id = course_location.course_id 
AND 
  course_registration.course_location_id = course_location.course_location_id 
AND 
  course_registration.student_id = student_info.student_id 

and student_info.student_id not in (      
  SELECT 
    distinct(award.student_id)
  FROM 
    award, 
    course_registration
  WHERE 
    course_registration.location = '$location_id' 
  AND 
    award.course_location_id = course_registration.course_location_id
  AND 
    course_start_date = '$start_date'
)
";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    }
    return $rows;
  }
function get_awards_by_location_and_student_id_100($location_id, $student_id, $start_date = "") {
      $new_1 = $start_date;
      $start_date = $student_id;
      $student_id = $new_1;
	$awards = array();
	$award_obj = new Award();
			$connection = connect_database();
		$query = "		
SELECT 
	distinct(award_id), 
	award.course_location_id, 
	course_name, 
	course_start_date, 
	course_end_date, 
	award.student_id, 
	award_title, 
	reason, 
	camper_of_the_week, 
	best_in_class, 
	teacher.first_name, 
	teacher.last_name, 
	teacher.nickname 
FROM 
	award, 
	course, 
	course_location, 
	teacher, 
	course_registration 
WHERE 
	award.course_location_id = course_location.course_location_id 
AND 
	course_location.course_id = course.course_id 
AND 
	award.teacher_id = teacher.teacher_id 
AND 
	course_start_date = '$start_date' 
and 
	award.student_id = course_registration.student_id
and
	award.student_id = '$student_id'
";
			$result = mysqli_query($connection,$query);
			if (!$result) {
					die(mysqli_error($connection));
			}
			while ($row = mysqli_fetch_assoc($result)) {
				if ($row['student_id'] != $student_id) continue;
				$row['award_title'] = $award_obj->to_html($row['award_title']);
				$row['reason'] = $award_obj->to_html($row['reason']);
				$row['first_name'] = ucfirst($row['first_name']);
				$row['last_name'] = ucfirst($row['last_name']);
				$row['teacher_first_name'] = $row['first_name'];
				$row['teacher_last_name'] = $row['last_name'];
				$awards[$row['award_id']] = $row;
			}
			return $awards;
	}
	function get_total_camp_report_by_location_gender_online($location_id, $start_date = "", $course_location = 0) {
        $students = array();
        $connection = connect_database();
        if ($course_location)
	  $course_location_text = "course_location_id";
	else
	  $course_location_text = "location_id";
            $query = "
SELECT 
	teacher_id, 
	cohort_id, 
	student_info.student_id, 
	student_last_name, 
	student_first_name, 
	camper_image, 
	gender, 
	pronoun, 
	emerg_name, 
	emerg_phone, 
	allergies, 
	t_shirt_size, 
	date_of_birth, 
	course_name, 
	course_start_date, 
	course_end_date, 
	course_location.course_location_id 
FROM 
	course, 
	course_location, 
	student_info, 
	course_registration 
WHERE 
	course_location.active = 'y' 
AND 
	course_location.course_start_date = '$start_date' 
AND 
	course_registration.location = 0 
AND 
	course.course_id = course_location.course_id 
AND 
	course_registration.course_location_id = course_location.course_location_id 
AND 
	course_registration.student_id = student_info.student_id 
and 
	course.course_id != 466
ORDER BY 
	student_last_name ASC, gender, pronoun, date_of_birth
";
        $result = mysqli_query($connection,$query);
        while ($row = mysqli_fetch_assoc($result)) {
			$row['student_first_name'] = ucfirst($row['student_first_name']);
			$row['student_last_name'] = ucfirst($row['student_last_name']);
            $students[] = $row;
        }
        return $students;
    }	
  function get_cohort_id($course_location_id, $student_id) {
    $connection = connect_database();
    if (!$course_location_id) return 0;
    if (!$student_id) return 0;
    $query = "SELECT cohort_id FROM `course_registration` WHERE course_location_id = '$course_location_id' and student_id = '$student_id' order by course_registration_id desc limit 0,1 ";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row["cohort_id"];
  }
  function get_teacher_id($course_location_id, $student_id) {
    $connection = connect_database();
    if (!$course_location_id) return 0;
    if (!$student_id) return 0;
    $query = "SELECT teacher_id FROM `course_registration` WHERE course_location_id = '$course_location_id' and student_id = '$student_id' order by course_registration_id desc limit 0,1 ";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row["teacher_id"];
  }
function get_awards_by_location_100_by_cohort($location_id, $start_date = "") {
	$location_id_converted = $this->get_location_id_converted($location_id);
	$awards = array();
	$award_obj = new Award();
	$connection = connect_database();
	$query = "
SELECT 
	award.course_location_id, 
	course_name, 
	course_start_date, 
	course_end_date, 
	award.student_id, 
	award_id, 
	award_title, 
	reason, 
	camper_of_the_week, 
	best_in_class, 
	teacher.first_name, 
	teacher.last_name, 
	teacher.nickname,
	course_registration.cohort_id,
	course_registration.teacher_id
FROM 
	award, 
	course, 
	course_location, 
	teacher, 
	course_registration 
WHERE 
course_registration.teacher_id in (
	SELECT `teacher_id` FROM `teacher_location` where `location_id` = '$location_id_converted'
)
AND 
	award.course_location_id = course_location.course_location_id 
AND 
	course_location.course_id = course.course_id 
AND 
	award.teacher_id = teacher.teacher_id 
AND 
	course_start_date = '$start_date'
and
	award.student_id = course_registration.student_id 
and
	award.teacher_id in (
		SELECT `teacher_id` FROM `teacher_location` where `location_id` = '$location_id_converted'
	)		
order by
	award.teacher_id asc
";
			$result = mysqli_query($connection,$query);
			if (!$result) {
					die(mysqli_error($connection));
			}
			while ($row = mysqli_fetch_assoc($result)) {
		$row['award_title'] = $award_obj->to_html($row['award_title']);
					$row['reason'] = $award_obj->to_html($row['reason']);
		$row['first_name'] = ucfirst($row['first_name']);
		$row['last_name'] = ucfirst($row['last_name']);
		$row['teacher_first_name'] = $row['first_name'];
		$row['teacher_last_name'] = $row['last_name'];
					$awards[$row['award_id']] = $row;
			}
			return $awards;
	}
  function get_no_awards_by_location_100_by_cohort ($location_id, $start_date = "") {
		$location_id_converted = $this->get_location_id_converted($location_id);
    $connection = connect_database();
    if ($start_date) {      
$query = "
SELECT 
  DISTINCT student_info.student_id, 
  student_last_name, 
  student_first_name, 
  course_name, 
  course_start_date, 
  course_end_date, 
  course_location.course_location_id,
  course_registration.teacher_id,
  course_registration.cohort_id
FROM 
  course, 
  course_location, 
  student_info, 
  course_registration 
WHERE 
course_registration.teacher_id in (
	SELECT `teacher_id` FROM `teacher_location` where `location_id` = '$location_id_converted'
)
and
  course_location.course_start_date = '$start_date' 
and 
  course.course_id != 466
AND 
  course.course_id = course_location.course_id 
AND 
  course_registration.course_location_id = course_location.course_location_id 
AND 
  course_registration.student_id = student_info.student_id 
and student_info.student_id not in (
SELECT 
	award.student_id
FROM 
	award, 
	course, 
	course_location, 
	teacher, 
	course_registration 
WHERE 
course_registration.teacher_id in (
	SELECT `teacher_id` FROM `teacher_location` where `location_id` = '$location_id_converted'
)
AND 
	award.course_location_id = course_location.course_location_id 
AND 
	course_location.course_id = course.course_id 
AND 
	award.teacher_id = teacher.teacher_id 
AND 
	course_start_date = '$start_date'
and
	award.student_id = course_registration.student_id 
and
	award.teacher_id in (
		SELECT `teacher_id` FROM `teacher_location` where `location_id` = '$location_id_converted'
	)
)
";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    }
    return $rows;
  }
  function get_location_id_converted($location_id) {
				if ($location_id == 112) return 124;
		elseif ($location_id == 111) return 126;
		elseif ($location_id == 110) return 123;
		elseif ($location_id == 109) return 125;
		elseif ($location_id == 108) return 122;
		elseif ($location_id == 107) return 121;
		elseif ($location_id == 106) return 120;
		elseif ($location_id == 105) return 119;
		elseif ($location_id == 104) return 118;
		elseif ($location_id == 103) return 117;
		elseif ($location_id == 0) return 0;
		else return 100;
  }
  function get_cohort_id_by_location($location, $student_id, $date) {
    $connection = connect_database();
    if (!$student_id) return 0;
    $query = "
SELECT 
	cohort_id 
FROM 
	course_registration,
	course_location
WHERE 
	course_location.course_location_id = course_registration.course_location_id
and
	course_registration.location = '$location' 
and
	course_location.course_start_date = '$date'
and 
	course_registration.student_id = '$student_id' 
order by 
	course_registration_id asc
limit 
	0,1
";
    $result = mysqli_query($connection,$query);
    $row = mysqli_fetch_assoc($result);
    return $row["cohort_id"];
  }
	function get_courses_by_location_for_adminhome($location_id, $order_by = "course_name") {
		$courses = array();		
		$connection = connect_database();
		$query = "
SELECT 
	course_location.course_id, 
	course_registration.course_location_id, 
	course_name, 
	course_description, 
	course_type, 
	max_enrollment, 
	course_location.active, 
	course_cost, 
	course_duration, 
	camp_id, 
	course_start_date, 
	course_end_date, 
	start_grade, 
	end_grade 
FROM 
	course, 
	course_location,
	course_registration
WHERE 
	course_registration.location = '$location_id' 
AND 
	course.course_id = course_location.course_id 
AND 
	course_location.course_location_id =  course_registration.course_location_id
ORDER BY 
	course_start_date
";
		$result = mysqli_query($connection,$query);
        if (!$result) {
            die(mysqli_error($connection));
        }
        while ($row = mysqli_fetch_assoc($result)) {
			$courses[] = $row;
		}
		return $courses;
	}
  function get_location_options_by_date($date = '') {
    $connection = connect_database();
		$query = "SELECT 
			cohort_id,
			camper_image,
			emerg_name ,
			emerg_phone ,
			allergies,
			course_end_date,
			course_registration.teacher_id, date_of_birth,gender,pronoun,t_shirt_size,friend_to_be_grouped_with, course.course_name, student_info.student_id, student_first_name, student_last_name, course_registration.course_location_id, course_start_date, school_name, location, location.location_id FROM course, student_info, course_registration, course_location, location WHERE course_location.course_location_id = course_registration.course_location_id and location.location_id = course_location.location_id and course_start_date = '$date' and student_info.student_id = course_registration.student_id and course.course_id = course_location.course_id and course_registration.location <> '0' order by course_name asc";
    $result = mysqli_query($connection,$query);
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    return $rows;
  }
	function get_student_week_location($student_id, $course_location_id, $course_start_date = "") {
		$week = 0;
		$connection = connect_database();
		$query = "SELECT camp_id FROM course_location WHERE course_location_id = '$course_location_id'";
		$result = mysqli_query($connection,$query);
		$row = mysqli_fetch_assoc($result);
		$camp_id = $row['camp_id'];
      if (!empty($course_start_date))
		$query = "SELECT DISTINCT(course_start_date) FROM course_registration, course_location WHERE student_id = '$student_id' AND course_location.camp_id = '$camp_id' AND course_registration.course_location_id = course_location.course_location_id AND course_start_date <= '$course_start_date'";
      else
		$query = "SELECT DISTINCT(course_start_date) FROM course_registration, course_location WHERE student_id = '$student_id' AND course_location.camp_id = '$camp_id' AND course_registration.course_location_id = course_location.course_location_id AND course_start_date <= now()";
		$result = mysqli_query($connection,$query);
		while ($row = mysqli_fetch_assoc($result)) $week++;
		return $week;	
	}
  function mark_tag($account_id) {
    $connection = connect_database();
		$query = "INSERT INTO `tags` (`id`, `account_id`, `engine`, `created`, `updated`) VALUES (NULL, '$account_id', NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
    if (mysqli_query($connection,$query))
      return 1;
    else
      return 0;
  }
  function get_tag($account_id) {
    $connection = connect_database();
		$query = "select created from tags where account_id = '$account_id'";
		$result = mysqli_query($connection,$query);
		$row = mysqli_fetch_assoc($result);
		return $row['created'];
  }
  function get_last_payment_type($account_id) {
    $connection = connect_database();
		$query = "SELECT `status`, `last_four_digits` FROM `report_registrations` where `account_id` = '$account_id' and `status` <> '' order by id desc limit 1";
		$result = mysqli_query($connection,$query);
		$row = mysqli_fetch_assoc($result);
    $return[] = $row['status'];
    $return[] = $row['last_four_digits'];
		return $return;
  }
  function mark_waitlist_sent($post) {
    foreach ($post as $key => $value) 
      if ($value == 'mark_sent') $sql_string .= $key.',';
    $sql_string = substr($sql_string,0,strlen($sql_string)-1);
    $connection = connect_database();
    $sql = "update `wait_list` set emailed = ".time()." where wait_list_id in ($sql_string)";
    if (mysqli_query($connection,$sql))
      return 1;
    else
      return 0;
  }
  function get_student_self_checkout($student_id) {
    $connection = connect_database();
    $sql = "SELECT self_checkout FROM `student_info` WHERE student_id = $student_id";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    return $row["self_checkout"];
  }
  function update_student_self_checkout($student_id, $set) {
    $student_id++;$student_id--;
    $set++;$set--;
    $connection = connect_database();
    $sql = "update `student_info` set self_checkout = $set where student_id = $student_id";
    if (mysqli_query($connection,$sql)) return 1;
  }
	function get_admin_id($admin_email) {
		$connection = connect_database();
		$query = "SELECT `admin_id` FROM `admin_info` where `email` like '$admin_email' order by admin_id asc limit 0,1";
		$result = mysqli_query($connection,$query);
		if (!$result) {
			die(mysqli_error($connection));
		}
		$row = mysqli_fetch_assoc($result);
		return $row["admin_id"];
	}
  function get_location_id($course_location_id) {
    $connection = connect_database();
    $sql = "SELECT location_id FROM `course_location` WHERE course_location_id = $course_location_id";
    $result = mysqli_query($connection,$sql);
    $row = mysqli_fetch_assoc($result);
    return $row["location_id"];
  }
  function get_5th_camp_students() {
	  list($year, $month, $day) = explode("-", date('Y-m-d'));
		$year --;
	  if ($month < 9) {
	    $previous_year = $year-1;
	  } else {
	    $previous_year = $year;
	    $year ++;
	  }
    $connection = connect_database();
	  $query = "
SELECT
	distinct(student_id)
FROM
	course_location,
	course_registration
WHERE
	course_registration.course_location_id = course_location.course_location_id
AND
 	course_start_date like '$year-%-%'
ORDER BY
	course_start_date ASC;
";
		$result = mysqli_query($connection,$query);
		while ($row = mysqli_fetch_assoc($result)) $all_students[] = $row["student_id"];
		foreach($all_students as $student_id) {
			$number_of_camps = $this->get_number_of_camps($student_id);
			if ($number_of_camps == 5)
				$results[$student_id] = $number_of_camps;
		}
		if (is_array($results)) arsort($results);
		return $results;
  }
	 // Returns all students enrolled this summer with their camp count.
  // No filtering by number of camps — let the page decide what to show.
  function get_end_of_line_students() {
      list($year, $month, $day) = explode("-", date('Y-m-d'));
      $year--;
      if ($month < 9) {
          $previous_year = $year - 1;
      } else {
          $previous_year = $year;
          $year++;
      }
      $connection = connect_database();
      $query = "
SELECT
    distinct(student_id)
FROM
    course_location,
    course_registration
WHERE
    course_registration.course_location_id = course_location.course_location_id
AND
    course_start_date like '$year-%-%'
ORDER BY
    course_start_date ASC;
";
      $result = mysqli_query($connection, $query);
      $results = array();
      while ($row = mysqli_fetch_assoc($result)) {
          $student_id = $row["student_id"];
          $number_of_camps = $this->get_number_of_camps($student_id);
          $results[$student_id] = $number_of_camps;
      }
      if (is_array($results)) arsort($results);
      return $results;
  }





  function get_number_of_camps($student_id) {
    $connection = connect_database();
		$query = "
SELECT
	course_start_date
FROM
	course_location,
	course_registration
WHERE
	student_id = '$student_id'
AND
	course_registration.course_location_id = course_location.course_location_id
ORDER BY
	course_start_date ASC
";
		$result = mysqli_query($connection,$query);
		while ($row = mysqli_fetch_assoc($result)) $all_course_start_dates[] = $row["course_start_date"];
		foreach ($all_course_start_dates as $course_start_date) {
			$course_start_date_year = substr($course_start_date,0,4);
			$results[$course_start_date_year] = 1;
		}
		return count($results);
  }
  function get_current_camp_location($student_id) {
	  list($year, $month, $day) = explode("-", date('Y-m-d'));
	  if ($month < 9) {
	    $previous_year = $year-1;
	  } else {
	    $previous_year = $year;
	    $year ++;
	  }
    $connection = connect_database();
	  $query = "
SELECT
	school_name
FROM
  location,
	course_location,
	course_registration
WHERE
	location.location_id = course_location.location_id
AND
	course_registration.course_location_id = course_location.course_location_id
AND
 	course_start_date like '$year-%-%'
and
	student_id = '$student_id'
ORDER BY
	course_start_date desc
limit 0,1;
";
		$result = mysqli_query($connection,$query);
		$row = mysqli_fetch_assoc($result);
		return $row["school_name"];
  }
  function get_first_course_enrollment($student_id) {
    $connection = connect_database();
	  $query = "
SELECT
	course_registration.course_location_id,
	course.course_name,
	course_start_date
FROM
	course,
	location,
	course_location,
	course_registration
WHERE
	location.location_id = course_location.location_id
AND
	course_registration.course_location_id = course_location.course_location_id
AND
	student_id = '$student_id'
AND
	course.course_id = course_location.course_id
ORDER BY
	course_start_date ASC
limit
	0,1
";
		$result = mysqli_query($connection,$query);
		$row = mysqli_fetch_assoc($result);
		return array($row["course_location_id"], $row["course_name"], $row["course_start_date"]);
  }
  function get_last_week_of_attendance_this_summer($student_id) {
    $connection = connect_database();
	  $query = "
SELECT
	course_start_date,
	course_registration.course_location_id,
	course.course_name
FROM
	course,
	location,
	course_location,
	course_registration
WHERE
	location.location_id = course_location.location_id
AND
	course_registration.course_location_id = course_location.course_location_id
AND
	student_id = '$student_id'
AND
	course.course_id = course_location.course_id
ORDER BY
	course_start_date DESC
limit
	0,1
";
		$result = mysqli_query($connection,$query);
		$row = mysqli_fetch_assoc($result);
		return array($row["course_location_id"], $row["course_start_date"].": ".$row["course_name"]);
  }
	function get_admin_stem_tech($admin_id) {
		$connection = connect_database();
		$query = "SELECT `stem_tech_access` FROM `admin_info` where `admin_id` = $admin_id";
		$result = mysqli_query($connection,$query);
		$row = mysqli_fetch_assoc($result);
		return $row["stem_tech_access"];
	}
	function get_student_emails($student_id) {
		$connection = connect_database();
		$query = "SELECT account_info.email, account_info.secondary_email FROM account_info, student_info where student_info.student_id = $student_id
		and student_info.account_id = account_info.account_id";
		$result = mysqli_query($connection,$query);
		$row = mysqli_fetch_assoc($result);
		return array($row["email"],$row["secondary_email"]);
	}
	function get_admin_slideshow_url($course_location_id) {
		$connection = connect_database();
		$query = "SELECT `slideshow_url` FROM `slideshows` where `clid` = $course_location_id order by id desc limit 0,1";
		$result = mysqli_query($connection,$query);
		$row = mysqli_fetch_assoc($result);
		if ($row)
			return $row["slideshow_url"];
		else
			return 0;
	}
	function get_admin_slideshow_present($course_location_id) {
		$connection = connect_database();
		$query = "SELECT `slideshow_present` FROM `slideshows` where `clid` = $course_location_id order by id desc limit 0,1";
		$result = mysqli_query($connection,$query);
		$row = mysqli_fetch_assoc($result);
		if ($row)
			return $row["slideshow_present"];
		else
			return 0;
	}
		function get_extended_care($location_id) {
		$connection = connect_database();
		$query = "SELECT `extended_care` FROM `location` where `location_id` = $location_id";
		$result = mysqli_query($connection,$query);
		$row = mysqli_fetch_assoc($result);
		if ($row)
			return $row["extended_care"];
		else
			return 2;
		}
}
?>
