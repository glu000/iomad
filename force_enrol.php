<?php


// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX START
require_once("$CFG->dirroot/enrol/license/locallib.php");
require_once("$CFG->dirroot/group/lib.php");

// Enrol the user in the course.

$enrol = enrol_get_plugin('license');
$timestart = time();

if (empty($licenserecord ['type'])) {
    // Set the timeend to be time start + the valid length for the license in days.
    $timeend = $timestart + ($licenserecord ['validlength'] * 24 * 60 * 60 );
} else {
    // Set the timeend to be when the license runs out.
    $timeend = $licenserecord ['expirydate'];
}

$enrolinstances = enrol_get_instances($licensecourse, true);
foreach($enrolinstances as $instance) {
    if ($instance->enrol=='license') break;
}

$enrol->enrol_user($instance, $userdata->id, $instance->roleid, $timestart, $timeend);

// Get the userlicense record.
$userlicense = $DB->get_record('companylicense_users', array('id' => $licenserecord['id']));

// Add the user to the appropriate course group.
if (!$DB->get_record('course', array('id' => $licensecourse, 'groupmode' => 0))) {
    company::add_user_to_shared_course($licensecourse, $userdata->id, $licenserecord['companyid'], $userlicense->groupid);
}

// Update the userlicense record to mark it as in use.
$DB->set_field('companylicense_users', 'isusing', 1, array('id' => $userlicense->id));

// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX END
