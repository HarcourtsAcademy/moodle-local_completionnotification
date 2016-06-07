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
 * Completion notification functions.
 *
 * @package    local_completionnotification
 * @copyright  2016 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$enabled = get_config('local_completionnotification', 'enabled');
$startdate = get_config('local_completionnotification', 'startdate');

// TODO: replace the following line with if (!$CFG->enablecompletion || !enabled || !isloggedin() || is_siteadmin()) { .
if (!$CFG->enablecompletion || !$enabled || !isloggedin()) {
    return;
}

global $PAGE;
// Skip if displaying the completion notification page.
if ($PAGE->url->out_as_local_url() == '/local/completionnotification/complete.php') {
    return;
}

// TODO: Skip if showing a Moodle admin page.

error_log('$startdate: ' . print_r($startdate, true) . is_int($startdate));

if (empty($startdate) || !is_int(intval($startdate))) {
    return;
}

// Regular Completion cron, capturing the output in an output buffer for deletion.
require_once($CFG->dirroot.'/completion/cron.php');
ob_start();
completion_cron_criteria();
completion_cron_completions();
ob_end_clean();

global $DB, $USER;

$sql = 'SELECT 
            count(id) as count
        FROM
            {course_completions}
        WHERE
            userid = :userid AND timecompleted > :startdate;';
$params = array('userid' => $USER->id, 'startdate' => $startdate);

$newcompletions = $DB->get_record_sql($sql, $params);

error_log('$newcompletions: ' . print_r($newcompletions, true));

if ($newcompletions->count > 0) {
    $url = new moodle_url('/local/completionnotification/complete.php', array('wanturl' => $PAGE->url->out_as_local_url()));
//    redirect($url);
}
