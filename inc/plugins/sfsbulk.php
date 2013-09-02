<?php
/**
 * This file is part of StopForumSpam.com Bulk User Checker plugin for MyBB.
 * Copyright (C) 2013 Muhammad Anas <anastts.pk@gmail.com>
 * Author's website: http://anas.pk
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function sfsbulk_info() {
    return array(
        'name' => 'StopForumSpam - Bulk Spammers Detection',
        'description' => 'Bulk check your complete, existing user base for known spammers by comparing against StopForumSpam.com database',
        'website' => 'http://anas.pk',
        'author' => 'Muhammad Anas',
        'authorsite' => 'http://anas.pk',
        'version' => '0.0.1',
    );
}

function sfsbulk_install() {
    global $db;
    
    $sfsbulk_settings_group = array(
        'name' => 'sfsbulkcheck',
        'title' => 'SFS Bulk Check Settings',
        'description' => 'Settings for StopForumSpam bulk user checking plugin.',
        'disporder' => '1',
        'isdefault' => 'no');
    $db->insert_query('settinggroups', $sfsbulk_settings_group);
    $gid = $db->insert_id();
    
    $sfsbulk_setting = array(
        'name' => 'sfsbulk_max_posts',
        'title' => 'Maximum posts for users to check',
        'description' => 'Only users with this much or less post count will be checked. Leave to default (0) to check only those users who have not made any post. Enter -1 to check all users.',
        'optionscode' => 'text',
        'value' => '0',
        'disporder' => '1',
        'gid' => intval($gid));
    $db->insert_query('settings', $sfsbulk_setting);
    
    $sfsbulk_setting = array(
        'name' => 'sfsbulk_regafter_datetime',
        'title' => 'Check users registered AFTER this date and time',
        'description' => 'Please enter a datetime in this format: yyyy-mm-dd hh:mm:ss . Time part is optional. Only users registered AFTER this date and time will be checked. Leave blank if you dont want to impose a lower limit. If you enter a value, make sure that it is valid. If it turned out to be invalid, it will be ignored.',
        'optionscode' => 'text',
        'value' => '',
        'disporder' => '2',
        'gid' => intval($gid));
    $db->insert_query('settings', $sfsbulk_setting);
    
    $sfsbulk_setting = array(
        'name' => 'sfsbulk_regbefore_datetime',
        'title' => 'Check users registered BEFORE this date and time',
        'description' => 'Please enter a date in this format: yyyy-mm-dd hh:mm:ss . Time part is optional. Only users registered BEFORE this date will be checked. Leave blank if you dont want to impose a higher limit. If you enter a value, make sure that it is valid. If it turned out to be invalid, it will be ignored.',
        'optionscode' => 'text',
        'value' => '',
        'disporder' => '3',
        'gid' => intval($gid));
    $db->insert_query('settings', $sfsbulk_setting);
    
    $sfsbulk_setting = array(
        'name' => 'sfsbulk_max_per_shift',
        'title' => 'Maximum users to check per run of the script',
        'description' => 'Tune this number to match with your scheduling policy and the limit imposed by StopForumSpam.com on your usage of API',
        'optionscode' => 'text',
        'value' => '2000',
        'disporder' => '4',
        'gid' => intval($gid));
    $db->insert_query('settings', $sfsbulk_setting);
    
    $sfsbulk_setting = array(
        'name' => 'sfsbulk_queryfield',
        'title' => 'Field to query on',
        'description' => 'Which field of the profile of users should be used to query StopForumSpam.com database?',
        'optionscode' => 'select\nregip=Registration IP Address\nlastip=Last Seen IP Address\nemail=Email Address\nusername=Username',
        'value' => 'email',
        'disporder' => '5',
        'gid' => intval($gid));
    $db->insert_query('settings', $sfsbulk_setting);
    
    $sfsbulk_setting = array(
        'name' => 'sfsbulk_action_on_spammer',
        'title' => 'Action on spammers',
        'description' => 'What to do with users who are spammers according to StopForumSpam.com?',
        'optionscode' => 'select\nban=Ban\ndelete=Delete',
        'value' => 'delete',
        'disporder' => '6',
        'gid' => intval($gid));
    $db->insert_query('settings', $sfsbulk_setting);
    
    $sfsbulk_setting = array(
        'name' => 'sfsbulk_lastseen_after',
        'title' => 'Last seen AFTER',
        'description' => 'Only those users will be processed who were seen AFTER this date and time on StopForumSpam.com. Please enter a valid datetime in the format yyyy-mm-dd hh:mm:ss . Invalid value will be ignored. Leave blank to explicitly ignore it.',
        'optionscode' => 'text',
        'value' => '',
        'disporder' => '7',
        'gid' => intval($gid));
    $db->insert_query('settings', $sfsbulk_setting);
    
    $sfsbulk_setting = array(
        'name' => 'sfsbulk_min_frequency',
        'title' => 'Minimum Frequency',
        'description' => 'Only those users will be processed who have this much or more frequency as reported by StopForumSpam.com. Please enter a non-negative integer or leave to default (0) if you dont want to narrow down search on this field.',
        'optionscode' => 'text',
        'value' => '0',
        'disporder' => '8',
        'gid' => intval($gid));
    $db->insert_query('settings', $sfsbulk_setting);
    
    $sfsbulk_setting = array(
        'name' => 'sfsbulk_min_confidence',
        'title' => 'Minimum Confidence',
        'description' => 'Only those users will be processed who have this much or more confidence value as reported by StopForumSpam.com. Please enter a positive number or leave to default (0.0) if you dont want to narrow down search on this field.',
        'optionscode' => 'text',
        'value' => '0.0',
        'disporder' => '9',
        'gid' => intval($gid));
    $db->insert_query('settings', $sfsbulk_setting);
    
    rebuild_settings();
    
    $db->write_query( "ALTER TABLE " . TABLE_PREFIX . "users ADD COLUMN sfsbulk_checked tinyint(1) DEFAULT 0" );
    $db->write_query( "ALTER TABLE " . TABLE_PREFIX . "users ADD COLUMN sfsbulk_last_checked bigint(30)" );
    $db->write_query( "ALTER TABLE " . TABLE_PREFIX . "users ADD COLUMN sfsbulk_appears tinyint(1) DEFAULT 0" );
    $db->write_query( "ALTER TABLE " . TABLE_PREFIX . "users ADD COLUMN sfsbulk_lastseen bigint(30)" );
    $db->write_query( "ALTER TABLE " . TABLE_PREFIX . "users ADD COLUMN sfsbulk_frequency int" );
    $db->write_query( "ALTER TABLE " . TABLE_PREFIX . "users ADD COLUMN sfsbulk_confidence float" );
}

function sfsbulk_uninstall() {
    global $db;
    $db->write_query( "DELETE FROM " . TABLE_PREFIX . "settings WHERE name LIKE 'sfsbulk_%'" );
    $db->write_query( "DELETE FROM " . TABLE_PREFIX . "settinggroups WHERE name = 'sfsbulkcheck'" );
    
    rebuild_settings();
    
    $db->write_query( "ALTER TABLE " . TABLE_PREFIX . "users DROP COLUMN sfsbulk_confidence" );
    $db->write_query( "ALTER TABLE " . TABLE_PREFIX . "users DROP COLUMN sfsbulk_frequency" );
    $db->write_query( "ALTER TABLE " . TABLE_PREFIX . "users DROP COLUMN sfsbulk_lastseen" );
    $db->write_query( "ALTER TABLE " . TABLE_PREFIX . "users DROP COLUMN sfsbulk_appears" );
    $db->write_query( "ALTER TABLE " . TABLE_PREFIX . "users DROP COLUMN sfsbulk_last_checked" );
    $db->write_query( "ALTER TABLE " . TABLE_PREFIX . "users DROP COLUMN sfsbulk_checked" );
}

function sfsbulk_is_installed() {
    global $db;
    return $db->simple_select("settings", "*", "name LIKE 'sfsbulk_%'")->num_rows;
}

$plugins->add_hook("admin_user_action_handler", "sfsbulk_user_action");

function sfsbulk_user_action($actions) {
    $actions['sfsbulk_dashboard'] = array('active' => 'sfsbulk_dashboard', 'file' => 'sfsbulk_dashboard.php');
    return $actions;
}

$plugins->add_hook('admin_user_menu', 'sfsbulk_user_menu_item');

function sfsbulk_user_menu_item($sub_menu) {
    $sub_menu['80'] = array("id" => "sfsbulk_dashboard", "title" => "SFSBulk Dashboard", "link" => "index.php?module=user-sfsbulk_dashboard");
    return $sub_menu;
}

?>