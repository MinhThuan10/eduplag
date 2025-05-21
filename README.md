## 🛠 Manual Integration Guide

This plugin requires modifications to core Moodle files to fully integrate plagiarism checking features into the Assignment module.

---

### 📁 File: `mod/assign/gradingtable.php`

#### ✅ 1. Load Eduplag library

**After:**
```php
defined('MOODLE_INTERNAL') || die();  // (line 25)
```
**Add:**
```php
// Eduplag
require_once($CFG->dirroot . '/mod/assign/submission/eduplag/locallib.php');
```
✅ 2. Add Eduplag columns to grading table

**After:**
```php
$headers[] = $plugin->get_name();  // (line 500)
```
**Add:**
```php
// Eduplag
assignsubmission_eduplag_add_grading_columns($assignment, $columns, $headers);
```
✅ 3. Add column rendering functions

**After:**
```php
function col_userid(stdClass $row)  // (line 1438)
```
**Add:**
```php
// EduPlag
public function col_plagiarism($row) {
    return assignsubmission_eduplag_col_plagiarism($row);
}

public function col_urlfilechecked($row) {
    return assignsubmission_eduplag_col_urlfilechecked($row);
}

public function col_checkedfile($row) {
    return assignsubmission_eduplag_col_checkedfile($row);
}
```
###  📁 File: mod/assign/locallib.php

✅ 1. Load helper

**After:**
```php
require_once($CFG->libdir . '/portfolio/caller.php');  // (line 96)
```
**Add:**
```php
// Eduplag
require_once($CFG->dirroot . '/mod/assign/submission/eduplag/lib_helper.php');
```
✅ 2. add checkbox values

**Before:**
```php
return $returnid;  // (line 821)
```
**Add:**
```php
// Eduplag
$checkplagiarism = !empty($formdata->checkplagiarism) ? 1 : 0;
$viewstudent = !empty($formdata->viewstudent) ? 1 : 0;
eduplag_save_checkbox_values($returnid, $checkplagiarism, $viewstudent);
```
✅ 3. Update checkbox values

**Before:**
```php
return $result;  // (1588)
```
**Add:**
```php
// Eduplag
$checkplagiarism = !empty($formdata->checkplagiarism) ? 1 : 0;
$viewstudent = !empty($formdata->viewstudent) ? 1 : 0;
eduplag_save_checkbox_values($formdata->instance, $checkplagiarism, $viewstudent);
```
###  📁 File: mod/assign/mod_form.php

✅ 1. Load helper

**After:**
```php
require_once($CFG->dirroot . '/mod/assign/locallib.php');  // (line 29)
```
**Add:**
```php
// Eduplag
require_once($CFG->dirroot . '/mod/assign/submission/eduplag/lib_helper.php');
```

✅ 2. Add checkbox controls to form

**Before:**
```php
$assignment->add_all_plugin_settings($mform) // (line 127)
```
**Add:**
```php
// Eduplag
;
eduplag_add_checkboxes_to_form($mform);
```
✅ 3. Prepopulate checkbox values
**After:**
```php
function data_preprocessing  // (line 294)
```
**Add:**
```php
// Eduplag
if (!empty($defaultvalues['instance'])) {
    $eduplag = eduplag_get_checkbox_values($defaultvalues['instance']);
    $defaultvalues['checkplagiarism'] = $eduplag['checkplagiarism'];
    $defaultvalues['viewstudent'] = $eduplag['viewstudent'];
}
```
###  📁 File: mod/assign/classes/output/renderer.php
✅ 1. Render plagiarism info in submission status (compact view)

**Before:**
```php
return $o;  // (line 634)
```
**Add:**
```php
// Eduplag
$o .= assign_submission_eduplag::render_student_plagiarism_info($submission, $status->coursemoduleid, $this->page->context->id);
```
✅ 2. Render plagiarism info in submission full view

**Before:**
```php
$o .= $warningmsg;  // (line 868)
```
**Add:**
```php
// Eduplag
$o .= assign_submission_eduplag::render_student_plagiarism_info($submission, $status->coursemoduleid, $this->page->context->id);
```
