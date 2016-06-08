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

$wanturl = optional_param('wanturl', '/', PARAM_LOCALURL);

require_login(null, false, null, $wanturl);

$enabled = get_config('local_completionnotification', 'enabled');
$startdate = get_config('local_completionnotification', 'startdate');

// TODO: replace the following line with if (!$CFG->enablecompletion || !enabled || !isloggedin() || is_siteadmin()) { .
if (!$CFG->enablecompletion || !$enabled || !isloggedin()) {
    redirect($CFG->wwwroot . clean_param($wanturl, PARAM_LOCALURL));
}

if (empty($startdate) || !is_int(intval($startdate))) {
    redirect($CFG->wwwroot . clean_param($wanturl, PARAM_LOCALURL));
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/completionnotification/complete.php');
$PAGE->set_title('Course Complete');
$PAGE->set_heading('Congratulations!');

global $DB;
$sql = 'SELECT 
            distinct(course) as courseid
        FROM
            {course_completions}
        WHERE
            userid = :userid AND timecompleted > :startdate;';
$params = array('userid' => $USER->id, 'startdate' => $startdate);

$newcompletions = $DB->get_records_sql($sql, $params);

if (empty($newcompletions)) {
    redirect($CFG->wwwroot . clean_param($wanturl, PARAM_LOCALURL));
}

error_log('$newcompletions: ' . print_r($newcompletions, true));

$PAGE->requires->jquery();
$PAGE->requires->js_call_amd('local_completionnotification/fireworks', 'init');

$output =  "<p>Congratulations! You have successfully completed:</p>";
$output.= html_writer::start_tag('ul', array('class' => 'courselist'));

foreach ($newcompletions as $completion) {
    $course = $DB->get_record('course', array('id' => $completion->courseid));
    $output.= html_writer::tag('li', $course->fullname, array('class' => 'coursename'));
}


$output.= html_writer::end_tag('ul');
$output.= html_writer::tag('a', 'Continue',
        array('class' => 'btn btn-primary',
              'href' => $CFG->wwwroot . clean_param($wanturl, PARAM_LOCALURL)));

echo $OUTPUT->header();
echo $output;
echo $OUTPUT->footer();

