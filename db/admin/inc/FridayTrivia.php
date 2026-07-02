<?php

class FridayTrivia
{
    static function ensure_tables($db)
    {
        mysqli_query($db, "CREATE TABLE IF NOT EXISTS friday_trivia_games (
            game_id int unsigned NOT NULL AUTO_INCREMENT,
            location_id int unsigned NOT NULL,
            week_start date NOT NULL,
            title varchar(160) NOT NULL DEFAULT 'Friday Trivia',
            active tinyint(1) NOT NULL DEFAULT 1,
            started_at datetime DEFAULT NULL,
            started_by int unsigned NOT NULL DEFAULT 0,
            created_by int unsigned NOT NULL DEFAULT 0,
            created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (game_id),
            UNIQUE KEY friday_trivia_location_week (location_id, week_start)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        self::ensure_column($db, 'friday_trivia_games', 'started_at', "datetime DEFAULT NULL");
        self::ensure_column($db, 'friday_trivia_games', 'started_by', "int unsigned NOT NULL DEFAULT 0");

        mysqli_query($db, "CREATE TABLE IF NOT EXISTS friday_trivia_questions (
            question_id int unsigned NOT NULL AUTO_INCREMENT,
            game_id int unsigned NOT NULL,
            sort_order int unsigned NOT NULL DEFAULT 0,
            question text NOT NULL,
            choice_a varchar(255) NOT NULL,
            choice_b varchar(255) NOT NULL,
            choice_c varchar(255) NOT NULL,
            choice_d varchar(255) NOT NULL,
            correct_choice char(1) NOT NULL DEFAULT 'a',
            PRIMARY KEY (question_id),
            KEY friday_trivia_question_game (game_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        mysqli_query($db, "CREATE TABLE IF NOT EXISTS friday_trivia_attempts (
            attempt_id int unsigned NOT NULL AUTO_INCREMENT,
            game_id int unsigned NOT NULL,
            student_id int unsigned NOT NULL,
            course_location_id int unsigned NOT NULL DEFAULT 0,
            score int unsigned NOT NULL DEFAULT 0,
            total int unsigned NOT NULL DEFAULT 0,
            points_awarded int unsigned NOT NULL DEFAULT 0,
            submitted_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (attempt_id),
            UNIQUE KEY friday_trivia_student_game (game_id, student_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        mysqli_query($db, "CREATE TABLE IF NOT EXISTS friday_trivia_answers (
            answer_id int unsigned NOT NULL AUTO_INCREMENT,
            attempt_id int unsigned NOT NULL,
            question_id int unsigned NOT NULL,
            selected_choice char(1) NOT NULL,
            is_correct tinyint(1) NOT NULL DEFAULT 0,
            elapsed_seconds int unsigned NOT NULL DEFAULT 0,
            points_awarded int unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY (answer_id),
            KEY friday_trivia_answer_attempt (attempt_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        self::ensure_column($db, 'friday_trivia_answers', 'elapsed_seconds', "int unsigned NOT NULL DEFAULT 0");
        self::ensure_column($db, 'friday_trivia_answers', 'points_awarded', "int unsigned NOT NULL DEFAULT 0");

        mysqli_query($db, "CREATE TABLE IF NOT EXISTS friday_trivia_progress (
            progress_id int unsigned NOT NULL AUTO_INCREMENT,
            game_id int unsigned NOT NULL,
            student_id int unsigned NOT NULL,
            course_location_id int unsigned NOT NULL DEFAULT 0,
            current_index int unsigned NOT NULL DEFAULT 0,
            total_questions int unsigned NOT NULL DEFAULT 0,
            run_json mediumtext NOT NULL,
            started_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (progress_id),
            UNIQUE KEY friday_trivia_progress_student_game (game_id, student_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    static function ensure_column($db, $table, $column, $definition)
    {
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        if ($table === '' || $column === '') return;
        $result = mysqli_query($db, "SHOW COLUMNS FROM `" . $table . "` LIKE '" . mysqli_real_escape_string($db, $column) . "'");
        if ($result && mysqli_num_rows($result)) return;
        mysqli_query($db, "ALTER TABLE `" . $table . "` ADD COLUMN `" . $column . "` " . $definition);
    }

    static function h($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    static function allowed_locations($admin_id)
    {
        require_once(dirname(__FILE__) . '/SuperAdmin.php');
        $super = new SuperAdmin();
        return $super->get_admin_locations((int)$admin_id);
    }

    static function week_options($db, $location_ids)
    {
        $ids = array_map('intval', (array)$location_ids);
        $ids = array_filter($ids);
        if (!count($ids)) return array();
        $sql = "SELECT DISTINCT course_start_date
                FROM course_location
                WHERE active = 'y'
                  AND location_id IN (" . implode(',', $ids) . ")
                  AND YEAR(course_start_date) = " . intval(CURRENT_YEAR) . "
                ORDER BY course_start_date ASC";
        $result = mysqli_query($db, $sql);
        $weeks = array();
        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $weeks[] = $row['course_start_date'];
        }
        return $weeks;
    }

    static function default_week($weeks)
    {
        $weeks = array_values((array)$weeks);
        if (!count($weeks)) return '';
        $today = date('Y-m-d');
        foreach ($weeks as $week) {
            $week_start = date('Y-m-d', strtotime($week));
            $week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));
            if ($today >= $week_start && $today <= $week_end) return $week;
        }
        foreach ($weeks as $week) {
            if (date('Y-m-d', strtotime($week)) >= $today) return $week;
        }
        return $weeks[count($weeks) - 1];
    }

    static function save_game_from_csv($db, $admin_id, $location_id, $week_start, $title, $tmp_file)
    {
        $location_id = (int)$location_id;
        $week_start = date('Y-m-d', strtotime($week_start));
        $title = trim($title) ? trim($title) : 'Friday Trivia';
        if (!$location_id || !$week_start || !is_uploaded_file($tmp_file)) {
            return array(false, 'Please choose a location, week, and CSV file.');
        }

        $rows = array();
        $handle = fopen($tmp_file, 'r');
        if (!$handle) return array(false, 'Could not read the CSV file.');
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 5) continue;
            $data = array_map('trim', $data);
            if (strtolower($data[0]) == 'question') continue;
            if ($data[0] === '' || $data[1] === '' || $data[2] === '' || $data[3] === '' || $data[4] === '') continue;
            $rows[] = array_slice($data, 0, 5);
            if (count($rows) >= 25) break;
        }
        fclose($handle);
        if (!count($rows)) return array(false, 'No valid trivia rows were found. Use: Question, Correct Answer, Wrong Answer, Wrong Answer, Wrong Answer.');

        mysqli_query($db, sprintf(
            "INSERT INTO friday_trivia_games (location_id, week_start, title, active, created_by)
             VALUES (%d, '%s', '%s', 1, %d)
             ON DUPLICATE KEY UPDATE title = VALUES(title), active = 1, started_at = NULL, started_by = 0, updated_at = CURRENT_TIMESTAMP",
            $location_id,
            mysqli_real_escape_string($db, $week_start),
            mysqli_real_escape_string($db, $title),
            (int)$admin_id
        ));
        $game_id = mysqli_insert_id($db);
        if (!$game_id) {
            $existing = self::game_for_location_week($db, $location_id, $week_start);
            $game_id = $existing ? (int)$existing['game_id'] : 0;
        }
        if (!$game_id) return array(false, 'Could not save the trivia game.');

        mysqli_query($db, "DELETE FROM friday_trivia_questions WHERE game_id = " . (int)$game_id);
        mysqli_query($db, "DELETE friday_trivia_answers FROM friday_trivia_answers INNER JOIN friday_trivia_attempts ON friday_trivia_answers.attempt_id = friday_trivia_attempts.attempt_id WHERE friday_trivia_attempts.game_id = " . (int)$game_id);
        mysqli_query($db, "DELETE FROM friday_trivia_attempts WHERE game_id = " . (int)$game_id);

        $order = 1;
        foreach ($rows as $row) {
            mysqli_query($db, sprintf(
                "INSERT INTO friday_trivia_questions (game_id, sort_order, question, choice_a, choice_b, choice_c, choice_d, correct_choice)
                 VALUES (%d, %d, '%s', '%s', '%s', '%s', '%s', 'a')",
                (int)$game_id,
                $order++,
                mysqli_real_escape_string($db, $row[0]),
                mysqli_real_escape_string($db, $row[1]),
                mysqli_real_escape_string($db, $row[2]),
                mysqli_real_escape_string($db, $row[3]),
                mysqli_real_escape_string($db, $row[4])
            ));
        }
        return array(true, 'Trivia saved with ' . count($rows) . ' questions. Previous attempts for this location/week were cleared.');
    }

    static function game_for_location_week($db, $location_id, $week_start)
    {
        $result = mysqli_query($db, sprintf(
            "SELECT friday_trivia_games.*, location.school_name
             FROM friday_trivia_games
             INNER JOIN location ON location.location_id = friday_trivia_games.location_id
             WHERE friday_trivia_games.location_id = %d
               AND friday_trivia_games.week_start = '%s'
             LIMIT 1",
            (int)$location_id,
            mysqli_real_escape_string($db, date('Y-m-d', strtotime($week_start)))
        ));
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    static function games_for_locations($db, $location_ids)
    {
        $ids = array_map('intval', (array)$location_ids);
        $ids = array_filter($ids);
        if (!count($ids)) return array();
        $result = mysqli_query($db, "SELECT g.*, l.school_name,
            (SELECT COUNT(*) FROM friday_trivia_questions q WHERE q.game_id = g.game_id) AS question_count,
            (SELECT COUNT(*) FROM friday_trivia_attempts a WHERE a.game_id = g.game_id) AS attempt_count
            FROM friday_trivia_games g
            INNER JOIN location l ON l.location_id = g.location_id
            WHERE g.location_id IN (" . implode(',', $ids) . ")
            ORDER BY g.week_start DESC, l.school_name ASC");
        $rows = array();
        while ($result && ($row = mysqli_fetch_assoc($result))) $rows[] = $row;
        return $rows;
    }

    static function game_by_id_for_locations($db, $game_id, $location_ids)
    {
        $ids = array_map('intval', (array)$location_ids);
        $ids = array_filter($ids);
        if (!count($ids)) return null;
        $result = mysqli_query($db, "SELECT g.*, l.school_name
            FROM friday_trivia_games g
            INNER JOIN location l ON l.location_id = g.location_id
            WHERE g.game_id = " . (int)$game_id . "
              AND g.location_id IN (" . implode(',', $ids) . ")
            LIMIT 1");
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    static function set_game_active($db, $game_id, $location_ids, $active)
    {
        $game = self::game_by_id_for_locations($db, $game_id, $location_ids);
        if (!$game) return array(false, 'That trivia game is not available for your account.');
        mysqli_query($db, "UPDATE friday_trivia_games SET active = " . ((int)$active ? 1 : 0) . " WHERE game_id = " . (int)$game_id);
        return array(true, ((int)$active ? 'Trivia game activated.' : 'Trivia game turned off.'));
    }

    static function begin_game($db, $game_id, $location_ids, $admin_id)
    {
        $game = self::game_by_id_for_locations($db, $game_id, $location_ids);
        if (!$game) return array(false, 'That trivia game is not available for your account.');
        mysqli_query($db, sprintf(
            "UPDATE friday_trivia_games SET active = 1, started_at = NOW(), started_by = %d WHERE game_id = %d",
            (int)$admin_id,
            (int)$game_id
        ));
        return array(true, 'Trivia game begun. Camper standby pages will launch automatically.');
    }

    static function reset_game_start($db, $game_id, $location_ids)
    {
        $game = self::game_by_id_for_locations($db, $game_id, $location_ids);
        if (!$game) return array(false, 'That trivia game is not available for your account.');
        mysqli_query($db, "UPDATE friday_trivia_games SET started_at = NULL, started_by = 0 WHERE game_id = " . (int)$game_id);
        return array(true, 'Trivia game moved back to standby.');
    }

    static function student_game_started($db, $student_id)
    {
        $game = self::current_student_game($db, $student_id);
        return array('started' => ($game && !empty($game['started_at'])) ? 1 : 0);
    }

    static function save_progress($db, $game, $student_id, $run)
    {
        $json = json_encode($run);
        if ($json === false) return;
        mysqli_query($db, sprintf(
            "INSERT INTO friday_trivia_progress (game_id, student_id, course_location_id, current_index, total_questions, run_json)
             VALUES (%d, %d, %d, %d, %d, '%s')
             ON DUPLICATE KEY UPDATE course_location_id = VALUES(course_location_id), current_index = VALUES(current_index), total_questions = VALUES(total_questions), run_json = VALUES(run_json), updated_at = CURRENT_TIMESTAMP",
            (int)$game['game_id'],
            (int)$student_id,
            (int)$game['course_location_id'],
            isset($run['current']) ? (int)$run['current'] : 0,
            isset($run['question_ids']) ? count($run['question_ids']) : 0,
            mysqli_real_escape_string($db, $json)
        ));
    }

    static function load_progress($db, $game_id, $student_id)
    {
        $result = mysqli_query($db, sprintf(
            "SELECT * FROM friday_trivia_progress WHERE game_id = %d AND student_id = %d LIMIT 1",
            (int)$game_id,
            (int)$student_id
        ));
        $row = $result ? mysqli_fetch_assoc($result) : null;
        if (!$row || empty($row['run_json'])) return null;
        $run = json_decode($row['run_json'], true);
        return is_array($run) ? $run : null;
    }

    static function delete_progress($db, $game_id, $student_id)
    {
        mysqli_query($db, "DELETE FROM friday_trivia_progress WHERE game_id = " . (int)$game_id . " AND student_id = " . (int)$student_id);
    }

    static function campers_for_game($db, $game_id, $location_ids)
    {
        $game = self::game_by_id_for_locations($db, $game_id, $location_ids);
        if (!$game) return array(null, array());
        $result = mysqli_query($db, sprintf(
            "SELECT DISTINCT si.student_id, si.student_first_name, si.student_last_name, si.login_username,
                    a.attempt_id, a.score, a.total, a.points_awarded, a.submitted_at,
                    p.current_index, p.total_questions, p.updated_at AS progress_updated_at
             FROM course_registration cr
             INNER JOIN course_location cl ON cl.course_location_id = cr.course_location_id
             INNER JOIN student_info si ON si.student_id = cr.student_id
             LEFT JOIN friday_trivia_attempts a ON a.game_id = %d AND a.student_id = si.student_id
             LEFT JOIN friday_trivia_progress p ON p.game_id = %d AND p.student_id = si.student_id
             WHERE cl.location_id = %d
               AND cl.course_start_date = '%s'
               AND cl.active = 'y'
               AND cr.status = 'a'
             ORDER BY si.student_last_name ASC, si.student_first_name ASC",
            (int)$game['game_id'],
            (int)$game['game_id'],
            (int)$game['location_id'],
            mysqli_real_escape_string($db, $game['week_start'])
        ));
        $rows = array();
        while ($result && ($row = mysqli_fetch_assoc($result))) $rows[] = $row;
        return array($game, $rows);
    }

    static function reset_student($db, $game_id, $student_id, $location_ids)
    {
        $game = self::game_by_id_for_locations($db, $game_id, $location_ids);
        if (!$game) return array(false, 'That trivia game is not available for your account.');

        $eligible = mysqli_query($db, sprintf(
            "SELECT cr.student_id
             FROM course_registration cr
             INNER JOIN course_location cl ON cl.course_location_id = cr.course_location_id
             WHERE cr.student_id = %d
               AND cl.location_id = %d
               AND cl.course_start_date = '%s'
               AND cr.status = 'a'
             LIMIT 1",
            (int)$student_id,
            (int)$game['location_id'],
            mysqli_real_escape_string($db, $game['week_start'])
        ));
        if (!$eligible || !mysqli_num_rows($eligible)) return array(false, 'That camper is not in this trivia week.');

        $attempt = self::attempt($db, $game_id, $student_id);
        if ($attempt) {
            mysqli_query($db, "DELETE FROM friday_trivia_answers WHERE attempt_id = " . (int)$attempt['attempt_id']);
            mysqli_query($db, "DELETE FROM friday_trivia_attempts WHERE attempt_id = " . (int)$attempt['attempt_id']);
        }
        self::delete_progress($db, $game_id, $student_id);
        return array(true, 'Trivia reset for that camper. Existing BravoPoints were not changed.');
    }

    static function current_student_game($db, $student_id)
    {
        $today = date('Y-m-d');
        $result = mysqli_query($db, sprintf(
            "SELECT g.*, l.school_name, cr.course_location_id
             FROM course_registration cr
             INNER JOIN course_location cl ON cl.course_location_id = cr.course_location_id
             INNER JOIN friday_trivia_games g ON g.location_id = cl.location_id AND g.week_start = cl.course_start_date
             INNER JOIN location l ON l.location_id = cl.location_id
             WHERE cr.student_id = %d
               AND cr.status = 'a'
               AND cl.active = 'y'
               AND g.active = 1
               AND '%s' BETWEEN cl.course_start_date AND cl.course_end_date
             ORDER BY cl.course_start_date DESC
             LIMIT 1",
            (int)$student_id,
            mysqli_real_escape_string($db, $today)
        ));
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    static function questions($db, $game_id)
    {
        $result = mysqli_query($db, "SELECT * FROM friday_trivia_questions WHERE game_id = " . (int)$game_id . " ORDER BY sort_order ASC, question_id ASC");
        $rows = array();
        while ($result && ($row = mysqli_fetch_assoc($result))) $rows[] = $row;
        shuffle($rows);
        return $rows;
    }

    static function questions_by_ids($db, $game_id, $question_ids)
    {
        $ids = array_map('intval', (array)$question_ids);
        $ids = array_filter($ids);
        if (!count($ids)) return array();
        $result = mysqli_query($db, "SELECT * FROM friday_trivia_questions WHERE game_id = " . (int)$game_id . " AND question_id IN (" . implode(',', $ids) . ")");
        $rows = array();
        while ($result && ($row = mysqli_fetch_assoc($result))) $rows[(int)$row['question_id']] = $row;
        return $rows;
    }

    static function attempt($db, $game_id, $student_id)
    {
        $result = mysqli_query($db, sprintf(
            "SELECT * FROM friday_trivia_attempts WHERE game_id = %d AND student_id = %d LIMIT 1",
            (int)$game_id,
            (int)$student_id
        ));
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    static function answers_for_attempt($db, $attempt_id)
    {
        $result = mysqli_query($db, sprintf(
            "SELECT a.*, q.question, q.choice_a, q.choice_b, q.choice_c, q.choice_d, q.correct_choice
             FROM friday_trivia_answers a
             INNER JOIN friday_trivia_questions q ON q.question_id = a.question_id
             WHERE a.attempt_id = %d
             ORDER BY a.answer_id ASC",
            (int)$attempt_id
        ));
        $rows = array();
        while ($result && ($row = mysqli_fetch_assoc($result))) $rows[] = $row;
        return $rows;
    }

    static function submit($db, $game, $student_id, $answers)
    {
        if (self::attempt($db, $game['game_id'], $student_id)) return array(false, 'You already played this trivia game.');
        $questions = self::questions($db, $game['game_id']);
        $by_id = array();
        foreach ($questions as $question) $by_id[(int)$question['question_id']] = $question;
        $score = 0;
        $total = count($by_id);
        foreach ($by_id as $question_id => $question) {
            $selected = isset($answers[$question_id]) ? strtolower(trim($answers[$question_id])) : '';
            if ($selected == strtolower($question['correct_choice'])) $score++;
        }
        $points = $score * 10;
        mysqli_query($db, sprintf(
            "INSERT INTO friday_trivia_attempts (game_id, student_id, course_location_id, score, total, points_awarded)
             VALUES (%d, %d, %d, %d, %d, %d)",
            (int)$game['game_id'],
            (int)$student_id,
            (int)$game['course_location_id'],
            (int)$score,
            (int)$total,
            (int)$points
        ));
        $attempt_id = mysqli_insert_id($db);
        foreach ($by_id as $question_id => $question) {
            $selected = isset($answers[$question_id]) ? strtolower(trim($answers[$question_id])) : '';
            if (!in_array($selected, array('a', 'b', 'c', 'd'))) $selected = '';
            mysqli_query($db, sprintf(
                "INSERT INTO friday_trivia_answers (attempt_id, question_id, selected_choice, is_correct, elapsed_seconds, points_awarded)
                 VALUES (%d, %d, '%s', %d, 0, %d)",
                (int)$attempt_id,
                (int)$question_id,
                mysqli_real_escape_string($db, $selected),
                $selected == strtolower($question['correct_choice']) ? 1 : 0,
                $selected == strtolower($question['correct_choice']) ? 10 : 0
            ));
        }
        if ($points > 0) {
            require_once(dirname(__FILE__) . '/../../inc/Points.php');
            $points_helper = new Points();
            $points_helper->add(array('student_id' => (int)$student_id, 'points_to_add' => (int)$points));
        }
        return array(true, array('score' => $score, 'total' => $total, 'points' => $points));
    }

    static function submit_timed_attempt($db, $game, $student_id, $run)
    {
        if (self::attempt($db, $game['game_id'], $student_id)) return array(false, 'You already played this trivia game.');
        $question_ids = isset($run['question_ids']) ? $run['question_ids'] : array();
        $question_map = self::questions_by_ids($db, $game['game_id'], $question_ids);
        $answers = isset($run['answers']) ? $run['answers'] : array();
        $score = 0;
        $points = 0;
        $total = count($question_ids);

        foreach ($question_ids as $question_id) {
            $question_id = (int)$question_id;
            if (!isset($question_map[$question_id])) continue;
            $answer = isset($answers[$question_id]) ? $answers[$question_id] : array();
            $selected = isset($answer['selected']) ? strtolower(trim($answer['selected'])) : '';
            $elapsed = isset($answer['elapsed']) ? (int)$answer['elapsed'] : 20;
            $is_correct = $selected && $selected == strtolower($question_map[$question_id]['correct_choice']);
            $question_points = 0;
            if ($is_correct) {
                $score++;
                if ($elapsed <= 5) $question_points = 5;
                elseif ($elapsed <= 10) $question_points = 3;
                elseif ($elapsed <= 15) $question_points = 2;
                else $question_points = 1;
                $points += $question_points;
            }
        }

        $max_points = $total * 5;
        if ($points > $max_points) $points = $max_points;
        mysqli_query($db, sprintf(
            "INSERT INTO friday_trivia_attempts (game_id, student_id, course_location_id, score, total, points_awarded)
             VALUES (%d, %d, %d, %d, %d, %d)",
            (int)$game['game_id'],
            (int)$student_id,
            (int)$game['course_location_id'],
            (int)$score,
            (int)$total,
            (int)$points
        ));
        $attempt_id = mysqli_insert_id($db);
        foreach ($question_ids as $question_id) {
            $question_id = (int)$question_id;
            if (!isset($question_map[$question_id])) continue;
            $answer = isset($answers[$question_id]) ? $answers[$question_id] : array();
            $selected = isset($answer['selected']) ? strtolower(trim($answer['selected'])) : '';
            $elapsed = isset($answer['elapsed']) ? (int)$answer['elapsed'] : 20;
            if ($elapsed < 0) $elapsed = 0;
            if ($elapsed > 20) $elapsed = 20;
            if (!in_array($selected, array('a', 'b', 'c', 'd'))) $selected = '';
            $is_correct = $selected == strtolower($question_map[$question_id]['correct_choice']);
            $question_points = 0;
            if ($is_correct) {
                if ($elapsed <= 5) $question_points = 5;
                elseif ($elapsed <= 10) $question_points = 3;
                elseif ($elapsed <= 15) $question_points = 2;
                else $question_points = 1;
            }
            mysqli_query($db, sprintf(
                "INSERT INTO friday_trivia_answers (attempt_id, question_id, selected_choice, is_correct, elapsed_seconds, points_awarded)
                 VALUES (%d, %d, '%s', %d, %d, %d)",
                (int)$attempt_id,
                (int)$question_id,
                mysqli_real_escape_string($db, $selected),
                $is_correct ? 1 : 0,
                (int)$elapsed,
                (int)$question_points
            ));
        }
        if ($points > 0) {
            require_once(dirname(__FILE__) . '/../../inc/Points.php');
            $points_helper = new Points();
            $points_helper->add(array('student_id' => (int)$student_id, 'points_to_add' => (int)$points));
        }
        return array(true, array('score' => $score, 'total' => $total, 'points' => $points));
    }
}

?>
