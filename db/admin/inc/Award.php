<?php
class Award {
	var $duplicate_award = null;
	var $validation_error = '';

	function give_award($teacher_id, $p) {
		$connection = connect_database();
		$student_id = $p['student_id'];
		$course_location_id = $p['course_location_id'];
		$clean_title = $this->clean_award_text($p['award_title']);
		$clean_reason = $this->clean_award_text($p['reason']);
		if (trim($clean_title) == '' || trim($clean_reason) == '') {
			$this->validation_error = 'Please enter both an award title and a reason before saving.';
			return false;
		}
		$duplicate = $this->find_duplicate_award_for_week($student_id, $course_location_id);
		if ($duplicate) {
			$this->duplicate_award = $duplicate;
			return false;
		}
		$award_title = mysqli_real_escape_string($connection, $clean_title);
		$reason = mysqli_real_escape_string($connection, $clean_reason);
		$camper_of_the_week = $p['camper_of_the_week'];
		$best_in_class = $p['best_in_class'];
		if (!$camper_of_the_week) $camper_of_the_week = 'n';
		if (!$best_in_class) $best_in_class = 'n';
		$query = sprintf("INSERT INTO award (student_id, course_location_id, award_title, reason, camper_of_the_week, best_in_class, teacher_id) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s')", $student_id, $course_location_id, $award_title, $reason, $camper_of_the_week, $best_in_class, $teacher_id);
		$result = mysqli_query($connection, $query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		return 1;
	}

	function get_duplicate_award() {
		return $this->duplicate_award;
	}

	function get_validation_error() {
		return $this->validation_error;
	}

	function find_duplicate_award_for_week($student_id, $course_location_id, $exclude_award_id = 0) {
		$connection = connect_database();
		$student_id = intval($student_id);
		$course_location_id = intval($course_location_id);
		$exclude_award_id = intval($exclude_award_id);
		if (!$student_id || !$course_location_id) return null;

		$query = "
			SELECT
				a.award_id,
				a.award_title,
				a.reason,
				a.course_location_id,
				c.course_name,
				cl.course_start_date,
				cl.course_end_date,
				t.first_name,
				t.last_name
			FROM award a
			INNER JOIN course_location cl ON a.course_location_id = cl.course_location_id
			INNER JOIN course_location target_cl ON target_cl.course_location_id = '$course_location_id'
			INNER JOIN course c ON cl.course_id = c.course_id
			LEFT JOIN teacher t ON a.teacher_id = t.teacher_id
			WHERE a.student_id = '$student_id'
			  AND cl.location_id = target_cl.location_id
			  AND cl.course_start_date = target_cl.course_start_date
		";
		if ($exclude_award_id) {
			$query .= " AND a.award_id != '$exclude_award_id'";
		}
		$query .= " ORDER BY a.award_id ASC LIMIT 1";

		$result = mysqli_query($connection, $query);
		if (!$result) {
			die(mysqli_error($connection));
		}
		$row = mysqli_fetch_assoc($result);
		if (!$row) return null;
		$row['award_title'] = $this->to_html($row['award_title']);
		$row['reason'] = $this->to_html($row['reason']);
		return $row;
	}

	function get_award($award_id) {
		$connection = connect_database();
		$query = sprintf("SELECT student_first_name, student_last_name, first_name, last_name, nickname, award_id, award.student_id, course_location_id, award_title, reason, camper_of_the_week, best_in_class, award.teacher_id FROM award INNER JOIN student_info ON award.student_id = student_info.student_id LEFT JOIN teacher ON award.teacher_id = teacher.teacher_id WHERE award_id = '%s'", $award_id);
		$result = mysqli_query($connection, $query);
		if (!$result) {
            die(mysqli_error($connection));
        }

		$row = mysqli_fetch_assoc($result);
		$row['student_first_name'] = ucfirst($row['student_first_name'])
;
		$row['award_title'] = $this->to_html($row['award_title']);
		$row['reason'] = $this->to_html($row['reason']);

		return $row;
	}

	function get_award_by_student_id($student_id) {
        $connection = connect_database();
        $query = sprintf("SELECT student_first_name, student_last_name, first_name, last_name, nickname, award_id, award.student_id, course_location_id, award_title, reason, camper_of_the_week, best_in_class, award.teacher_id FROM award INNER JOIN student_info ON award.student_id = student_info.student_id LEFT JOIN teacher ON award.teacher_id = teacher.teacher_id WHERE award.student_id = '%s'", $student_id);
        $result = mysqli_query($connection, $query);
        if (!$result) {
            die(mysqli_error($connection));
        }

        $row = mysqli_fetch_assoc($result);
		$row['student_first_name'] = ucfirst($row['student_first_name'])
;
		$row['award_title'] = $this->to_html($row['award_title']);
		$row['reason'] = $this->to_html($row['reason']);

        return $row;
    }

	function update_award($award_id, $p) {
		$connection = connect_database();
		$student_id = $p['student_id'];
		$course_location_id = $p['course_location_id'];
		$award_title = mysqli_real_escape_string($connection, $this->clean_award_text($p['award_title']));
		$reason = mysqli_real_escape_string($connection, $this->clean_award_text($p['reason']));
		$camper_of_the_week = $p['camper_of_the_week'];
		$best_in_class = $p['best_in_class'];
		if (!$camper_of_the_week) $camper_of_the_week = 'n';
		if (!$best_in_class) $best_in_class = 'n';
		$query = sprintf("UPDATE award SET award_title = '%s', reason = '%s', camper_of_the_week = '%s', best_in_class = '%s' WHERE award_id = '%s'", $award_title, $reason, $camper_of_the_week, $best_in_class, $award_id);
		$result = mysqli_query($connection, $query);
        if (!$result) {
            die(mysqli_error($connection));
        }
		return 1;
	}

	function delete_award($award_id) {
		$connection = connect_database();
		$query = sprintf("DELETE FROM award WHERE award_id = '%s'", $award_id);	
		$result = mysqli_query($connection, $query);
        if (!$result) {
            die(mysqli_error($connection));
        }

		return 1;
	}

	function to_html($text) {
		$text = $this->clean_award_text($text);
		$text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
		return $text;
	}

	function clean_award_text($text) {
		$text = stripslashes((string)$text);
		return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	static function course_teachers($course_location_id, $fallback = array()) {
		$connection = connect_database();
		$course_location_id = intval($course_location_id);
		$teachers = array();
		if ($course_location_id) {
			$query = "
				SELECT teacher.teacher_id, teacher.first_name, teacher.last_name, teacher.nickname
				FROM teacher_course_location
				INNER JOIN teacher ON teacher.teacher_id = teacher_course_location.teacher_id
				WHERE teacher_course_location.course_location_id = '$course_location_id'
				ORDER BY teacher_course_location.teacher_course_location_id ASC, teacher.nickname ASC, teacher.last_name ASC
			";
			$result = mysqli_query($connection, $query);
			while ($result && ($row = mysqli_fetch_assoc($result))) {
				$teachers[] = $row;
			}
		}
		if (!count($teachers) && is_array($fallback) && !empty($fallback['first_name'])) {
			$teachers[] = $fallback;
		}
		return $teachers;
	}

	static function teacher_full_name($teacher) {
		$nickname = '';
		if (!empty($teacher['nickname'])) $nickname = '"' . $teacher['nickname'] . '"';
		return trim($teacher['first_name'] . ' ' . $nickname . ' ' . $teacher['last_name']);
	}

	static function teacher_sign_name($teacher) {
		$first = isset($teacher['first_name']) ? trim($teacher['first_name']) : '';
		$last = isset($teacher['last_name']) ? trim($teacher['last_name']) : '';
		if ($first != '' && $last != '') return substr($first, 0, 1) . ' ' . $last;
		return trim($first . ' ' . $last);
	}

	static function teacher_list_text($teachers, $style = 'full') {
		$names = array();
		foreach ($teachers as $teacher) {
			$name = ($style == 'sign') ? self::teacher_sign_name($teacher) : self::teacher_full_name($teacher);
			if ($name != '') $names[] = $name;
		}
		if (!count($names)) return 'Instructor';
		if (count($names) == 1) return $names[0];
		$last = array_pop($names);
		return implode(', ', $names) . ' and ' . $last;
	}
}
?>
