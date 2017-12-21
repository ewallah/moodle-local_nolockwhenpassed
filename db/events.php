<?php

$observers = array(

    array(
        'eventname'   => '\mod_quiz\event\attempt_submitted',
        'callback'    => '\local_nolockwhenpassed\observer::attempt_submitted',
        'includefile' => '/local/nolockwhenpassed/eventincludes.php'
    )
);
