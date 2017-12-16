<?php

$observers = array(

    array(
        'eventname'   => '\core\event\user_graded',
        'callback'    => '\local_nolockwhenpassed\observer::user_graded',
        'includefile' => '/mod/quiz/locallib.php'
    )
);
