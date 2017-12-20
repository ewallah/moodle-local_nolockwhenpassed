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
    static public function attempt_submitted(\mod_quiz\event\attempt_submitted $event) {
        global $DB;
        $attempt = $event->get_record_snapshot('quiz_attempts', $event->objectid);
        $quiz    = $event->get_record_snapshot('quiz', $attempt->quiz);

        $gradefraction = $attempt->sumgrades / $quiz->sumgrades;

        self::log_to_file(compact('event', 'quiz', 'grade', 'attempts', 'bestgrade','gradefraction'));

        if ($gradefraction >= 0.8) { //Is the new grade 80% or better?
            //Any existing grades for this user?
            $grades = grade_get_grades($quiz->course, 'mod', 'quiz', $quiz->id, $attempt->userid);
            if (!empty($grades->items[0]->grades)) {
                $grade = new \grade_grade((array)$grades->items[0]->grades);
                if ($grade->is_locked() || $grade->is_overridden()){
                    //Turn overridden and locked off.
                    $grade->set_overridden(false);
                    $grade->set_locked(0);
                    self::log_to_file(compact('grade'));
                    $grade->update('local_nolockwhenpassed');
                }
            }
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
