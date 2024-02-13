<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_isyc_student_tracking
 * @category    string
 * @copyright   2023 Your Darío <dcester@isyc.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_isyc_student_tracking\output\render;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot . '/grade/querylib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot.'/user/filters/lib.php');

require_login();
$id = required_param('id', PARAM_INT);
// Which page in the pagination to show.
$page = optional_param('page', '1', PARAM_INT);
$PAGE->set_context(context_course::instance($id));
$url = new moodle_url('/local/isyc_student_tracking/index.php');
$PAGE->set_url($url);
$course = get_course($id);
require_login($course);
$PAGE->set_title('Student Tracking');
$PAGE->set_heading($course->fullname);
$indexcontent = new render;
// Number of records to show for page.
$elements = 5;
$userdata = $indexcontent->get_user_data($id, $page, $elements);
$currentpage = isset($_GET['page']) ? intval($_GET['page']) : 0;
$currentgroupid = isset($_GET['groupid']) ? intval($_GET['groupid']) : 0;
if (isset($_GET['fullname'])) {
    $fullname = ($_GET['fullname']);
} else {
    $fullname = '';
}
// Return group param or fullname param, to create in csv.php and excel.php to create the object shown in
// the files to be the same as the data in the report.
if ($currentgroupid > 0) {
    $exportcsv = new moodle_url('/local/isyc_student_tracking/export/csv.php', ['id' => $id, 'groupid' => $currentgroupid]);
    $csvurl = $exportcsv->out(false);
    $exportlinkexcel = new moodle_url('/local/isyc_student_tracking/export/excel.php', ['id' => $id, 'groupid' => $currentgroupid]);
    $excelurl = $exportlinkexcel->out(false);
} else if ($fullname) {
    $exportcsv = new moodle_url('/local/isyc_student_tracking/export/csv.php', ['id' => $id, 'fullname' => $fullname]);
    $csvurl = $exportcsv->out(false);
    $exportlinkexcel = new moodle_url('/local/isyc_student_tracking/export/excel.php', ['id' => $id, 'fullname' => $fullname]);
    $excelurl = $exportlinkexcel->out(false);
} else {
    $exportcsv = new moodle_url('/local/isyc_student_tracking/export/csv.php', ['id' => $id]);
    $csvurl = $exportcsv->out(false);
    $exportlinkexcel = new moodle_url('/local/isyc_student_tracking/export/excel.php', ['id' => $id]);
    $excelurl = $exportlinkexcel->out(false);
}
$orderstatus = (isset($_GET['order']) && $_GET['order'] == 'ASC') ? 'DESC' : 'ASC';
$nextpage = $currentpage + 1;
$lastpage = $currentpage - 1;
$groups = $indexcontent->get_groups($id, $currentgroupid);
// If the the user search is not found or the aren't users in the group,
// the pagination is not appear and $data has a message to the user.
if ($userdata == null) {
    $numbertofpages = 0;
    $pages = false;
    if ( isset($_GET['groupid'])) {
        $message = 'There are not users in the this group.';
    } else {
        $message = 'Null results for ' . '"' . $_GET['fullname'] . '".';
    }
    // Data to render if the results in the query is null.
    $data = [
        'users' => $userdata,
        'currentcourseid' => $id,
        'pages' => $pages,
        'nextpage' => $nextpage,
        'nextpagedesactived' => true,
        'lastpage' => $lastpage,
        'lastpagedesactived' => true,
        'groups' => $groups,
        'message' => $message,
        'url' => $url,
        'linkdesactivated' => true,
    ];
} else {
    $countuser = $userdata[0]['totalresults'];
    $usercount = count($userdata);
    $numbertofpages = ceil($countuser / $elements);
    $pages = $indexcontent->get_pagination($numbertofpages, $currentpage, $currentgroupid);
    // Data to render in the mustache if the the results in the query is not null.
    $data = [
        'users' => $userdata,
        'currentcourseid' => $id,
        'pages' => $pages,
        'nextpage' => $nextpage,
        'nextpagedesactived' => ($currentpage == $numbertofpages),
        'lastpage' => $lastpage,
        'lastpagedesactived' => ($currentpage == 1),
        'currentgroupid' => $currentgroupid,
        'groups' => $groups,
        'url' => $url,
        'csvlink' => $csvurl,
        'excellink' => $excelurl,
        'page' => $page,
        'elements' => $elements,
        'fullname' => $fullname,
        'orderby' => (isset($_GET['orderby']) ? $_GET['orderby'] : null),
        'order' => (isset($_GET['order']) ? $_GET['order'] : null),
        'orderdata' => $orderstatus,
        'iconascending' => (isset($_GET['order']) && $_GET['order'] == 'ASC'),
        'icondescending' => (isset($_GET['order']) && $_GET['order'] == 'DESC'),
        'firstname' => (isset($_GET['orderby']) && $_GET['orderby'] == 'firstname'),
        'lastname' => (isset($_GET['orderby']) && $_GET['orderby'] == 'lastname'),
        'progress' => (isset($_GET['orderby']) && $_GET['orderby'] == 'progress'),
    ];
}
$template = 'local_isyc_student_tracking/index';
echo $OUTPUT->header();
// Render the indexcontent template.
echo $OUTPUT->render_from_template($template, $data);
echo $OUTPUT->footer();
