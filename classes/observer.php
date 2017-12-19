<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_nolockwhenpassed;

class observer {
    static public function user_graded(\core\event\user_graded $event) {
        global $DB;
        $grade = $event->get_grade();
        $grade->load_grade_item();
        if ($grade->grade_item->itemtype != 'mod' || $grade->grade_item->itemmodule != 'quiz') {
            return;
        }
        $quiz = $DB->get_record('quiz', array('id' => $grade->grade_item->iteminstance));
        $attempts = quiz_get_user_attempts($quiz->id, $grade->userid);

        // Calculate the best grade.
        $bestgrade = quiz_calculate_best_grade($quiz, $attempts);

        $gradefraction = $bestgrade / $quiz->sumgrades;

        self::log_to_file(compact('event', 'quiz', 'grade', 'attempts', 'bestgrade','gradefraction'));

        if ($gradefraction >= 0.8 && ($grade->is_locked() || $grade->is_overridden())){
            //Turn overridden and locked off.
            $grade->set_overridden(false);
            $grade->set_locked(0);
            self::log_to_file(compact('grade'));
            $grade->update('local_nolockwhenpassed');
            //regrade quiz for this user.
            quiz_update_grades($quiz, $grade->userid);
        }
    }

    static public function log_to_file($tolog) {
        if (!is_dir('/tmp/jamiesensei')) {
            mkdir('/tmp/jamiesensei', 0777, true);

        }
        if (!is_string($tolog)) {
            $tolog = print_r($tolog, true);
        }
        file_put_contents("/tmp/jamiesensei/debugger.log", $tolog."\n", FILE_APPEND);
    }
}
