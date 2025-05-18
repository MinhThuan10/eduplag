<?php
namespace assignsubmission_eduplag;
use mod_assign\event\submission_deleted;

defined('MOODLE_INTERNAL') || die();

class eduplag {
    public static function user_created(\core\event\user_created $event) {
        global $DB, $SITE;

        $userid = $event->objectid;
        $user = $DB->get_record('user', ['id' => $userid]);

        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');

        $fullname = $SITE->fullname;

        if (!$user) {
            return;
        }

        error_log("EDUPlag user_created event triggered for user: " . $user->id);

        $data = [
            'school_name' => $fullname,
            'email'    => $user->email,
            'firstname'=> $user->firstname,
            'lastname' => $user->lastname,
            'school_key' => $schoolkey
        ];
        print_r($_POST);
        
        $ch = curl_init('http://localhost:5000/mod/api/add_user');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
        
    }


    // update user
    public static function user_updated(\core\event\user_updated $event) {
        global $DB, $SITE;

        $userid = $event->objectid;
        $user = $DB->get_record('user', ['id' => $userid]);

        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');

        $fullname = $SITE->fullname;

        if (!$user) {
            return;
        }

        error_log("EDUPlag user_created event triggered for user: " . $user->id);

        $data = json_encode([
            'school_name' => $fullname,
            'email'    => $user->email,
            'firstname'=> $user->firstname,
            'lastname' => $user->lastname,
            'school_key' => $schoolkey
        ]);
        
        $ch = curl_init('http://localhost:5000/mod/api/update_user');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // dùng PUT
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
        
    }

    public static function user_deleted(\core\event\user_deleted $event) {
        global $SITE;

        $data = $event->get_data(); // lấy toàn bộ thông tin event
        $userid = $event->objectid;
        $fullname = $SITE->fullname;
        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');

        $email = $data['other']['email'] ?? null;

        if (!$email) {
            error_log("Không lấy được email từ event user_deleted");
            return;
        }

        $payload = json_encode([
            'school_name' => $fullname,
            'email'       => $email,
            'school_key'  => $schoolkey
        ]);

        $ch = curl_init('http://localhost:5000/mod/api/delete_user');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);

        error_log("DELETE user API response: " . $response);
        
    }

    public static function course_created(\core\event\course_created $event) {
        global $DB, $SITE;
        $data = $event->get_data();
        $courseid = $data['objectid'];
        $course = $DB->get_record('course', ['id' => $courseid]);

        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');

        $fullname = $SITE->fullname;

        if (!$course) {
            return;
        }

        $timecreated = $course->timecreated;
        $enddate = $course->enddate;
        $created_str = date('m/d/Y', $timecreated);
        $end_str = $enddate ? date('m/d/Y', $enddate) : 'Không có';

        $data = [
            'school_name' => $fullname,
            'class_id' => $courseid,
            'class_name'    => $course->fullname,
            'start_day'=> $created_str,
            'end_day' => $end_str,
            'school_key' => $schoolkey
        ];
        print_r($_POST);
        
        $ch = curl_init('http://localhost:5000/mod/api/create_class');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
        
    }

    public static function course_updated(\core\event\course_updated $event) {
        global $DB, $SITE;

        $data = $event->get_data();
        $courseid = $data['objectid'];
        $course = $DB->get_record('course', ['id' => $courseid]);

        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');

        $fullname = $SITE->fullname;

        if (!$course) {
            return;
        }

        $enddate = $course->enddate;
        $end_str = $enddate ? date('m/d/Y', $enddate) : 'Không có';

        $data = [
            'school_name' => $fullname,
            'class_id' => $courseid,
            'class_name'    => $course->fullname,
            'end_day' => $end_str,
            'school_key' => $schoolkey
        ];
        
        $ch = curl_init('http://localhost:5000/mod/api/update_class');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // dùng PUT
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
        
    }

    public static function course_deleted(\core\event\course_deleted $event) {
        global $SITE, $DB;


        $data = $event->get_data();
        $courseid = $data['objectid'];
        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');
        $fullname = $SITE->fullname;

        $payload = json_encode([
            'school_name' => $fullname,
            'class_id'       => $courseid,
            'school_key'  => $schoolkey
        ]);

        $ch = curl_init('http://localhost:5000/mod/api/delete_class');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }

    // asignment
    public static function assignment_created(\core\event\course_module_created $event) {
        global $DB, $SITE;

        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');

        $fullname = $SITE->fullname;

        if ($event->other['modulename'] !== 'assign') {
            return true;
        }

        // Lấy course module instance ID
        $cmid = $event->contextinstanceid;
        $cm = get_coursemodule_from_id('assign', $cmid);
        if (!$cm) {
            return false;
        }

        // Lấy thông tin assignment
        $assignment = $DB->get_record('assign', ['id' => $cm->instance]);
        if (!$assignment) {
            return false;
        }

        $data = [
            'school_name' => $fullname,
            'school_key' => $schoolkey,
            'class_id' => $assignment->course,
            'assignment_id' => $assignment->id,
            'assignmentName'    => $assignment->name,
            'startDay'=> date('m/d/Y', $assignment->allowsubmissionsfromdate),
            'dueDay' => date('m/d/Y', $assignment->duedate)
        ];
        print_r($_POST);
        
        $ch = curl_init('http://localhost:5000/mod/api/create_assignment');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
        
    }

    public static function assignment_updated(\core\event\course_module_updated $event) {
        global $DB, $SITE;

        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');

        $fullname = $SITE->fullname;

        if ($event->other['modulename'] !== 'assign') {
            return true;
        }

        // Lấy course module instance ID
        $cmid = $event->contextinstanceid;
        $cm = get_coursemodule_from_id('assign', $cmid);
        if (!$cm) {
            return false;
        }

        // Lấy thông tin assignment
        $assignment = $DB->get_record('assign', ['id' => $cm->instance]);
        if (!$assignment) {
            return false;
        }

        $data = [
            'school_name' => $fullname,
            'school_key' => $schoolkey,
            'class_id' => $assignment->course,
            'assignment_id' => $assignment->id,
            'assignmentName'    => $assignment->name,
            'startDay'=> date('m/d/Y', $assignment->allowsubmissionsfromdate),
            'dueDay' => date('m/d/Y', $assignment->duedate)
        ];
        
        $ch = curl_init('http://localhost:5000/mod/api/update_assignment');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // dùng PUT
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
        
    }


    public static function on_module_updated(\core\event\course_module_updated $event) {
        global $DB;

        $cmid = $event->contextinstanceid;

        // Lấy bản ghi course_modules
        $cm = $DB->get_record('course_modules', ['id' => $cmid], '*', IGNORE_MISSING);

        if ($cm && $cm->module == self::get_assign_moduleid() && $cm->deletioninprogress) {
            // Gọi hàm xử lý bạn muốn khi người dùng ấn "xóa"
            debugging("Assignment {$cm->instance} is marked for deletion.");
            self::assignment_deleted($cm);
        }
    }

    private static function get_assign_moduleid() {
        global $DB;
        static $assignid = null;
        if ($assignid === null) {
            $assignid = $DB->get_field('modules', 'id', ['name' => 'assign']);
        }
        return $assignid;
    }

    public static function assignment_deleted(\core\event\course_module_deleted $event) {

        global $SITE;
        if ($event->other['modulename'] !== 'assign') {
            return true;
        }
        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');
        $fullname = $SITE->fullname;

        $payload = json_encode([
            'school_name' => $fullname,
            'assignment_id' => $event->other['instanceid'],
            'school_key'  => $schoolkey
        ]);

        $ch = curl_init('http://localhost:5000/mod/api/delete_assignment');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);

    }

    public static function user_assigned_role(\core\event\role_assigned $event) {
        global $DB, $SITE;
    
        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');
        $fullname = $SITE->fullname;
    
        $userid = $event->relateduserid;
        $roleid = $event->objectid;

        error_log("RoleID là: $roleid");

        $contextid = $event->contextid;
    
        $role = $DB->get_record('role', ['id' => $roleid], 'shortname');
    
        $context = \context::instance_by_id($contextid);
        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }
    
        $courseid = $context->instanceid;
    
        $user = $DB->get_record('user', ['id' => $userid], 'email');
    
        $data = [
            'school_name' => $fullname,
            'school_key'  => $schoolkey,
            'email'       => $user->email,
            'role'        => self::convert_role_shortname($role->shortname), // sẽ convert thành Teacher / Student
            'class_id'    => $courseid,
        ];
    

        $ch = curl_init('http://localhost:5000/mod/api/add_user_to_class');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
    }
    
    private static function convert_role_shortname($shortname) {
        switch ($shortname) {
            case 'student':
                return 'Student';
            case 'manager':
                return 'Teacher';
            case 'teacher':
            case 'editingteacher':
                return 'Teacher';
            default:
                return 'Student';
        }
    }


    public static function user_unenrolled(\core\event\user_enrolment_deleted $event) {
        global $DB, $SITE;

        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');
        $fullname = $SITE->fullname;

        $userid   = $event->relateduserid;
        $courseid = $event->courseid;
    
        $user = $DB->get_record('user', ['id' => $userid], 'id, email');
    
        $data = [
            'school_name' => $fullname,
            'school_key'  => $schoolkey,
            'email'       => $user->email,
            'class_id'    => $courseid,
        ];

        $ch = curl_init('http://localhost:5000/mod/api/detele_user_from_class');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
    }
    


    public static function handle_file_uploaded(\assignsubmission_file\event\assessable_uploaded $event) {
        global $DB, $SITE, $USER, $CFG;

        $context = $event->get_context();
        $submissionid = $event->objectid;

        $cm = get_coursemodule_from_id('assign', $context->instanceid);
        if (!$cm) {
            debugging('Không lấy được course module.', DEBUG_DEVELOPER);
            return;
        }
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        $assignment = new \assign($context, $cm, $cm->course);

        $submission = $DB->get_record('assign_submission', ['id' => $submissionid]);
        if (!$submission) {
            debugging('Không tìm thấy submission.', DEBUG_DEVELOPER);
            return;
        }

        $schoolkey = get_config('assignsubmission_eduplag', 'school_key');
        $fullname = $SITE->fullname;
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $context->id,                    
            'assignsubmission_file',     
            ASSIGNSUBMISSION_FILE_FILEAREA, 
            $submissionid,                  
            'id',                         
            false                         
        );

        if (!empty($files)) {
            foreach ($files as $file) {
                $filename = $file->get_filename();
                if ($filename === '.') {
                    continue;
                }

                //  Đọc nội dung file
                $tempfile = tempnam(sys_get_temp_dir(), 'mdl_');
                $file->copy_content_to($tempfile);
                $filepath = $tempfile;


                $post_data = [
                    'school_key'       => $schoolkey,
                    'school_name'      => $fullname,
                    'class_id'         => $assignment->get_course()->id,
                    'assignment_id'    => $assignment->get_instance()->id,
                    'email'            => $USER->email,
                    'submission_id'    => $submissionid,
                    'submissionTitle'  => $filename,
                    'submitDay'        => date("m/d/Y", $submission->timemodified),
                    'file'             => new \CURLFile($filepath, $file->get_mimetype(), $filename),
                    'callback_url'     => $CFG->wwwroot . '/mod/assign/submission/eduplag/call_api_eduplag.php'
                ];


                $ch = curl_init('http://localhost:5000/api/check_plagiarism_moodle');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                curl_setopt($ch, CURLOPT_VERBOSE, true);  

                $response = curl_exec($ch);

                if (curl_errno($ch)) {
                    error_log('cURL error: ' . curl_error($ch));
                } else {
                    error_log('Response: ' . $response);
                }

                curl_close($ch);

                if (!$response) {
                    error_log('No response from API!');
                }
                break;
            }
        }

    }

}
