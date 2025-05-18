<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) { // Chỉ admin mới có quyền cấu hình

    $settings = new admin_settingpage('assignsubmission_eduplag', get_string('pluginname', 'assignsubmission_eduplag'));

    $settings->add(new admin_setting_configtext(
        'assignsubmission_eduplag/school_key',
        get_string('schoolkey', 'assignsubmission_eduplag'),
        get_string('schoolkey_desc', 'assignsubmission_eduplag'),
        '',
        PARAM_RAW_TRIMMED
    ));

    $ADMIN->add('assignsubmissionplugins', $settings);
}
