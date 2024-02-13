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
namespace local_isyc_student_tracking\output;

use local_isyc_student_tracking\sqlqueries\sqlqueries;

class render {
    /**
     * Return the data of the users
     *
     * @param int $courseid The id of the course.
     * @param int $page The current page in the pagination.
     * @param int $length The number of records to show for page in the pagination.
     * @return array $user_data Data of the users.
     */
    public static function get_user_data($courseid, $page = null, $length = null) {
        $sql = new sqlqueries;
        $userdata = [];
        $offset = ($page - 1) * $length;
        // Filter to get the data of the users in a group if the filter is applied.
        // If exist the param orderby & order, the query is ordered by these params.
        if (isset($_GET['groupid']) && $_GET['groupid'] >= 1) {
            $groupid = $_GET['groupid'];
            $totalresults = count($sql->get_user_info($courseid, null, null, $groupid));
            if (isset($_GET['orderby']) && isset($_GET['order'])) {
                $orderby = $_GET['orderby'];
                $order = $_GET['order'];
                $userinfo = $sql->get_user_info($courseid, $offset, $length, $groupid, null, $orderby, $order);
            } else {
                $userinfo = $sql->get_user_info($courseid, $offset, $length, $groupid);
            }
            // Get the data of the user with specific fullname.
        } else if (isset($_GET['fullname']) && $_GET['fullname'] != '') {
            $fullname = $_GET['fullname'];
            $totalresults = count($sql->get_user_info($courseid, null, null, null, $fullname));
            if (isset($_GET['orderby']) && isset($_GET['order'])) {
                $orderby = $_GET['orderby'];
                $order = $_GET['order'];
                $userinfo = $sql->get_user_info($courseid, $offset, $length, null, $fullname, $orderby, $order);
            } else {
                $userinfo = $sql->get_user_info($courseid, $offset, $length, null, $fullname);
            }
            // Get data for for all users in the course.
        } else {
            $totalresults = count($sql->get_user_info($courseid));
            if (isset($_GET['orderby']) && isset($_GET['order'])) {
                $orderby = $_GET['orderby'];
                $order = $_GET['order'];
                $userinfo = $sql->get_user_info($courseid, $offset, $length, null, null, $orderby, $order);
            } else {
                $userinfo = $sql->get_user_info($courseid, $offset, $length);
            }
        }
        $completioninfo = new \completion_info(course_get_format($courseid)->get_course());
        $activitites = $completioninfo->get_activities();
        $activitiescount = count($activitites);
        $i = 0;
        foreach ($userinfo as $user) {
            $userid = $user['id'];
            $completedactivities = 0;
            foreach ($activitites as $activity) {
                $activitycompletion = $completioninfo->get_data($activity, true, $userid);
                if ($activitycompletion->completionstate != COMPLETION_INCOMPLETE) {
                    $completedactivities++;
                }
            }
            $completionparams = [
                'userid' => $userid,
                'course' => $courseid,
            ];
            // Get the date when the user finished the course.
            $ccompletion = new \completion_completion($completionparams);
            if ($ccompletion->timecompleted != null) {
                $datecompletion = userdate($ccompletion->timecompleted);
            } else {
                $datecompletion = 'Not yet';
            }
            // Get the finalgrade in the course.
            $coursegrade = grade_get_course_grade($userid, $courseid);
            $coursefinalgrade = round($coursegrade->grade, 2);
            $userdata[] = [
                'firstname' => $userinfo[$i]['firstname'],
                'lastname' => $userinfo[$i]['lastname'],
                'activities' => $activitiescount,
                'count' => $completedactivities,
                'progress' => $userinfo[$i]['progress'],
                'enddate' => $datecompletion,
                'finalgrade' => $coursefinalgrade,
                'totalresults' => $totalresults,
            ];
            $i++;
        }
        return $userdata;
    }
    /**
     * Return array with the pages in the pagination,
     * get group id value for add or not add the param groupid with the value currentgroupid.
     *
     * @param int $totalpages Number of pages int the pagination.
     * @param int $currentpage The parameter of the current page.
     * @param array $currentgroup The parameter of the filter group.
     * @return array $pages.
     */
    public static function get_pagination($totalpages, $currentpage, $currentgroupid) {
        $groupid = false;
        if ($currentgroupid > 0) {
            $groupid = true;
        }
        for ($i = 1; $i <= $totalpages; $i++) {
            $pages[] = [
                'page' => $i,
                'is_active' => ($i == $currentpage),
                'groupid' => $groupid,
                'currentgroupid' => $currentgroupid,
            ];
        }
        return $pages;
    }
    /**
     * Return the data of the groups of the current course.
     *
     * @param int $courseid Current course id.
     * @param int $currentgroupid Current group id.
     * @return array $groupsdata.
     */
    public static function get_groups($courseid, $currentgroupid) {
        $groups = groups_get_all_groups($courseid);
        // This if is for the course without groups.
        if (empty($groups)) {
            $groupsdata = false;
        } else {
            foreach ($groups as $group) {
                if ($group->id == $currentgroupid) {
                    $selected = true;
                } else {
                    $selected = false;
                }
                $groupsdata[] = [
                    'groupid' => $group->id,
                    'groupname' => $group->name,
                    'selected' => $selected,
                ];
            }
        }
        return $groupsdata;
    }
}
