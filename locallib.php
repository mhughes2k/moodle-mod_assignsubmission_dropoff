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
// MERCHANTABILITY or FITNESS FA PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * This file contains the version information for Mahara submission plugin
 *
 * @package    assignsubmission_dropoff
 * @copyright  2015 Michael Hughes / University of Strathclyde
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class assign_submission_dropoff extends assign_submission_plugin {
	public function get_name() {
        return get_string('dropoff', 'assignsubmission_dropoff');
	}
    
    public function get_settings(MoodleQuickForm $mform){
        // TODO implement drop off to a specific drop box
        $mform->addElement('textarea', 'assignsubmission_dropoff_instructions', get_string('instructions', 'assignsubmission_dropoff'));
        $mform->addHelpButton('assignsubmission_dropoff_instructions', 'instructions', 'assignsubmission_dropoff');
        $mform->setDefault('assignsubmission_dropoff_instructions', $this->get_config('dropoff_instructions'));
    }
    
    public function save_settings(stdClass $data) {
        //var_dump($data->assignsubmission_dropoff_instructions);
        $this->set_config('dropoff_instructions', $data->assignsubmission_dropoff_instructions);//['text']);
        return true;
    }
    
    public function save(stdClass $submission, stdClass $data) {
        var_dump($submission);
        var_dump($data);
        //die('saving');
        
        if (!empty($data->dropoff_recordsubmission)) {
            $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
            $submission->timemodified = $data->dropoff_timesubmitted;
            //$result= $DB->update_record('assign_submission', $submission);
            //$this->lock_submission($submission->userid);
            debugging('recording submission');
        } else {
            debugging('not recording submission');
        }
        return true;
    }
    
    /**
     * Display information to markers and students
     */
    public function view_summary(stdClass $submission, & $showviewlink) {
        var_dump($submission);
        
        $out = '';
        
        $instructions = $this->get_config('dropoff_instructions');
        
        $out = $instructions;
        $showviewlink = false;
        return $out;
    }
    
    /**
     * Display the submission to students and markers
     */
    public function view(stdClass $submissionorgrade) {
        var_dump($submissionorgrade);
        return "view";   
    }
    
    public function is_empty(stdClass $submission) {
        global $DB;
        var_dump($submission);
        return false;
        

/*
        return $DB->count_records('assignsubmission_dropoff', array(
            'assignment' => $submission->id,
            )
        );
*/
    }
    
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        global $OUTPUT, $USER;
        //var_dump($submission);
        //var_dump($this->assignment);
        $assignctxt = $this->assignment->get_context();
        //$isStaff = has_capability('$capability''', $assignctxt)
        $mform->addElement('header', 'dropoff', 'Drop off');
        if ($submission->userid != $USER->id && $this->assignment->can_edit_submission($submission->userid, $USER->id)) {
            $url = new moodle_url('/mod/assign/submission/dropoff/process.php', 
                array(
                    'id' => $this->assignment->get_instance()->id,
                    'sid' => $submission->id, 
                    'sesskey' => sesskey()
                )
            );
            $links = $OUTPUT->action_link($url, 'Record Submission');
            //$mform->addElement('static', 'recordsubmission', 'Record Submission', $links);
            $mform->addElement('selectyesno','dropoff_recordsubmission', 'Record Submission');
            $mform->addElement('static', 'recordsubmissioninfo', '', "This will lock the student submission.");
            $mform->addElement('date_time_selector','dropoff_timesubmitted', 'Date & Time Submitted');
        } 
        if ($submission->userid == $USER->id) {
            
            
            $url = new moodle_url('/mod/assign/submission/dropoff/coverpage.php', 
                array(
                    'id' => $this->assignment->get_instance()->id,
                    'uid' => $USER->id, 
                    'sesskey' => sesskey()
                )
            );
            $idents = $OUTPUT->action_link($url, 'Cover Page');
            $mform->addElement('static', 'dropoffidentpage', 'Get Identifier', $idents);
        }   
    }
}