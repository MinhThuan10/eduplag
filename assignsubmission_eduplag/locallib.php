<?php

defined('MOODLE_INTERNAL') || die();

class assign_submission_eduplag extends assign_submission_plugin {

    public function get_name() {
        return get_string('eduplag', 'assignsubmission_eduplag');
    }


    public function remove(stdClass $submission) {
        global $DB, $SITE, $USER;

        $contextid = $this->assignment->get_context()->id;
        $submissionid = $submission->id;

        $fs = get_file_storage();

        $fs->delete_area_files(
            $contextid,
            'assignsubmission_file',
            'checked_file',
            $submissionid
        );

        $DB->delete_records('assignsub_eduplag_res', ['submissionid' => $submissionid]);

        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');
        $fullname = $SITE->fullname;

        $payload = json_encode([
            'school_name'      => $fullname,
            'school_key'       => $schoolkey,
            'email'            => $USER->email,
            'class_id'         => $this->assignment->get_course()->id,
            'assignment_id'    => $this->assignment->get_instance()->id,
        ]);

        $ch = curl_init('http://172.30.24.172/mod/api/delete_file');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch) || $httpcode >= 400) {
            debugging("Eduplag DELETE failed: " . curl_error($ch) . " - HTTP $httpcode", DEBUG_DEVELOPER);
        }

        curl_close($ch);

        return true;
    }

    public function has_user_summary() {
        return false;
    }


    public static function render_student_plagiarism_info($submission, $coursemoduleid, $contextid) {
        global $DB;
        $output = '';

        $plagiarism_check = $DB->get_record('assignsub_eduplag_cfg', ['assignmentid' => $submission->assignment]);
        $plagiarism_data = $DB->get_record('assignsub_eduplag_res', ['submissionid' => $submission->id]);

        if ($plagiarism_check && $plagiarism_check->viewstudent == 1 && $plagiarism_data) {
            // Score.
            $output .= '<div>' . get_string('plagiarism', 'assignsubmission_eduplag') . ': ' . $plagiarism_data->plagiarism . '</div>';

            // URL báo cáo.
            if (!empty($plagiarism_data->urlfilechecked)) {
                $url = \html_writer::link($plagiarism_data->urlfilechecked, 'View Report', ['target' => '_blank']);
                $output .= '<div>' . get_string('urlfilechecked', 'assignsubmission_eduplag') . ': ' . $url . '</div>';
            }

            // File đã kiểm tra đạo văn.
            $fs = get_file_storage();
            $context = \context_module::instance($coursemoduleid);
            $files = $fs->get_area_files(
                $context->id,
                'assignsubmission_file',
                'checked_file',
                $submission->id,
                'timemodified',
                false
            );

            if (!empty($files)) {
                $file = reset($files);
                $downloadurl = \moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
                $link = \html_writer::link(
                    $downloadurl,
                    \html_writer::tag('i', '', ['class' => 'icon fa fa-file']) . ' ' . $file->get_filename(),
                    ['target' => '_blank']
                );
                $output .= '<div>' . get_string('checkedfile', 'assignsubmission_eduplag') . ': ' . $link . '</div>';
            }
        }
        return $output;
    }

}

function assignsubmission_eduplag_add_grading_columns($assignment, &$columns, &$headers) {
    global $DB;
    $data = $DB->get_record('assignsub_eduplag_cfg', ['assignmentid' => $assignment->get_instance()->id]);
    if ($data && $data->checkplagiarism == 1) {
        $columns[] = 'plagiarism';
        $headers[] = get_string('plagiarism', 'assignsubmission_eduplag');

        $columns[] = 'urlfilechecked';
        $headers[] = get_string('urlfilechecked', 'assignsubmission_eduplag');

        $columns[] = 'checkedfile';
        $headers[] = get_string('checkedfile', 'assignsubmission_eduplag');
    }
}

function assignsubmission_eduplag_col_plagiarism($row) {
    global $DB;
    $data = $DB->get_record('assignsub_eduplag_res', ['submissionid' => $row->submissionid]);
    if (!$data || is_null($data->plagiarism)) {
        return '';
    }
    return $data->plagiarism;
}

function assignsubmission_eduplag_col_urlfilechecked($row) {
    global $DB;
    $data = $DB->get_record('assignsub_eduplag_res', ['submissionid' => $row->submissionid]);
    if (!$data || empty($data->urlfilechecked)) {
        return '';
    }
    return \html_writer::link($data->urlfilechecked, 'View Report', ['target' => '_blank']);
}

function assignsubmission_eduplag_col_checkedfile($row) {
    global $CFG, $DB;
    
    
    $submission = $DB->get_record('assign_submission', ['id' => $row->submissionid]);
    if (!$submission) {
        return '';
    }
    $assignmentid = $submission->assignment;

    // Lấy course module id từ assignmentid
    $cm = get_coursemodule_from_instance('assign', $assignmentid);
    if (!$cm) {
        return '';
    }
    $context = context_module::instance($cm->id);

    $fs = get_file_storage();
    $files = $fs->get_area_files(
        $context->id,
        'assignsubmission_file',
        'checked_file',
        $row->submissionid,
        'timemodified',
        false
    );
    if (empty($files)) {
        return '';
    }
    $file = reset($files);
    $url = moodle_url::make_pluginfile_url(
        $file->get_contextid(),
        $file->get_component(),
        $file->get_filearea(),
        $file->get_itemid(),
        $file->get_filepath(),
        $file->get_filename()
    );
    $iconurl = $CFG->wwwroot . '/pix/f/pdf-24.png';
    $iconhtml = html_writer::empty_tag('img', [
        'src' => $iconurl,
        'alt' => 'PDF',
        'class' => 'icon',
        'style' => 'vertical-align: middle; margin-right: 5px;'
    ]);
    return $iconhtml . html_writer::link($url, $file->get_filename(), ['target' => '_blank']);
}
