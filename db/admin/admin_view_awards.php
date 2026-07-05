<?php
ob_start();
session_start();
require(dirname(__FILE__) . '/admin_config.php');
require_once(dirname(__FILE__) . '/inc/AwardEmailWorkflow.php');

$admin_id = $_SESSION['admin_id'];
if (!$admin_id) {
    header("Location: admin_login.php");
    exit;
}

$location_id = intval($_GET['location_id']);
$date = $_GET['date'];
$admin = new Admin();
$db = connect_database();
AwardEmailWorkflow::ensure_tables($db);
$admin_stem_tech = $admin->get_admin_stem_tech($admin_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $return = "admin_view_awards.php?date=" . urlencode($date) . "&location_id=" . intval($location_id);
    if (isset($_POST['save_award'])) {
        $award_id = intval($_POST['save_award']);
        $title = isset($_POST['award_title'][$award_id]) ? $_POST['award_title'][$award_id] : '';
        $reason = isset($_POST['reason'][$award_id]) ? $_POST['reason'][$award_id] : '';
        AwardEmailWorkflow::update_award_text($db, $award_id, $title, $reason);
        $_SESSION['award_workflow_message'] = 'Award updated. Certificate will regenerate before sending.';
        header("Location: " . $return . "#award-" . $award_id);
        exit;
    }
    if (isset($_POST['save_all'])) {
        $saved = 0;
        if (isset($_POST['award_title']) && is_array($_POST['award_title'])) {
            foreach ($_POST['award_title'] as $award_id => $title) {
                $award_id = intval($award_id);
                $reason = isset($_POST['reason'][$award_id]) ? $_POST['reason'][$award_id] : '';
                $current = AwardEmailWorkflow::award($db, $award_id);
                if (!$current) continue;
                if ($current['award_title'] != $title || $current['reason'] != $reason) {
                    AwardEmailWorkflow::update_award_text($db, $award_id, $title, $reason);
                    $saved++;
                }
            }
        }
        $_SESSION['award_workflow_message'] = $saved . ' changed award' . ($saved == 1 ? '' : 's') . ' saved. Changed certificates will regenerate before sending.';
        header("Location: " . $return);
        exit;
    }
    if (isset($_POST['generate_missing'])) {
        $awards = AwardEmailWorkflow::awards_for_location($db, $location_id, $date, $admin_stem_tech);
        $generated = 0;
        foreach ($awards as $award) {
            if (!$award['email_sent'] && !$award['file_pdf_path']) {
                if (AwardEmailWorkflow::generate_pdf($db, $award['award_id'])) $generated++;
            }
        }
        $_SESSION['award_workflow_message'] = $generated . ' certificate PDF' . ($generated == 1 ? '' : 's') . ' generated.';
        header("Location: " . $return);
        exit;
    }
    if (isset($_POST['send_test'])) {
        $test_email = isset($_POST['test_email']) ? trim($_POST['test_email']) : '';
        $test_award_id = isset($_POST['test_award_id']) ? intval($_POST['test_award_id']) : 0;
        $allowed_awards = AwardEmailWorkflow::awards_for_location($db, $location_id, $date, $admin_stem_tech);
        $allowed = false;
        if (!$test_award_id && count($allowed_awards)) {
            $test_award_id = intval($allowed_awards[0]['award_id']);
        }
        foreach ($allowed_awards as $allowed_award) {
            if (intval($allowed_award['award_id']) == $test_award_id) {
                $allowed = true;
                break;
            }
        }
        if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['award_workflow_error'] = 'Please enter a valid test email address.';
        } elseif (!$allowed) {
            $_SESSION['award_workflow_error'] = 'Please choose a valid award from this location/date.';
        } else {
            list($ok, $message) = AwardEmailWorkflow::send_award_test_email($db, $test_award_id, $test_email);
            if ($ok) $_SESSION['award_workflow_message'] = $message . '. This did not mark the award as sent.';
            else $_SESSION['award_workflow_error'] = $message;
        }
        header("Location: " . $return);
        exit;
    }
    if (isset($_POST['send_unsent'])) {
        $awards = AwardEmailWorkflow::awards_for_location($db, $location_id, $date, $admin_stem_tech);
        $sent = 0;
        $failed = array();
        foreach ($awards as $award) {
            if ($award['email_sent']) continue;
            list($ok, $message) = AwardEmailWorkflow::send_award_email($db, $award['award_id']);
            if ($ok) $sent++;
            else $failed[] = $award['student_first_name'] . ' ' . $award['student_last_name'] . ': ' . $message;
        }
        $_SESSION['award_workflow_message'] = $sent . ' award email' . ($sent == 1 ? '' : 's') . ' sent.';
        if (count($failed)) $_SESSION['award_workflow_error'] = implode('<br>', $failed);
        header("Location: " . $return);
        exit;
    }
}

$awards = AwardEmailWorkflow::awards_for_location($db, $location_id, $date, $admin_stem_tech);
$pending = 0;
$missing_pdf = 0;
$sent_count = 0;
foreach ($awards as $award) {
    if ($award['email_sent']) $sent_count++;
    else $pending++;
    if (!$award['email_sent'] && !$award['file_pdf_path']) $missing_pdf++;
}

function awa_h($value) {
    return htmlspecialchars(html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8', false);
}

show_header();
show_admin_menu();
$sent_url = str_replace('admin_view_awards', 'admin_view_awards_sent', $_SERVER['REQUEST_URI']);
?>
<style>
body { text-align:left; }
.aw-wrap { position:relative; left:-13px; width:calc(100% + 26px); max-width:1120px; margin: .75rem 0 2rem; padding: 0; box-sizing: border-box; font-family:"Segoe UI", Arial, sans-serif; font-size:13px; }
.aw-wrap * { box-sizing: border-box; }
.aw-wrap h1 { margin:0; color:#f26522; font-family:var(--company); font-size:2.25rem; line-height:1.05; }
.aw-top { display:flex; justify-content:space-between; gap:.75rem; align-items:flex-start; margin-bottom:.65rem; border-bottom:3px solid #f26522; padding-bottom:.55rem; max-width:100%; }
.aw-stats { display:flex; flex-wrap:wrap; gap:.5rem; justify-content:flex-end; }
.aw-stat { padding:.3rem .48rem; border:1px solid #d8e4f0; border-radius:6px; background:#fff; font-weight:800; color:#173b5f; font-size:.82rem; }
.aw-actions { display:flex; flex-wrap:wrap; gap:.4rem; margin:.65rem 0; max-width:100%; align-items:center; }
.aw-actions button, .aw-actions a { display:inline-block; padding:.42rem .58rem; border:0; border-radius:6px; background:#2f7ec1; color:#fff; text-decoration:none; cursor:pointer; font-weight:800; font-size:.82rem; }
.aw-actions button.primary { background:#f26522; }
.aw-actions input, .aw-actions select { border:1px solid #c6d6e6; border-radius:6px; padding:.38rem .45rem; font-size:.82rem; max-width:220px; }
.aw-message, .aw-error { margin:.65rem 0; padding:.55rem .75rem; border-radius:6px; font-weight:800; }
.aw-message { background:#eefaf1; border:1px solid #b8e2c2; color:#247a3d; }
.aw-error { background:#fff2ef; border:1px solid #f3b0a1; color:#a33a21; }
.aw-list { display:grid; gap:.45rem; width:100%; }
.aw-card { width:100%; box-sizing:border-box; background:#fff; border:1px solid #d8e4f0; border-radius:7px; padding:.58rem .65rem; box-shadow:0 4px 12px rgba(11,57,99,.06); scroll-margin-top:110px; overflow:hidden; text-align:left; }
.aw-card.sent { opacity:.68; }
.aw-head { display:grid; grid-template-columns:minmax(210px,.7fr) auto; gap:.5rem; align-items:start; }
.aw-name { font-size:.96rem; font-weight:900; color:#173b5f; text-align:left; }
.aw-meta { color:#526a83; font-size:.78rem; line-height:1.3; text-align:left; }
.aw-status { display:flex; flex-wrap:wrap; gap:.35rem; justify-content:flex-end; }
.aw-pill { padding:.18rem .36rem; border-radius:999px; background:#edf2f7; color:#36546f; font-size:.66rem; font-weight:900; text-transform:uppercase; }
.aw-pill.ok { background:#e9f8ee; color:#247a3d; }
.aw-pill.warn { background:#fff3cd; color:#8a5a00; }
.aw-grid { display:grid; grid-template-columns: minmax(190px, .75fr) minmax(300px, 1.45fr); gap:.48rem; margin-top:.48rem; align-items:start; }
.aw-field-head { display:flex; align-items:baseline; justify-content:space-between; gap:.5rem; margin:0 0 .2rem; }
.aw-grid label { display:block; margin:0; color:#173b5f; font-size:.72rem; font-weight:900; text-transform:uppercase; }
.aw-count { color:#526a83; font-size:.7rem; font-weight:800; white-space:nowrap; }
.aw-count.low { color:#a33a21; }
.aw-grid input, .aw-grid textarea { width:100%; border:1px solid #c6d6e6; border-radius:5px; font-family:"Segoe UI", Arial, sans-serif; font-size:.86rem; line-height:1.25; padding:.34rem .42rem; }
.aw-grid textarea { min-height:30px; height:30px; resize:vertical; overflow:auto; }
.aw-card-actions { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.45rem; }
.aw-card-actions button, .aw-card-actions a { padding:.3rem .48rem; border:0; border-radius:5px; background:#2f7ec1; color:#fff; text-decoration:none; cursor:pointer; font-size:.72rem; font-weight:800; }
.aw-card-actions a.secondary { background:#6b7280; }
@media (max-width:780px) { .aw-wrap { left:0; width:100%; margin-left:auto; margin-right:auto; padding:0 .75rem; } .aw-top, .aw-head, .aw-grid { grid-template-columns:1fr; display:block; } .aw-status { justify-content:flex-start; margin-top:.5rem; } .aw-grid > div { margin-top:.75rem; } }
</style>
<div class="aw-wrap">
    <div class="aw-top">
        <div>
            <h1>Course Awards</h1>
            <div class="aw-meta"><a class="blue" href="admin_home.php">Admin Home</a> :: <?php echo awa_h($date); ?> / Location <?php echo intval($location_id); ?></div>
        </div>
        <div class="aw-stats">
            <div class="aw-stat"><?php echo count($awards); ?> total</div>
            <div class="aw-stat"><?php echo $pending; ?> unsent</div>
            <div class="aw-stat"><?php echo $missing_pdf; ?> need PDF</div>
            <div class="aw-stat"><?php echo $sent_count; ?> sent</div>
        </div>
    </div>

<?php if (isset($_SESSION['award_workflow_message'])) { ?>
    <div class="aw-message"><?php echo awa_h($_SESSION['award_workflow_message']); unset($_SESSION['award_workflow_message']); ?></div>
<?php } ?>
<?php if (isset($_SESSION['award_workflow_error'])) { ?>
    <div class="aw-error"><?php echo $_SESSION['award_workflow_error']; unset($_SESSION['award_workflow_error']); ?></div>
<?php } ?>

    <div class="aw-actions">
        <form id="aw-save-all" method="post" action="<?php echo awa_h($_SERVER['REQUEST_URI']); ?>"></form>
        <button type="submit" form="aw-save-all" name="save_all" value="1">Save All Award Text Changes</button>
        <form method="post" action="<?php echo awa_h($_SERVER['REQUEST_URI']); ?>">
            <button type="submit" name="generate_missing" value="1">Generate Missing PDFs</button>
        </form>
        <form method="post" action="<?php echo awa_h($_SERVER['REQUEST_URI']); ?>">
            <input type="email" name="test_email" placeholder="test email">
            <select name="test_award_id">
<?php foreach ($awards as $test_award) {
    $test_label = trim($test_award['student_first_name'] . ' ' . $test_award['student_last_name']) . ' - ' . $test_award['award_title'];
?>
                <option value="<?php echo intval($test_award['award_id']); ?>"><?php echo awa_h($test_label); ?></option>
<?php } ?>
            </select>
            <button type="submit" name="send_test" value="1">Send Test</button>
        </form>
        <form method="post" action="<?php echo awa_h($_SERVER['REQUEST_URI']); ?>" onsubmit="return confirm('Send every unsent award email for this location/date?');">
            <button class="primary" type="submit" name="send_unsent" value="1">Send All Unsent Award Emails</button>
        </form>
        <a href="award_email_template.php" target="_blank">Edit Email Template</a>
        <a href="<?php echo awa_h($sent_url); ?>">Awards Sent</a>
    </div>

    <div class="aw-list">
<?php if (!count($awards)) { ?>
        <div class="aw-card">No awards found for this location/date.</div>
<?php } ?>
<?php foreach ($awards as $award) {
    $grade_level = $admin->get_grade_level_by_current_year($award['student_id']);
    $pdf_ok = $award['file_pdf_path'] ? true : false;
?>
        <div id="award-<?php echo intval($award['award_id']); ?>" class="aw-card <?php echo $award['email_sent'] ? 'sent' : ''; ?>">
            <div class="aw-head">
                <div>
                    <div class="aw-name"><?php echo awa_h($award['student_first_name'] . ' ' . $award['student_last_name']); ?></div>
                    <div class="aw-meta">
                        <?php echo awa_h($award['course_name']); ?><br>
                        <?php echo awa_h(date('M j', strtotime($award['course_start_date'])) . ' - ' . date('M j, Y', strtotime($award['course_end_date']))); ?> |
                        Grade <?php echo awa_h($grade_level); ?> |
                        Teacher <?php echo awa_h(!empty($award['teacher_names_display']) ? $award['teacher_names_display'] : trim($award['teacher_first_name'] . ' ' . $award['teacher_last_name'])); ?>
                    </div>
                </div>
                <div class="aw-status">
                    <span class="aw-pill <?php echo $award['email_sent'] ? 'ok' : 'warn'; ?>"><?php echo $award['email_sent'] ? 'Sent' : 'Unsent'; ?></span>
                    <span class="aw-pill <?php echo $pdf_ok ? 'ok' : 'warn'; ?>"><?php echo $pdf_ok ? 'PDF Ready' : 'Needs PDF'; ?></span>
                    <?php if ($award['camper_of_the_week'] == 'y') { ?><span class="aw-pill">Big Bravo</span><?php } ?>
                    <?php if ($award['best_in_class'] == 'y') { ?><span class="aw-pill">Special Recognition</span><?php } ?>
                </div>
            </div>
                <div class="aw-grid">
                    <div>
                        <div class="aw-field-head">
                            <label>Award Title</label>
                            <span class="aw-count" data-aw-count-for="title-<?php echo intval($award['award_id']); ?>">0 left</span>
                        </div>
                        <input id="title-<?php echo intval($award['award_id']); ?>" form="aw-save-all" type="text" name="award_title[<?php echo intval($award['award_id']); ?>]" maxlength="34" value="<?php echo awa_h($award['award_title']); ?>">
                    </div>
                    <div>
                        <div class="aw-field-head">
                            <label>Reason</label>
                            <span class="aw-count" data-aw-count-for="reason-<?php echo intval($award['award_id']); ?>">0 left</span>
                        </div>
                        <textarea id="reason-<?php echo intval($award['award_id']); ?>" form="aw-save-all" name="reason[<?php echo intval($award['award_id']); ?>]" maxlength="120" rows="1"><?php echo awa_h($award['reason']); ?></textarea>
                    </div>
                </div>
                <div class="aw-card-actions">
                    <button form="aw-save-all" type="submit" name="save_award" value="<?php echo intval($award['award_id']); ?>">Save This Award</button>
                    <?php if ($award['file_pdf_path']) { ?><a class="secondary" target="_blank" href="<?php echo awa_h($award['file_pdf_path']); ?>">View PDF</a><?php } ?>
                    <a class="secondary" href="admin_edit_award.php?award_id=<?php echo intval($award['award_id']); ?>&date=<?php echo urlencode($date); ?>&location_id=<?php echo intval($location_id); ?>">Full Edit</a>
                    <a class="secondary" href="admin_delete_award.php?award_id=<?php echo intval($award['award_id']); ?>&date=<?php echo urlencode($date); ?>&location_id=<?php echo intval($location_id); ?>" onclick="return confirmDelete();">Delete</a>
                </div>
        </div>
<?php } ?>
    </div>
</div>
<script type="text/javascript">
(function() {
    function updateCount(field) {
        var counter = document.querySelector('[data-aw-count-for="' + field.id + '"]');
        if (!counter) return;
        var max = parseInt(field.getAttribute('maxlength'), 10) || 0;
        var left = Math.max(0, max - field.value.length);
        counter.innerHTML = left + ' left';
        if (left <= 5) counter.className = 'aw-count low';
        else counter.className = 'aw-count';
    }

    var fields = document.querySelectorAll('.aw-grid input[maxlength], .aw-grid textarea[maxlength]');
    for (var i = 0; i < fields.length; i++) {
        updateCount(fields[i]);
        fields[i].onkeyup = fields[i].onchange = fields[i].oninput = function() {
            updateCount(this);
        };
    }
})();
</script>
<?php
show_footer();
ob_end_flush();
?>
