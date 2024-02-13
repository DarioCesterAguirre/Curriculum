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

function local_isyc_student_tracking_extend_navigation_course(navigation_node $parentnode,
        stdClass $course, context_course $context) {
    if (has_capability('local/isyc_student_tracking:canview', $context)) {
        $url = new moodle_url('/local/isyc_student_tracking/index.php', ['id' => $course->id, 'page' => 1]);
        $parentnode->add(get_string('pluginname', 'local_isyc_student_tracking'), $url, navigation_node::TYPE_SETTING,
        null, null, null);
    }
}
