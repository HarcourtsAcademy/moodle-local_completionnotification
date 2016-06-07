<?php
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
 * Notifies students of course completion
 *
 * @package    local_completionnotification
 * @copyright  2016 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$wanturl = required_param('wanturl',PARAM_LOCALURL);

$enabled = get_config('local_completionnotification', 'enabled');
$startdate = get_config('local_completionnotification', 'startdate');

// TODO: replace the following line with if (!$CFG->enablecompletion || !enabled || !isloggedin() || is_siteadmin()) { .
if (!$CFG->enablecompletion || !$enabled || !isloggedin()) {
    redirect($CFG->wwwroot.'/');
}

if (empty($startdate) || !is_int(intval($startdate))) {
    redirect($CFG->wwwroot.'/');
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/completionnotification/complete.php');
$PAGE->set_title('Course Complete');
$PAGE->set_heading('Congratulations!');

global $DB;
$sql = 'SELECT 
            count(id) as count
        FROM
            {course_completions}
        WHERE
            userid = :userid AND timecompleted > :startdate;';
$params = array('userid' => $USER->id, 'startdate' => $startdate);

$newcompletions = $DB->get_record_sql($sql, $params);

$output =  "<p>Congratulations! You have successfully completed:</p>";
$output.= html_writer::start_tag('ul');
$output.= html_writer::end_tag('ul');
$output.= html_writer::tag('a', 'Continue',
        array('class' => 'btn btn-primary',
              'href' => $CFG->wwwroot . clean_param($wanturl, PARAM_LOCALURL)));

echo $OUTPUT->header();
echo $output;
echo $OUTPUT->footer();
