<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('NO_MOODLE_COOKIES', true);
require_once(__DIR__ . '/../../../../config.php');

header('Content-Type: application/json');

$rawinput = file_get_contents("php://input");
$data = json_decode($rawinput, true);

if (!$data || !isset($data['submission_id'], $data['score'], $data['file_checked'], $data['url'], $data['submission_title'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$submissionid = (int)$data['submission_id'];
$score = $data['score'] . '%';
$reporturl = $data['url'];
$submission_title = basename($data['submission_title']);
$file_checked_base64 = $data['file_checked'];

try {
    global $DB;

    $filesub = new stdClass();
    $filesub->submissionid = $submissionid;
    $filesub->plagiarism = $score;
    $filesub->urlfilechecked = $reporturl;

    $submission = $DB->get_record('assign_submission', ['id' => $submissionid]);
    if (!$submission) {
        http_response_code(404);
        echo json_encode(['error' => 'Submission not found']);
        exit;
    }
    if ($DB->record_exists('assignsub_eduplag_res', ['submissionid' => $submissionid])) {
        // Lấy id hiện có để cập nhật
        error_log('Record exists: ' . ($exists ? 'true' : 'false'));
        $existing = $DB->get_record('assignsub_eduplag_res', ['submissionid' => $submissionid], 'id', MUST_EXIST);
        $filesub->id = $existing->id;
        $updated = $DB->update_record('assignsub_eduplag_res', $filesub);
    } else {
        $updated = $DB->insert_record('assignsub_eduplag_res', $filesub);
    }



    $cm = get_coursemodule_from_instance('assign', $submission->assignment);
    $context = context_module::instance($cm->id);
    $fs = get_file_storage();

    $filename = 'checked_' . $submission_title;

    $existingfile = $fs->get_file(
        $context->id,
        'assignsubmission_file',
        'checked_file',
        $submissionid,
        '/',
        $filename
    );

    if ($existingfile) {
        $existingfile->delete();
    }

    $filerecord = [
        'contextid' => $context->id,
        'component' => 'assignsubmission_file',
        'filearea'  => 'checked_file',
        'itemid'    => $submissionid,
        'filepath'  => '/',
        'filename'  => $filename,
    ];

    $fs->create_file_from_string($filerecord, base64_decode($file_checked_base64));



    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
