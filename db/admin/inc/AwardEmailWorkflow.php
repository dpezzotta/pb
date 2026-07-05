<?php
require_once(dirname(__FILE__) . '/../../inc/CourseNextSteps.php');

class AwardEmailWorkflow
{
    static function ensure_tables($db)
    {
        mysqli_query($db, "CREATE TABLE IF NOT EXISTS award_email_templates (
            id int unsigned NOT NULL AUTO_INCREMENT,
            template_key varchar(80) NOT NULL,
            subject varchar(255) NOT NULL,
            body_html text NOT NULL,
            updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY award_email_template_key (template_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

        $result = mysqli_query($db, "SELECT id FROM award_email_templates WHERE template_key = 'weekly_award' LIMIT 1");
        if (!$result || !mysqli_fetch_assoc($result)) {
            mysqli_query($db, sprintf(
                "INSERT INTO award_email_templates (template_key, subject, body_html) VALUES ('weekly_award', '%s', '%s')",
                mysqli_real_escape_string($db, self::default_subject()),
                mysqli_real_escape_string($db, self::default_body())
            ));
        }
        self::ensure_next_steps_template_block($db);
    }

    static function default_subject()
    {
        return "That's a wrap, {{student_first_name}}! Thank you so much!";
    }

    static function default_body()
    {
        return '<h1 style="text-align:center;color:#222;font-size:22px;">Congratulations!</h1>
<p style="text-align:center;"><img src="https://www.planetbravo.com/img/logo-planetbravo320.png" width="160" height="132" alt="PlanetBravo Logo"></p>
<p>Dear {{student_first_name}} and family!</p>
<p>What a week, what a summer! Thank you for spending such a productive time with us this past week. What a joy it was to create with you!</p>
<p>Attached to this email is the full-size version of the special mini-certificate awarded to {{student_first_name}}. It is also accessible through <a href="{{certificate_url}}">this link</a>.</p>
<p><strong>QUICK FEEDBACK FORM</strong>: <a href="https://docs.google.com/forms/d/e/1FAIpQLSdh2BC5xDB_9qFZvyB0k0V0XNa_MqTTYdHJPWpNS-e46VYzRw/viewform?usp=sf_link">HERE</a><br>If you have a moment, we humbly request feedback so we may reflect on the program. We thank you in advance for letting us know how we did.</p>
' . self::next_steps_email_block() . '
<p><strong>GAME SOFTWARE NOTICE</strong><br>We use a variety of fun and advanced programs at camp, all of which are supervised and moderated behind our school firewalls and content filters. If students are using online tools and games at home such as Super Tux Kart, Minecraft, PaintBall, or any programs that allow communication with the outside world, we highly advise supervising usage of these programs or moving their devices to a common space in the home.</p>
<p>Thank you again for your participation in this program, and we hope to see you again! You may always see future camp offerings <a href="https://www.planetbravo.com/camps/register.php">HERE</a>. If you have any questions at all, please feel free to reply to this email.</p>
<p>Best wishes,<br>The PlanetBravo Team<br>310.443.7607</p>';
    }

    static function next_steps_email_block()
    {
        return '<div style="margin:18px 0 18px;padding:16px;border:1px solid #f7b267;background:#fff7ed;text-align:center;">
<div style="font-size:17px;line-height:23px;font-weight:bold;color:#0f4778;text-transform:uppercase;">NEXT STEPS - Continuing the Work</div>
<p style="margin:10px 0 12px;color:#333;text-align:left;">The projects created at camp were awesome, but what\'s next? The best of the best power users keep playing and practicing what they have learned. On the last day of camp, we uploaded our work to our <a href="http://drive.planetbravo.com">Google Drive</a> account. Camper user IDs are located in parentheses on their nametag from camp, and was also emailed to parents the day before camp started. Their password is their 8-digit birthday in the mmddyyyy format.</p>
<p style="margin:0 0 14px;color:#333;text-align:left;">Read about how to access work from camp and continue building from home with course-specific next steps.</p>
<a href="{{next_steps_url}}" style="display:inline-block;background:#f26522;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 18px;border-radius:6px;">Open Next Steps for {{course_name}}</a>
</div>';
    }

    static function ensure_next_steps_template_block($db)
    {
        $old = '<p><strong>YOUR GOOGLE DRIVE ACCOUNT</strong><br>The projects created at camp were awesome, but what\'s next? The best of the best power users keep playing and practicing what they have learned. On the last day of camp, we uploaded our work to our <a href="http://drive.planetbravo.com">Google Drive</a> account. Camper user IDs are located in parentheses on their nametag from camp, and was also emailed to parents the day before camp started. Their password is their 8-digit birthday in the mmddyyyy format. <strong>Read about how to access work and take the next steps at <a href="{{next_steps_url}}">Next Steps After Camp</a>.</strong></p>';
        $new = self::next_steps_email_block();
        mysqli_query($db, sprintf(
            "UPDATE award_email_templates
             SET body_html = REPLACE(body_html, '%s', '%s')
             WHERE template_key = 'weekly_award'
               AND body_html LIKE '%%Next Steps After Camp%%'
               AND body_html NOT LIKE '%%NEXT STEPS - Continuing the Work%%'",
            mysqli_real_escape_string($db, $old),
            mysqli_real_escape_string($db, $new)
        ));
    }

    static function template($db)
    {
        self::ensure_tables($db);
        $result = mysqli_query($db, "SELECT * FROM award_email_templates WHERE template_key = 'weekly_award' LIMIT 1");
        $template = $result ? mysqli_fetch_assoc($result) : array('subject' => self::default_subject(), 'body_html' => self::default_body());
        $normalized = self::normalize_saved_template_body($template['body_html']);
        if ($normalized != $template['body_html']) {
            mysqli_query($db, sprintf(
                "UPDATE award_email_templates SET body_html = '%s' WHERE template_key = 'weekly_award'",
                mysqli_real_escape_string($db, $normalized)
            ));
            $template['body_html'] = $normalized;
        }
        return $template;
    }

    static function update_template($db, $subject, $body_html)
    {
        self::ensure_tables($db);
        $body_html = self::normalize_saved_template_body($body_html);
        mysqli_query($db, sprintf(
            "UPDATE award_email_templates SET subject = '%s', body_html = '%s' WHERE template_key = 'weekly_award'",
            mysqli_real_escape_string($db, $subject),
            mysqli_real_escape_string($db, $body_html)
        ));
    }

    static function awards_for_location($db, $location_id, $date, $admin_stem_tech = 0)
    {
        $sql = "SELECT award.award_id, award.student_id, award.course_location_id, award.award_title, award.reason,
                       award.camper_of_the_week, award.best_in_class, award.file_pdf_path, award.email_sent,
                       course.course_name, course_location.course_start_date, course_location.course_end_date,
                       teacher.first_name AS teacher_first_name, teacher.last_name AS teacher_last_name, teacher.nickname,
                       student_info.student_first_name, student_info.student_last_name, student_info.date_of_birth,
                       account_info.email, account_info.secondary_email, account_info.registrar_first_name
                FROM award
                INNER JOIN course_location ON award.course_location_id = course_location.course_location_id
                INNER JOIN course ON course.course_id = course_location.course_id
                LEFT JOIN teacher ON teacher.teacher_id = award.teacher_id
                INNER JOIN student_info ON student_info.student_id = award.student_id
                INNER JOIN account_info ON account_info.account_id = student_info.account_id
                WHERE course_location.location_id = " . intval($location_id) . "
                  AND course_location.course_start_date = '" . mysqli_real_escape_string($db, $date) . "'
                ORDER BY teacher.nickname ASC, course.course_name ASC, student_info.student_last_name ASC, student_info.student_first_name ASC";
        $result = mysqli_query($db, $sql);
        $rows = array();
        while ($result && ($row = mysqli_fetch_assoc($result))) {
            $stem_tech = substr($row['course_name'], 0, 4);
            if ($admin_stem_tech == 1 && $stem_tech == 'TECH') continue;
            if ($admin_stem_tech == 2 && $stem_tech == 'STEM') continue;
            $teachers = Award::course_teachers($row['course_location_id'], array(
                'first_name' => $row['teacher_first_name'],
                'last_name' => $row['teacher_last_name'],
                'nickname' => $row['nickname']
            ));
            $row['teacher_names_display'] = Award::teacher_list_text($teachers, 'full');
            $rows[] = $row;
        }
        return $rows;
    }

    static function award($db, $award_id)
    {
        $result = mysqli_query($db, "SELECT award.*, course.course_name, course_location.course_start_date, course_location.course_end_date, course_location.location_id,
            teacher.first_name AS teacher_first_name, teacher.last_name AS teacher_last_name, teacher.nickname,
            student_info.student_first_name, student_info.student_last_name, student_info.account_id,
            account_info.email, account_info.secondary_email, account_info.registrar_first_name
            FROM award
            INNER JOIN course_location ON award.course_location_id = course_location.course_location_id
            INNER JOIN course ON course.course_id = course_location.course_id
            LEFT JOIN teacher ON teacher.teacher_id = award.teacher_id
            INNER JOIN student_info ON student_info.student_id = award.student_id
            INNER JOIN account_info ON account_info.account_id = student_info.account_id
            WHERE award.award_id = " . intval($award_id) . " LIMIT 1");
        $award = $result ? mysqli_fetch_assoc($result) : null;
        if ($award) {
            $teachers = Award::course_teachers($award['course_location_id'], array(
                'first_name' => $award['teacher_first_name'],
                'last_name' => $award['teacher_last_name'],
                'nickname' => $award['nickname']
            ));
            $award['teacher_names_display'] = Award::teacher_list_text($teachers, 'full');
            $award['teacher_sign_display'] = Award::teacher_list_text($teachers, 'sign');
        }
        return $award;
    }

    static function update_award_text($db, $award_id, $title, $reason)
    {
        mysqli_query($db, sprintf(
            "UPDATE award SET award_title = '%s', reason = '%s', file_pdf_path = NULL WHERE award_id = %d",
            mysqli_real_escape_string($db, self::clean_award_text($title)),
            mysqli_real_escape_string($db, self::clean_award_text($reason)),
            intval($award_id)
        ));
    }

    static function generate_pdf($db, $award_id)
    {
        $award = self::award($db, $award_id);
        if (!$award) return false;
        if ($award['file_pdf_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $award['file_pdf_path'])) {
            return $award['file_pdf_path'];
        }

        require_once(dirname(__FILE__) . '/../fpdf185/fpdf.php');
        $pdf = new FPDF('L');
        $width = 297;
        $height = 210;
        $pdf->AddPage();
        $pdf->Image(dirname(__FILE__) . '/../certificate.png', 0, 0, $width, $height);

        $award_title = html_entity_decode($award['award_title']);
        $reason = html_entity_decode($award['reason']);
        $reason_array = self::wrap_reason($reason);
        $teacher_full_name = !empty($award['teacher_names_display']) ? $award['teacher_names_display'] : 'Instructor';
        $teacher_sign_name = !empty($award['teacher_sign_display']) ? $award['teacher_sign_display'] : $teacher_full_name;
        $student_full_name = html_entity_decode($award['student_first_name'] . ' ' . $award['student_last_name']);

        $pdf->SetY(90);
        $pdf->SetFont('Arial', 'B', 22);
        $pdf->Cell(0, 10, 'The', 0, 1, 'C');
        $pdf->Cell(0, 7, " '" . $award_title . "' ", 0, 1, 'C');
        $pdf->Cell(0, 10, 'Award', 0, 0, 'C');
        $pdf->Ln(14);
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell(0, 10, 'Presented to:', 0, 0, 'C');
        $pdf->Ln(8);
        $pdf->SetFont('Arial', 'B', 28);
        $pdf->Cell(0, 10, $student_full_name, 0, 0, 'C');
        $pdf->Ln(14);
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell(0, 10, 'For:', 0, 0, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        foreach ($reason_array as $i => $reason_line) {
            $pdf->Cell(0, 0, $reason_line, 0, 0, 'C');
            if ($i < count($reason_array) - 1) $pdf->Ln(6);
        }
        $pdf->Ln(count($reason_array) > 1 ? 3 : 8);
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell(0, 16, 'Issued at:', 0, 0, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 6, 'PlanetBravo', 0, 0, 'C');
        $pdf->AddFont('BulgattiRegular', '', 'bulgatti_xgmv.php');
        $pdf->SetFont('BulgattiRegular', '', strlen($teacher_sign_name) > 28 ? 18 : 24);
        $pdf->SetY(-38);
        $pdf->SetX(-162);
        $pdf->Cell(120, 0, $teacher_sign_name, 0, 0, 'R');
        $pdf->SetFont('Arial', '', strlen($teacher_full_name) > 34 ? 9 : 12);
        $pdf->SetY(-26);
        $pdf->SetX(-142);
        $pdf->Cell(120, 0, $teacher_full_name, 0, 0, 'R');
        $pdf->SetY(-21);
        $pdf->SetX(-142);
        $pdf->Cell(120, 0, 'Instructor', 0, 0, 'R');
        $pdf->SetY(-35);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 14, date('F j, Y', strtotime($award['course_end_date'])), 0, 0, 'C');

        $year = date('Y', strtotime($award['course_end_date']));
        $dir = $_SERVER['DOCUMENT_ROOT'] . '/awards/' . $year;
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $school_name = self::safe_file_part(self::location_name($db, $award['location_id']));
        $file_date = date('d_F_Y', strtotime($award['course_end_date']));
        $file_name = '/awards/' . $year . '/Award_' . intval($award_id) . '_' . $school_name . '_' . $file_date . '.pdf';
        $pdf->Output('F', $_SERVER['DOCUMENT_ROOT'] . $file_name);
        mysqli_query($db, "UPDATE award SET file_pdf_path = '" . mysqli_real_escape_string($db, $file_name) . "' WHERE award_id = " . intval($award_id));
        return $file_name;
    }

    static function send_award_email($db, $award_id)
    {
        return self::send_award_email_to($db, $award_id, '', true);
    }

    static function send_award_test_email($db, $award_id, $test_email)
    {
        return self::send_award_email_to($db, $award_id, $test_email, false);
    }

    static function send_award_email_to($db, $award_id, $test_email = '', $mark_sent = true)
    {
        $award = self::award($db, $award_id);
        if (!$award) return array(false, 'Missing award.');
        if ($mark_sent && $award['email_sent']) return array(false, 'Already sent or missing award.');
        $pdf_path = self::generate_pdf($db, $award_id);
        if (!$pdf_path || !file_exists($_SERVER['DOCUMENT_ROOT'] . $pdf_path)) return array(false, 'PDF could not be generated.');

        $template = self::template($db);
        $certificate_url = 'https://www.planetbravo.com' . $pdf_path;
        $subject = self::render_template($template['subject'], $award, $certificate_url);
        $body = self::wrap_email(self::render_template($template['body_html'], $award, $certificate_url));
        $text = self::plain_text($body);
        $is_test = trim($test_email) != '';
        $to = $is_test ? trim($test_email) : trim($award['email']);
        $cc = trim($award['secondary_email']);
        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) return array(false, $is_test ? 'No valid test email.' : 'No valid primary parent email.');

        $vendor_paths = array(
            '/home/pezzotta/vendor/autoload.php',
            dirname(__FILE__) . '/../vendor/autoload.php',
            dirname(__FILE__) . '/../../vendor/autoload.php'
        );
        $vendor_loaded = false;
        foreach ($vendor_paths as $vendor) {
            if (file_exists($vendor)) {
                require_once($vendor);
                $vendor_loaded = true;
                break;
            }
        }
        if (!$vendor_loaded) return array(false, 'Mailgun vendor autoload was not found.');
        require_once(dirname(__FILE__) . '/../../inc/MailgunDeliverability.php');
        require_once(dirname(__FILE__) . '/../../inc/MailgunSmtp.php');

        $mail_error = '';
        $mailer = pb_mailgun_smtp_mailer($mail_error);
        if (!$mailer) return array(false, $mail_error ? $mail_error : 'Mailgun SMTP could not be initialized.');
        $email = (new \Symfony\Component\Mime\Email())
            ->from('PlanetBravo Camps <admin@planetbravo.com>')
            ->to($to)
            ->replyTo('PlanetBravo Camps <admin@planetbravo.com>')
            ->subject($subject)
            ->attachFromPath($_SERVER['DOCUMENT_ROOT'] . $pdf_path)
            ->text($text)
            ->html($body);
        if (!$is_test && $cc && filter_var($cc, FILTER_VALIDATE_EMAIL) && strtolower($cc) != strtolower($to)) $email->cc($cc);
        pb_mailgun_apply_deliverability_headers($email, $is_test ? 'award-email-test' : 'award-email');
        $mailer->send($email);
        if ($mark_sent) {
            mysqli_query($db, "UPDATE award SET email_sent = '1' WHERE award_id = " . intval($award_id));
        }
        return array(true, ($is_test ? 'Test sent to ' : 'Sent to ') . $to);
    }

    static function render_template($text, $award, $certificate_url)
    {
        $tokens = array(
            '{{student_first_name}}' => self::clean_award_text($award['student_first_name']),
            '{{student_name}}' => self::clean_award_text($award['student_first_name'] . ' ' . $award['student_last_name']),
            '{{parent_first_name}}' => self::clean_award_text($award['registrar_first_name']),
            '{{award_title}}' => self::clean_award_text($award['award_title']),
            '{{award_reason}}' => self::clean_award_text($award['reason']),
            '{{course_name}}' => self::clean_award_text($award['course_name']),
            '{{certificate_url}}' => $certificate_url,
            '{{next_steps_url}}' => CourseNextSteps::url_for_award($award['award_id']),
        );
        $text = str_replace(array(
            'https://pb4.us/nextsteps',
            'http://pb4.us/nextsteps',
            'https://www.pb4.us/nextsteps',
            'http://www.pb4.us/nextsteps'
        ), '{{next_steps_url}}', $text);
        $text = self::normalize_next_steps_block($text);
        $text = self::merge_drive_into_next_steps_block($text);
        return str_replace(array_keys($tokens), array_values($tokens), $text);
    }

    static function normalize_saved_template_body($text)
    {
        $text = str_replace(array(
            'https://pb4.us/nextsteps',
            'http://pb4.us/nextsteps',
            'https://www.pb4.us/nextsteps',
            'http://www.pb4.us/nextsteps'
        ), '{{next_steps_url}}', $text);
        $text = self::normalize_next_steps_block($text);
        return self::merge_drive_into_next_steps_block($text);
    }

    static function normalize_next_steps_block($text)
    {
        if (strpos($text, 'NEXT STEPS - Continuing the Work') !== false) return $text;
        if (strpos($text, '{{next_steps_url}}') === false) return $text;

        $block = self::next_steps_email_block();
        $text = preg_replace(
            '#<p>(<strong>YOUR GOOGLE DRIVE ACCOUNT</strong><br>.*?)(?:\s*<strong>\s*Read about how to access work and take the next steps at\s*<a href="\{\{next_steps_url\}\}">Next Steps After Camp</a>\.?\s*</strong>)\s*</p>#is',
            '<p>$1</p>' . $block,
            $text,
            1
        );
        if (strpos($text, 'NEXT STEPS - Continuing the Work') !== false) return $text;

        $text = preg_replace(
            '#<strong>\s*Read about how to access work and take the next steps at\s*<a href="\{\{next_steps_url\}\}">Next Steps After Camp</a>\.?\s*</strong>#is',
            $block,
            $text,
            1
        );
        if (strpos($text, 'NEXT STEPS - Continuing the Work') !== false) return $text;

        return str_replace('{{next_steps_url}}', '{{next_steps_url}}', $text) . $block;
    }

    static function merge_drive_into_next_steps_block($text)
    {
        if (strpos($text, 'NEXT STEPS - Continuing the Work') === false || strpos($text, 'YOUR GOOGLE DRIVE ACCOUNT') === false) {
            return $text;
        }
        return preg_replace(
            '#<p><strong>YOUR GOOGLE DRIVE ACCOUNT</strong><br>.*?</p>\s*#is',
            '',
            $text,
            1
        );
    }

    static function clean_award_text($text)
    {
        return html_entity_decode(stripslashes((string)$text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    static function wrap_email($body)
    {
        return '<!doctype html><html><body style="font-family: Helvetica, Arial, sans-serif; background:#f6f6f6; margin:0; padding:0;"><div style="max-width:600px;margin:0 auto;background:#fff;border:1px solid #e9e9e9;padding:22px;line-height:1.55;font-size:15px;">' . $body . '<div style="margin-top:24px;padding:12px;background:#f3f3f3;text-align:center;font-size:12px;"><a href="https://www.planetbravo.com/terms.php">PlanetBravo Terms</a><br>TAX ID: 16-1745358</div></div></body></html>';
    }

    static function plain_text($html)
    {
        $text = preg_replace('/<br\s*\/?>/i', "\n", (string)$html);
        $text = preg_replace('/<\/(p|div|h[1-6]|li)>/i', "\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }

    static function wrap_reason($reason)
    {
        if (strlen($reason) >= 120 && preg_match('/^(.{1,60})\s(.{0,60})\s(.{0,60})/', $reason, $match)) return array($match[1], $match[2], $match[3]);
        if (strlen($reason) > 60 && preg_match('/^(.{1,60})\s(.{0,60})/', $reason, $match)) return array($match[1], $match[2]);
        return array($reason);
    }

    static function location_name($db, $location_id)
    {
        $result = mysqli_query($db, "SELECT school_name FROM location WHERE location_id = " . intval($location_id) . " LIMIT 1");
        $row = $result ? mysqli_fetch_assoc($result) : null;
        return $row ? $row['school_name'] : 'PlanetBravo';
    }

    static function safe_file_part($text)
    {
        $text = preg_replace('/[^a-zA-Z0-9]+/', '_', $text);
        $text = trim($text, '_');
        return $text ? $text : 'PlanetBravo';
    }
}

?>
