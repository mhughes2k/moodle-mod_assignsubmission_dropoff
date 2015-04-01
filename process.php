<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Handles the recording by a member of staff the fact a student has submitted
 */

require_once("../../../../config.php");
require_once($CFG->dirroot . '/mod/assign/locallib.php');

$hash = required_param('hash', PARAM_ALPHANUM);

if(! $exists = $DB->get_record('assignsubmission_dropoff', array('hash' => $hash))) {
    print_error('Invalid hash');
    exit();
}


$id = $exists->assignment;
$uid = $exists->userid;
//$id = required_param('id', PARAM_INT);
//$uid = optional_param('uid', -1, PARAM_INT);

$urlparams = array('id' => $id,
                  'action' => optional_param('action', '', PARAM_TEXT),
                  'rownum' => optional_param('rownum', 0, PARAM_INT),
                  'useridlistid' => optional_param('action', 0, PARAM_INT));

$url = new moodle_url('/mod/assign/view.php', $urlparams);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'assign');

require_login($course, true, $cm);
$PAGE->set_url($url);

$context = context_module::instance($cm->id);

require_capability('mod/assign:view', $context);

$assign = new assign($context, $cm, $course);

$targetuser = $DB->get_record('user', array('id' => $uid));

redirect(new moodle_url('/mod/assign/view.php', array('id' => $assign->get_instance()->id, 
    'action' => 'editsubmission',
    'userid' => $uid
    )
));

if (false) {
    $submission = $assign->get_user_submission($targetuser->id, true);
    
    $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
    $submission->timemodified = time();
    $result= $DB->update_record('assign_submission', $submission);
    $assign->lock_submission($submission->userid);
    
    echo 'Submission recorded';
}