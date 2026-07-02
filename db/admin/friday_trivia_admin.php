<?php
ob_start();
session_start();
require(dirname(__FILE__) . '/admin_config.php');
require_once(dirname(__FILE__) . '/inc/FridayTrivia.php');

$admin_id = $_SESSION['admin_id'];
if (!$admin_id) {
    header("Location: admin_login.php");
    exit;
}

$db = connect_database();
FridayTrivia::ensure_tables($db);
$locations = FridayTrivia::allowed_locations($admin_id);
$location_ids = array_keys($locations);
$weeks = FridayTrivia::week_options($db, $location_ids);
$default_week = FridayTrivia::default_week($weeks);
$can_delete_finished_trivia = FridayTrivia::can_delete_finished_games($db, $admin_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'upload_game';
    if ($action == 'toggle_game') {
        list($ok, $message) = FridayTrivia::set_game_active(
            $db,
            isset($_POST['game_id']) ? intval($_POST['game_id']) : 0,
            $location_ids,
            isset($_POST['active']) ? intval($_POST['active']) : 0
        );
        if ($ok) $_SESSION['friday_trivia_message'] = $message;
        else $_SESSION['friday_trivia_error'] = $message;
    } elseif ($action == 'delete_game') {
        list($ok, $message) = FridayTrivia::delete_game(
            $db,
            isset($_POST['game_id']) ? intval($_POST['game_id']) : 0,
            $location_ids,
            $admin_id
        );
        if ($ok) $_SESSION['friday_trivia_message'] = $message;
        else $_SESSION['friday_trivia_error'] = $message;
    } elseif ($action == 'begin_game') {
        list($ok, $message) = FridayTrivia::begin_game(
            $db,
            isset($_POST['game_id']) ? intval($_POST['game_id']) : 0,
            $location_ids,
            $admin_id
        );
        if ($ok) $_SESSION['friday_trivia_message'] = $message;
        else $_SESSION['friday_trivia_error'] = $message;
    } elseif ($action == 'reset_game_start') {
        list($ok, $message) = FridayTrivia::reset_game_start(
            $db,
            isset($_POST['game_id']) ? intval($_POST['game_id']) : 0,
            $location_ids
        );
        if ($ok) $_SESSION['friday_trivia_message'] = $message;
        else $_SESSION['friday_trivia_error'] = $message;
    } elseif ($action == 'reset_student') {
        list($ok, $message) = FridayTrivia::reset_student(
            $db,
            isset($_POST['game_id']) ? intval($_POST['game_id']) : 0,
            isset($_POST['student_id']) ? intval($_POST['student_id']) : 0,
            $location_ids
        );
        if ($ok) $_SESSION['friday_trivia_message'] = $message;
        else $_SESSION['friday_trivia_error'] = $message;
        $redirect_game = isset($_POST['game_id']) ? intval($_POST['game_id']) : 0;
        header("Location: friday_trivia_admin.php?progress_game_id=" . $redirect_game);
        exit;
    } else {
        $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
        if (!isset($locations[$location_id])) {
            $_SESSION['friday_trivia_error'] = 'That location is not available for your account.';
        } else {
            list($ok, $message) = FridayTrivia::save_game_from_csv(
                $db,
                $admin_id,
                $location_id,
                $_POST['week_start'],
                $_POST['title'],
                isset($_FILES['csv_file']['tmp_name']) ? $_FILES['csv_file']['tmp_name'] : ''
            );
            if ($ok) $_SESSION['friday_trivia_message'] = $message;
            else $_SESSION['friday_trivia_error'] = $message;
        }
    }
    header("Location: friday_trivia_admin.php");
    exit;
}

$games = FridayTrivia::games_for_locations($db, $location_ids);
$progress_game_id = isset($_GET['progress_game_id']) ? intval($_GET['progress_game_id']) : 0;
$progress_game = null;
$progress_campers = array();
if ($progress_game_id) {
    list($progress_game, $progress_campers) = FridayTrivia::campers_for_game($db, $progress_game_id, $location_ids);
}

function ft_h($value) {
    return FridayTrivia::h($value);
}

show_header();
show_admin_menu();
?>
<style>
body { text-align:left; background:#edf4fa; }
.ft-wrap { max-width:1040px; margin:24px auto 50px; padding:0 16px; font-family:"Segoe UI",Arial,sans-serif; color:#173b5f; }
.ft-hero { background:#0f4778; color:#fff; border-top:8px solid #ff9700; border-radius:8px; padding:22px 24px; box-shadow:0 10px 26px rgba(15,71,120,.16); }
.ft-hero h1 { margin:0; font-size:34px; line-height:40px; font-weight:900; }
.ft-hero p { margin:8px 0 0; color:#dcecff; font-size:15px; line-height:23px; }
.ft-hero a { color:#fff; font-weight:900; }
.ft-card { margin-top:18px; background:#fff; border:1px solid #d8e4f0; border-radius:8px; padding:18px; box-shadow:0 8px 24px rgba(11,57,99,.08); }
.ft-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.ft-field label { display:block; margin:0 0 5px; color:#314761; font-size:12px; font-weight:900; text-transform:uppercase; }
.ft-label-row { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:0 0 5px; }
.ft-label-row label { margin:0; }
.ft-field input, .ft-field select { width:100%; height:38px; border:1px solid #c6d6e6; border-radius:6px; padding:0 10px; box-sizing:border-box; }
.ft-field input[type=file] { height:auto; padding:8px; background:#f8fbfe; }
.ft-actions { margin-top:14px; display:flex; align-items:center; gap:12px; }
.ft-button { border:0; border-radius:7px; background:#f26522; color:#fff; padding:11px 16px; font-weight:900; cursor:pointer; }
.ft-button.small { display:inline-flex; align-items:center; justify-content:center; min-height:30px; padding:7px 10px; font-size:12px; line-height:16px; border-radius:6px; text-decoration:none; box-sizing:border-box; white-space:nowrap; vertical-align:middle; }
.ft-button.blue { background:#0f4778; }
.ft-button.green { background:#08743a; }
.ft-button.orange { background:#f26522; }
.ft-button.gray { background:#64748b; }
.ft-button.red { background:#be123c; }
.ft-inline-form { display:inline; margin:0; }
.ft-controls { display:flex; align-items:center; gap:7px; flex-wrap:nowrap; min-width:278px; }
.ft-controls .ft-button { min-width:78px; }
.ft-controls .ft-button.begin { min-width:76px; }
.ft-controls .ft-button.progress { min-width:116px; }
.ft-controls .ft-button.delete { min-width:72px; }
.ft-status { display:inline-block; padding:5px 8px; border-radius:999px; font-weight:900; font-size:12px; }
.ft-status.active { background:#dcfce7; color:#166534; }
.ft-status.off { background:#f1f5f9; color:#475569; }
.ft-status.standby { background:#fff7ed; color:#9a3412; }
.ft-status.started { background:#dbeafe; color:#1d4ed8; }
.ft-status.done { background:#ecfdf3; color:#027a48; }
.ft-status.progress { background:#fff7ed; color:#9a3412; }
.ft-status.waiting { background:#eef5fb; color:#0f4778; }
.ft-help { margin-top:10px; padding:10px 12px; background:#fff7ed; border:1px solid #fed7aa; border-radius:7px; color:#8a3a0a; font-size:13px; line-height:20px; }
.ft-small-link { font-size:12px; line-height:18px; color:#0f4778; font-weight:800; }
.ft-message { margin-top:16px; padding:11px 13px; border-radius:7px; font-weight:900; }
.ft-message.ok { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
.ft-message.err { background:#fff1f2; border:1px solid #fecdd3; color:#9f1239; }
.ft-table { width:100%; border-collapse:collapse; margin-top:12px; }
.ft-table th, .ft-table td { padding:9px 8px; border-bottom:1px solid #e6eef6; text-align:left; font-size:13px; }
.ft-table th { color:#0f4778; text-transform:uppercase; font-size:11px; letter-spacing:.04em; }
.ft-table .ft-control-cell { width:300px; }
@media (max-width:720px) { .ft-grid { grid-template-columns:1fr; } }
</style>
<div class="ft-wrap">
    <div class="ft-hero">
        <h1>PlanetBravo Trivia</h1>
        <p>Upload one CSV per location and camp week. Campers play <strong><a target="_blank" href="https://www.planetbravo.com/trivia/friday.php">HERE</a></strong>, and correct answers automatically add BravoPoints.</p>
    </div>

    <?php if (!empty($_SESSION['friday_trivia_message'])) { ?>
        <div class="ft-message ok"><?php echo ft_h($_SESSION['friday_trivia_message']); unset($_SESSION['friday_trivia_message']); ?></div>
    <?php } ?>
    <?php if (!empty($_SESSION['friday_trivia_error'])) { ?>
        <div class="ft-message err"><?php echo ft_h($_SESSION['friday_trivia_error']); unset($_SESSION['friday_trivia_error']); ?></div>
    <?php } ?>

    <div class="ft-card">
        <h2>Load Trivia for a Location</h2>
        <form method="post" enctype="multipart/form-data" action="friday_trivia_admin.php">
            <input type="hidden" name="action" value="upload_game">
            <div class="ft-grid">
                <div class="ft-field">
                    <label for="location_id">Location</label>
                    <select name="location_id" id="location_id" required>
                        <?php foreach ($locations as $id => $name) { ?>
                            <option value="<?php echo intval($id); ?>"><?php echo ft_h($name); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="ft-field">
                    <label for="week_start">Camp Week</label>
                    <select name="week_start" id="week_start" required>
                        <?php foreach ($weeks as $week) { ?>
                            <option value="<?php echo ft_h($week); ?>"<?php if ($week == $default_week) echo ' selected'; ?>><?php echo ft_h(date('M j, Y', strtotime($week))); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="ft-field">
                    <label for="title">Game Title</label>
                    <input type="text" name="title" id="title" value="PlanetBravo Trivia">
                </div>
                <div class="ft-field">
                    <div class="ft-label-row">
                        <label for="csv_file">CSV File</label>
                        <a class="ft-small-link" href="/trivia/Sample_Trivia_Upload.csv" download>Download sample CSV</a>
                    </div>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv,text/csv" required>
                </div>
            </div>
            <div class="ft-help">CSV format: <strong>Question, Correct Answer, Wrong Answer, Wrong Answer, Wrong Answer</strong>. Choices are randomized for campers.</div>
            <div class="ft-actions">
                <button class="ft-button" type="submit">Upload Trivia</button>
                <a class="blue" href="/trivia/friday.php" target="_blank">Open camper page</a>
            </div>
        </form>
    </div>

    <div class="ft-card">
        <h2>Loaded Games</h2>
        <table class="ft-table">
            <tr><th>Week</th><th>Location</th><th>Title</th><th>Questions</th><th>Attempts</th><th>Status</th><th>Controls</th></tr>
            <?php foreach ($games as $game) { ?>
                <tr>
                    <td><?php echo ft_h(date('M j, Y', strtotime($game['week_start']))); ?></td>
                    <td><?php echo ft_h($game['school_name']); ?></td>
                    <td><?php echo ft_h($game['title']); ?></td>
                    <td><?php echo intval($game['question_count']); ?></td>
                    <td><?php echo intval($game['attempt_count']); ?></td>
                    <td>
                        <?php if (!$game['active']) { ?>
                            <span class="ft-status off">Off</span>
                        <?php } elseif (!empty($game['started_at'])) { ?>
                            <span class="ft-status started">Started</span>
                        <?php } else { ?>
                            <span class="ft-status standby">Standby</span>
                        <?php } ?>
                    </td>
                    <td class="ft-control-cell">
                        <div class="ft-controls">
                        <form class="ft-inline-form" method="post" action="friday_trivia_admin.php">
                            <input type="hidden" name="action" value="toggle_game">
                            <input type="hidden" name="game_id" value="<?php echo intval($game['game_id']); ?>">
                            <input type="hidden" name="active" value="<?php echo $game['active'] ? 0 : 1; ?>">
                            <button class="ft-button small <?php echo $game['active'] ? 'gray' : 'green'; ?>" type="submit"><?php echo $game['active'] ? 'Turn Off' : 'Activate'; ?></button>
                        </form>
                        <form class="ft-inline-form" method="post" action="friday_trivia_admin.php">
                            <input type="hidden" name="action" value="<?php echo !empty($game['started_at']) ? 'reset_game_start' : 'begin_game'; ?>">
                            <input type="hidden" name="game_id" value="<?php echo intval($game['game_id']); ?>">
                            <button class="ft-button small begin <?php echo !empty($game['started_at']) ? 'gray' : 'orange'; ?>" type="submit"><?php echo !empty($game['started_at']) ? 'Standby' : 'Begin'; ?></button>
                        </form>
                        <a class="ft-button small blue progress" href="friday_trivia_admin.php?progress_game_id=<?php echo intval($game['game_id']); ?>#camper-progress">Camper Progress</a>
                        <?php if ((int)$game['attempt_count'] == 0 || $can_delete_finished_trivia) { ?>
                        <form class="ft-inline-form" method="post" action="friday_trivia_admin.php" onsubmit="return confirm('<?php echo (int)$game['attempt_count'] > 0 ? 'This trivia has completed camper attempts. Delete it anyway? Existing BravoPoints totals will not be changed.' : 'Delete this trivia game? This removes its questions and any in-progress camper sessions.'; ?>');">
                            <input type="hidden" name="action" value="delete_game">
                            <input type="hidden" name="game_id" value="<?php echo intval($game['game_id']); ?>">
                            <button class="ft-button small red delete" type="submit">Delete</button>
                        </form>
                        <?php } ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            <?php if (!count($games)) { ?><tr><td colspan="7">No PlanetBravo Trivia games have been loaded yet.</td></tr><?php } ?>
        </table>
    </div>

    <?php if ($progress_game_id) { ?>
        <div class="ft-card" id="camper-progress">
            <?php if (!$progress_game) { ?>
                <h2>Camper Progress</h2>
                <p>That trivia game is not available for your account.</p>
            <?php } else { ?>
                <h2>Camper Progress</h2>
                <div class="ft-help">
                    <strong><?php echo ft_h($progress_game['school_name']); ?></strong> -
                    <?php echo ft_h(date('M j, Y', strtotime($progress_game['week_start']))); ?> -
                    <?php echo ft_h($progress_game['title']); ?>
                </div>
                <table class="ft-table">
                    <tr><th>Camper</th><th>Username</th><th>Status</th><th>Progress</th><th>Score</th><th>Points</th><th>Reset</th></tr>
                    <?php foreach ($progress_campers as $camper) {
                        $done = !empty($camper['attempt_id']);
                        $in_progress = !$done && $camper['total_questions'] !== null && intval($camper['total_questions']) > 0;
                        $status_class = $done ? 'done' : ($in_progress ? 'progress' : 'waiting');
                        $status_text = $done ? 'Complete' : ($in_progress ? 'In Progress' : 'Not Started');
                    ?>
                        <tr>
                            <td><?php echo ft_h(trim($camper['student_first_name'] . ' ' . $camper['student_last_name'])); ?></td>
                            <td><?php echo ft_h($camper['login_username']); ?></td>
                            <td><span class="ft-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                            <td>
                                <?php if ($done) { ?>
                                    Finished <?php echo ft_h(date('M j, g:i a', strtotime($camper['submitted_at']))); ?>
                                <?php } elseif ($in_progress) { ?>
                                    Question <?php echo intval($camper['current_index']) + 1; ?> of <?php echo intval($camper['total_questions']); ?>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                            <td><?php echo $done ? intval($camper['score']) . ' / ' . intval($camper['total']) : '-'; ?></td>
                            <td><?php echo $done ? intval($camper['points_awarded']) : '-'; ?></td>
                            <td>
                                <?php if ($done || $in_progress) { ?>
                                    <form class="ft-inline-form" method="post" action="friday_trivia_admin.php" onsubmit="return confirm('Reset trivia for this camper? Existing BravoPoints will not be changed.');">
                                        <input type="hidden" name="action" value="reset_student">
                                        <input type="hidden" name="game_id" value="<?php echo intval($progress_game['game_id']); ?>">
                                        <input type="hidden" name="student_id" value="<?php echo intval($camper['student_id']); ?>">
                                        <button class="ft-button small red" type="submit">Reset</button>
                                    </form>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!count($progress_campers)) { ?><tr><td colspan="7">No active campers found for this trivia week.</td></tr><?php } ?>
                </table>
            <?php } ?>
        </div>
    <?php } ?>
</div>
<?php
show_footer();
ob_end_flush();
?>
