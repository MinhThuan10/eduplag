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

/**
 * Add event handlers for the assign
 *
 * @package    assignsubmission_eduplag
 * @category   event
 * @copyright  2016 Ilya Tregubov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$observers = array(
    [
        'eventname'   => '\core\event\user_created',
        'callback'    => '\assignsubmission_eduplag\eduplag::user_created',
        'priority'    => 9999,
        'internal'    => false,
    ],

    [
        'eventname'   => '\core\event\user_updated',
        'callback'    => '\assignsubmission_eduplag\eduplag::user_updated',
        'priority'    => 9999,
        'internal'    => false,
    ],

    [
        'eventname'   => '\core\event\user_deleted',
        'callback'    => '\assignsubmission_eduplag\eduplag::user_deleted',
        'priority'    => 9999,
        'internal'    => false,
    ],

    // Class
    [
        'eventname'   => '\core\event\course_created',
        'callback'    => '\assignsubmission_eduplag\eduplag::course_created',
        'priority'    => 9999,
        'internal'    => false,
    ],

    [
        'eventname'   => '\core\event\course_updated',
        'callback'    => '\assignsubmission_eduplag\eduplag::course_updated',
        'priority'    => 9999,
        'internal'    => false,
    ],

    [
        'eventname'   => '\core\event\course_deleted',
        'callback'    => '\assignsubmission_eduplag\eduplag::course_deleted',
        'priority'    => 9999,
        'internal'    => false,
    ],

    // assignment
    [
        'eventname'   => '\core\event\course_module_created',
        'callback'    => '\assignsubmission_eduplag\eduplag::assignment_created',
        'priority'    => 9999,
        'internal'    => false,
    ],

    [
        'eventname'   => '\core\event\course_module_updated',
        'callback'    => '\assignsubmission_eduplag\eduplag::assignment_updated',
        'priority'    => 9999,
        'internal'    => false,
    ],

    // [
    //     'eventname'   => '\core\event\course_module_deleted',
    //     'callback'    => '\assignsubmission_eduplag\eduplag::assignment_deleted',
    //     'priority'    => 9999,
    //     'internal'    => false,
    // ],

    // add user to course
    [
        'eventname'   => '\core\event\role_assigned',
        'callback'    => '\assignsubmission_eduplag\eduplag::user_assigned_role',
        'priority'    => 9999,
        'internal'    => false,
    ],
    
    [
        'eventname'   => '\core\event\user_enrolment_deleted',
        'callback'    => '\assignsubmission_eduplag\eduplag::user_unenrolled',
        'priority'    => 9999,
        'internal'    => false,
    ],

    [
        'eventname'   => '\assignsubmission_file\event\assessable_uploaded',
        'callback'    => '\assignsubmission_eduplag\eduplag::handle_file_uploaded',
        'internal'    => false,
        'priority'    => 9999,
    ],

);

