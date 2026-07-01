<?php
ob_start();
session_start();
require(dirname(__FILE__) . '/admin_config.php');
require_once(dirname(__FILE__) . '/../inc/RotatingSites.php');

$admin_id = $_SESSION['admin_id'];
if (!$admin_id) {
  header("Location: admin_login.php");
  exit;
}

$connection = connect_database();
$rotating = new RotatingSites();
$rotating->ensure_tables($connection);
$weeks = $rotating->camp_weeks($connection);
$week_start = (!empty($_REQUEST['week_start']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_REQUEST['week_start'])) ? $_REQUEST['week_start'] : (count($weeks) ? $weeks[0] : $rotating->today());
$notice = '';
$error = '';

function pb_rotating_admin_redirect($week_start) {
  header("Location: rotating_sites_schedule.php?week_start=" . urlencode($week_start));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['action']) && $_POST['action'] == 'save_schedule') {
  $dates = !empty($_POST['dates']) && is_array($_POST['dates']) ? $_POST['dates'] : array();
  foreach ($dates as $date) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) continue;
    $brain_id = !empty($_POST['braingames'][$date]) ? (int)$_POST['braingames'][$date] : 0;
    $surprise_id = !empty($_POST['surprise'][$date]) ? (int)$_POST['surprise'][$date] : 0;
    $rotating->save_schedule($connection, 'braingames', $date, $brain_id, $admin_id);
    $rotating->save_schedule($connection, 'surprise', $date, $surprise_id, $admin_id);
  }
  $_SESSION['pb_rotating_notice'] = 'Rotating site schedule saved.';
  pb_rotating_admin_redirect($week_start);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['action']) && $_POST['action'] == 'clear_week') {
  $week = new DateTime($week_start, $rotating->timezone());
  for ($i = 0; $i < 5; $i++) {
    $day = clone $week;
    $day->modify("+$i days");
    $date_sql = mysqli_real_escape_string($connection, $day->format('Y-m-d'));
    mysqli_query($connection, "DELETE FROM rotating_site_schedule WHERE display_date = '$date_sql'");
  }
  $_SESSION['pb_rotating_notice'] = 'Schedule cleared for this camp week.';
  pb_rotating_admin_redirect($week_start);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['action']) && $_POST['action'] == 'clear_all') {
  mysqli_query($connection, "TRUNCATE TABLE rotating_site_schedule");
  $_SESSION['pb_rotating_notice'] = 'All Brain Games and Surprise schedule rows were cleared.';
  pb_rotating_admin_redirect($week_start);
}

if (!empty($_SESSION['pb_rotating_notice'])) {
  $notice = $_SESSION['pb_rotating_notice'];
  unset($_SESSION['pb_rotating_notice']);
}

$week = new DateTime($week_start, $rotating->timezone());
$dates = array();
for ($i = 0; $i < 5; $i++) {
  $day = clone $week;
  $day->modify("+$i days");
  $dates[] = $day->format('Y-m-d');
}

$brain_sites = $rotating->site_list($connection, 'braingames');
$surprise_sites = $rotating->site_list($connection, 'surprise');
$brain_schedule = $rotating->schedule_map($connection, 'braingames', $week_start, 5);
$surprise_schedule = $rotating->schedule_map($connection, 'surprise', $week_start, 5);

show_header();
show_admin_menu();
?>
<style>
.rs-admin { max-width: 1120px; margin: 0 auto 44px; font-family: Arial, Helvetica, sans-serif; color: #102b4f; }
.rs-hero { margin: 18px 0 16px; border-bottom: 3px solid #f45b20; padding-bottom: 12px; }
.rs-hero h1 { margin: 0; color: #f45b20; font-size: 38px; line-height: 44px; font-weight: 900; }
.rs-hero p { margin: 6px 0 0; color: #526174; font-size: 15px; line-height: 23px; }
.rs-panel { background: #fff; border: 1px solid #d6e3ef; border-radius: 8px; padding: 18px; margin: 16px 0; box-shadow: 0 10px 26px rgba(16,43,79,.07); }
.rs-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: end; }
.rs-field label { display:block; font-weight:900; font-size:12px; text-transform:uppercase; margin-bottom:5px; color:#102b4f; }
.rs-field select { height: 36px; min-width: 230px; border: 1px solid #bcd0e3; border-radius: 5px; padding: 0 10px; }
.rs-button { background:#2f80c7; color:#fff; border:0; border-radius:6px; padding:10px 14px; font-weight:900; cursor:pointer; text-decoration:none; display:inline-block; }
.rs-button.orange { background:#f45b20; }
.rs-notice { padding:12px 14px; background:#eaf8ef; border:1px solid #bde4c8; border-radius:6px; color:#0b6b2e; margin:12px 0; }
.rs-table { width: 100%; border-collapse: collapse; }
.rs-table th { text-align:left; padding:10px; background:#f1f7fd; color:#102b4f; font-size:13px; text-transform:uppercase; }
.rs-table td { padding:10px; border-top:1px solid #e5eef6; vertical-align:top; }
.rs-table select { width:100%; max-width:420px; height:34px; border:1px solid #bcd0e3; border-radius:5px; }
.rs-date { font-weight:900; color:#102b4f; }
.rs-small { color:#526174; font-size:13px; line-height:19px; }
.rs-links { display:flex; gap:10px; flex-wrap:wrap; margin-top:10px; }
</style>
<div class="rs-admin">
  <div class="rs-hero">
    <h1>Brain Games & Surprise Schedule</h1>
    <p>Schedule exactly what appears on each Pacific date. Current Pacific time: <strong><?php echo htmlspecialchars($rotating->now_label(), ENT_QUOTES, 'UTF-8'); ?></strong>.</p>
  </div>

  <?php if ($notice) { ?><div class="rs-notice"><?php echo htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>

  <div class="rs-panel">
    <form method="get" action="rotating_sites_schedule.php" class="rs-row" id="rs-week-form">
      <div class="rs-field">
        <label>Camp Week</label>
        <select name="week_start" id="rs-week-start">
          <?php foreach ($weeks as $week_option) { ?>
            <option value="<?php echo htmlspecialchars($week_option, ENT_QUOTES, 'UTF-8'); ?>"<?php if ($week_option == $week_start) echo ' selected'; ?>><?php echo date('M j, Y', strtotime($week_option)); ?></option>
          <?php } ?>
        </select>
      </div>
      <button class="rs-button" type="submit">Load Week</button>
    </form>
    <div class="rs-links">
      <a class="rs-button" target="_blank" href="/braingames">Open Brain Games</a>
      <a class="rs-button" target="_blank" href="/surprise">Open Surprise</a>
      <a class="rs-button" target="_blank" href="/braingames?archive=1">Past Brain Games</a>
      <a class="rs-button" target="_blank" href="/surprise?archive=1">Past Surprises</a>
    </div>
    <div class="rs-links" style="margin-top:14px;">
      <form method="post" action="rotating_sites_schedule.php" onsubmit="return confirm('Clear Brain Games and Surprise schedule rows for this loaded week?');">
        <input type="hidden" name="action" value="clear_week">
        <input type="hidden" name="week_start" value="<?php echo htmlspecialchars($week_start, ENT_QUOTES, 'UTF-8'); ?>">
        <button class="rs-button" type="submit" style="background:#6b7280;">Clear This Week</button>
      </form>
      <form method="post" action="rotating_sites_schedule.php" onsubmit="return confirm('Clear ALL Brain Games and Surprise schedule rows? Source site lists will remain.');">
        <input type="hidden" name="action" value="clear_all">
        <input type="hidden" name="week_start" value="<?php echo htmlspecialchars($week_start, ENT_QUOTES, 'UTF-8'); ?>">
        <button class="rs-button" type="submit" style="background:#992b1d;">Clear All Schedules</button>
      </form>
    </div>
  </div>

  <form method="post" action="rotating_sites_schedule.php">
    <input type="hidden" name="action" value="save_schedule">
    <input type="hidden" name="week_start" value="<?php echo htmlspecialchars($week_start, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="rs-panel">
      <table class="rs-table">
        <thead>
          <tr>
            <th style="width:180px;">Pacific Date</th>
            <th>Brain Games</th>
            <th>Surprise</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($dates as $date) { ?>
            <tr>
              <td>
                <div class="rs-date"><?php echo date('l', strtotime($date)); ?></div>
                <div class="rs-small"><?php echo date('M j, Y', strtotime($date)); ?></div>
                <input type="hidden" name="dates[]" value="<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>">
              </td>
              <td>
                <select name="braingames[<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>]">
                  <option value="0">No scheduled Brain Game</option>
                  <?php foreach ($brain_sites as $site) { ?>
                    <option value="<?php echo (int)$site['id']; ?>"<?php if (!empty($brain_schedule[$date]) && (int)$brain_schedule[$date] == (int)$site['id']) echo ' selected'; ?>><?php echo htmlspecialchars($site['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php } ?>
                </select>
              </td>
              <td>
                <select name="surprise[<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>]">
                  <option value="0">No scheduled Surprise</option>
                  <?php foreach ($surprise_sites as $site) { ?>
                    <option value="<?php echo (int)$site['id']; ?>"<?php if (!empty($surprise_schedule[$date]) && (int)$surprise_schedule[$date] == (int)$site['id']) echo ' selected'; ?>><?php echo htmlspecialchars($site['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php } ?>
                </select>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
      <div style="margin-top:16px;text-align:right;">
        <button class="rs-button orange" type="submit">Save Schedule</button>
      </div>
    </div>
  </form>
</div>
<script>
(function() {
  var weekSelect = document.getElementById('rs-week-start');
  var weekForm = document.getElementById('rs-week-form');
  if (weekSelect && weekForm) {
    weekSelect.onchange = function() {
      weekForm.submit();
    };
  }
})();
</script>
<?php
show_footer();
ob_flush();
?>
