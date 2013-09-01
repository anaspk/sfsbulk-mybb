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

$page->add_breadcrumb_item("StopForumSpam", "index.php?module=user-mass_mail");
$page->output_header( "StopForumSpam - Bulk Delete" );
echo "\n\n<p>Here the header ends and footer starts.</p>\n\n";
sfs_delete_user(1538);
echo "<p>Deleted User with UID: " . 1538 . "</p>";
$page->output_footer();


function sfs_delete_user( $uid ) {
    global $db, $cache;
    
    $query = $db->simple_select("users", "*", "uid='".intval($uid)."'");
    $user = $db->fetch_array($query);
    
    // if the user exists
    if ( $user['uid'] ) {
        $db->delete_query("userfields", "ufid='{$uid}'");
        $db->delete_query("privatemessages", "uid='{$uid}'");
        $db->delete_query("events", "uid='{$uid}'");
        $db->delete_query("forumsubscriptions", "uid='{$uid}'");
        $db->delete_query("threadsubscriptions", "uid='{$uid}'");
        $db->delete_query("sessions", "uid='{$uid}'");
        $db->delete_query("banned", "uid='{$uid}'");
        $db->delete_query("threadratings", "uid='{$uid}'");
        $db->delete_query("users", "uid='{$uid}'");
        $db->delete_query("joinrequests", "uid='{$uid}'");
        $db->delete_query("warnings", "uid='{$uid}'");
        $db->delete_query("reputation", "uid='{$uid}' OR adduid='{$uid}'");
        $db->delete_query("awaitingactivation", "uid='{$uid}'");
        $db->delete_query("posts", "uid = '{$uid}' AND visible = '-2'");
        $db->delete_query("threads", "uid = '{$uid}' AND visible = '-2'");

        // Update forum stats
        update_stats(array('numusers' => '-1'));

        // Update forums & threads if user is the lastposter
        $db->update_query("posts", array('uid' => 0), "uid='{$uid}'");
        $db->update_query("forums", array("lastposteruid" => 0), "lastposteruid = '{$uid}'");
        $db->update_query("threads", array("lastposteruid" => 0), "lastposteruid = '{$uid}'");

        // Did this user have an uploaded avatar?
        if($user['avatartype'] == "upload")
        {
                // Removes the ./ at the beginning the timestamp on the end...
                @unlink("../".substr($user['avatar'], 2, -20));
        }

        // Was this user a moderator?
        if(is_moderator($uid))
        {
                $db->delete_query("moderators", "id='{$uid}' AND isgroup = '0'");
                $cache->update_moderators();
        }

        // Log admin action
        log_admin_action($uid, $user['username']);
    }
}
?>