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
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot . '/grade/querylib.php');
require_once($CFG->libdir . '/completionlib.php');

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
$csvwriter = new csv_export_writer();
$csvwriter->set_filename('StudentTracking');
$csvwriter->add_data([
    'First Name',
    'Last Name',
    'Modules Completed',
    'Progress',
    'End Date',
    'Final Grade',
]);
foreach ($userdata as $user) {
    $csvwriter->add_data([$user['firstname'] . ' ' . $user['lastname'],
                          $user['count'] . '/' . $user['activities'],
                          $user['progress'] . '%',
                          $user['enddate'],
                          $user['finalgrade'], ]);
}
$csvwriter->download_file();
