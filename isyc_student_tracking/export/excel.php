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

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot . '/grade/querylib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/excellib.class.php');

require_login();
$id = required_param('id', PARAM_INT);
$course = get_course($id);
require_login($course);
$users = new render;
if ( isset($_GET['page'])) {
    $page = $_GET['page'];
    $elements = $_GET['elements'];
    $userdata = $users->get_user_data($id, $page, $elements);
} else {
    $userdata = $users->get_user_data($id);
}
$filename = "StudentTracking.xlsx";
$workbook = new MoodleExcelWorkbook($filename);
$worksheet = $workbook->add_worksheet();
$headers = ['Firstname', 'Lastname', 'Activities Completed', 'Progress%', 'End Date', 'Final Grade'];
$col = 0;
foreach ($headers as $header) {
    $worksheet->write_string(0, $col++, $header);
}
$row = 1;
foreach ($userdata as $user) {
    $col = 0;
    $worksheet->write_string($row, $col++, $user['firstname']);
    $worksheet->write_string($row, $col++, $user['lastname']);
    $worksheet->write_string($row, $col++, $user['count'] . '/' . $user['activities']);
    $worksheet->write_number($row, $col++, $user['progress']);
    $worksheet->write_string($row, $col++, $user['enddate']);
    $worksheet->write_number($row, $col++, $user['finalgrade']);
    $row++;
}
$workbook->close();
