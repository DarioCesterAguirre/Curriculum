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

namespace local_isyc_student_tracking\sqlqueries;

class sqlqueries {
    /**
     * Return the records
     *
     * @param int $courseid The course ID.
     * @param int $start Optional row number from start to return results.
     * @param int $count Optional number of rows to get.
     * @param int $groupid Optional ID of the group.
     * @param string $fullname Optional fullname of the user searched.
     * @param string $orderby Optional name of column to order the query.
     * @param string $order Optional type to order the query, ascendent or desdecent.
     * @return array $dataArray
     */
    public static function get_user_info($courseid, $start = null, $count = null,
                                        $groupid = null, $fullname = null, $orderby = null, $order = null) {
        global $DB;
        $groupfilter = '';
        $namefilter = '';
        $liketerm = '%' . $DB->sql_like_escape($fullname) . '%';
        $orderbyfilter = '';
        $validorderfields = ['firstname', 'lastname', 'progress'];
        $validorderdirections = ['ASC', 'DESC'];
        if ($groupid !== null && $groupid > 0) {
            $groupid = (int) $groupid;
            $groupfilter = ' INNER JOIN mdl_groups_members gm ON u.id = gm.userid AND gm.groupid = :groupid ';
        }
        if ($fullname !== null) {
            $namefilter = ' WHERE CONCAT(u.firstname, " ", u.lastname) LIKE :name
                            OR CONCAT(u.firstname, u.lastname) LIKE :name1
                            OR u.firstname LIKE :name2 OR u.lastname LIKE :name3 ';
        }
        if ($orderby !== null && $order !== null && in_array($orderby, $validorderfields)
            && in_array(strtoupper($order), $validorderdirections)) {
            $orderbyfilter = ' ORDER BY ' . $orderby . ' ' . $order;
            if ($orderby == 'firstname') {
                $orderbyfilter = ' ORDER BY ' . $orderby . ' ' . $order . ', lastname ' . $order;
            }
        }
        $sql = 'SELECT u.id, u.firstname, u.lastname,
                       (COALESCE(SUM(CASE WHEN cmc.completionstate > 0 THEN 1 ELSE 0 END), 0) /
                       (SELECT COUNT(*) FROM {course_modules} WHERE course = :courseid) * 100) AS progress
                  FROM {user} u
                  JOIN {user_enrolments} ue ON u.id = ue.userid
                  JOIN {enrol} e ON ue.enrolid = e.id AND e.courseid = :courseid1
             LEFT JOIN {course_modules_completion} cmc ON u.id = cmc.userid
                   AND cmc.coursemoduleid IN (SELECT id FROM mdl_course_modules WHERE course = :courseid2)
             LEFT JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id AND cm.course = :courseid3' .
                   $groupfilter .
                   $namefilter .
                   'GROUP BY u.id, u.firstname, u.lastname' .
                   $orderbyfilter;
        $params = [
            'courseid' => $courseid,
            'courseid1' => $courseid,
            'courseid2' => $courseid,
            'courseid3' => $courseid,
            'groupid' => $groupid,
            'name' => $liketerm,
            'name1' => $liketerm,
            'name2' => $liketerm,
            'name3' => $liketerm,
        ];
        $records = $DB->get_records_sql($sql, $params, $start, $count);
        $userinfo = [];
        foreach ($records as $record) {
            $userinfo[] = [
                'id' => $record->id,
                'firstname' => $record->firstname,
                'lastname' => $record->lastname,
                'progress' => round($record->progress, 2),
            ];
        }
        return $userinfo;
    }
}
