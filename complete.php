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
require_once($CFG->dirroot.'/blocks/course_overview/locallib.php');

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
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_url('/local/completionnotification/complete.php');
$PAGE->set_title('Course Complete');
$PAGE->set_heading('Congratulations!');

global $DB;
$sql = 'SELECT 
            *
        FROM
            {course_completions} cc
                JOIN
            {course} c ON cc.course = c.id
        WHERE
            userid = :userid AND timecompleted > :startdate
        GROUP BY
            cc.course;';
$params = array('userid' => $USER->id, 'startdate' => $startdate);

$newcompletions = $DB->get_records_sql($sql, $params);

if (empty($newcompletions)) {
    redirect($CFG->wwwroot . clean_param($wanturl, PARAM_LOCALURL));
}

error_log('$newcompletions: ' . print_r($newcompletions, true));

$PAGE->requires->jquery();
$PAGE->requires->js_call_amd('local_completionnotification/fireworks', 'start');

$output = html_writer::tag('h2', 'You have successfully completed:');
$output.= html_writer::start_tag('ul', array('class' => 'courselist'));

$renderer = $PAGE->get_renderer('block_course_overview');
$output.= $renderer->course_overview($newcompletions, array());

// Print link to continue to the wanted link.
$output.= $OUTPUT->container_start('buttons');
$url = new moodle_url(clean_param($wanturl, PARAM_LOCALURL));
$output.= $OUTPUT->single_button($url, 'Continue', 'get');
$output.= $OUTPUT->container_end('buttons');

echo $OUTPUT->header();
echo $output;
echo $OUTPUT->footer();

