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


require_once("../../../../config.php");
require_once($CFG->dirroot . '/mod/assign/locallib.php');

$id = required_param('id', PARAM_INT);
$sid = optional_param('sid', -1, PARAM_INT);

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

$submission = $assign->get_user_submission($USER->id, false);

$hash = md5($submission->assignment.$submission->userid);

//check hash
if($exists = $DB->get_record('assignsubmission_dropoff', array('hash' => $hash))) {
    //hash already issued..
} else {
    //record hash to associate it with the assignment
    $newhashrecord = new stdClass();
    $newhashrecord->assignment = $assign->get_instance()->id;
    $newhashrecord->userid = $submission->userid;
    $newhashrecord->hash = $hash;
    $DB->insert_record('assignsubmission_dropoff', $newhashrecord);
}



$url = new moodle_url('/mod/assign/submission/dropoff/process.php',
    array(
        'hash' => $hash
    )
);
$PAGE->set_pagelayout("embedded");
echo $OUTPUT->header();
echo $OUTPUT->container_start();
echo $OUTPUT->heading('Assignment Submission Cover Page');
echo html_writer::img(new moodle_url('/mod/assign/submission/dropoff/qrcode.php', array('hash' => $hash )), "QRCode");
echo $OUTPUT->action_link($url->out(), $url->out());
echo $OUTPUT->container_end_all();
echo $OUTPUT->footer();


