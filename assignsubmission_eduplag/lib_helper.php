<?php
defined('MOODLE_INTERNAL') || die();

function eduplag_get_checkbox_values($assignmentid) {
    global $DB;
    $data = $DB->get_record('assignsub_eduplag_cfg', ['assignmentid' => $assignmentid]);
    return [
        'checkplagiarism' => $data ? (int)$data->checkplagiarism : 0,
        'viewstudent' => $data ? (int)$data->viewstudent : 0
    ];
}

function eduplag_save_checkbox_values($assignmentid, $checkplagiarism, $viewstudent) {
    global $DB;
    if ($checkplagiarism === 0) {
        $viewstudent = 0;
    }
    $newdata = (object)[
        'assignmentid'    => $assignmentid,
        'checkplagiarism' => $checkplagiarism,
        'viewstudent'     => $viewstudent
    ];
    $record = $DB->get_record('assignsub_eduplag_cfg', ['assignmentid' => $assignmentid]);
    if ($record) {
        $newdata->id = $record->id;
        $DB->update_record('assignsub_eduplag_cfg', $newdata);
    } else {
        $DB->insert_record('assignsub_eduplag_cfg', $newdata);
    }
}

function eduplag_add_checkboxes_to_form(MoodleQuickForm $mform) {
    $mform->addElement('header', 'eduplag', get_string('eduplag', 'assignsubmission_eduplag'));
    $mform->addElement('advcheckbox', 'checkplagiarism', '', get_string('checkplagiarism', 'assignsubmission_eduplag'));
    $mform->setDefault('checkplagiarism', 0);
    $mform->addHelpButton('checkplagiarism', 'checkplagiarism', 'assignsubmission_eduplag');
    $mform->addElement('advcheckbox', 'viewstudent', '', get_string('viewstudent', 'assignsubmission_eduplag'));
    $mform->setDefault('viewstudent', 0);
    $mform->addHelpButton('viewstudent', 'viewstudent', 'assignsubmission_eduplag');
    $mform->disabledIf('viewstudent', 'checkplagiarism', 'notchecked');
}