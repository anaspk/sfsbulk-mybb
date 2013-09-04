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

 
define("IN_MYBB", 1);
require_once __DIR__ . "/../../global.php";
require_once MYBB_ROOT."inc/functions_post.php";

// proceed only if the plugin is installed
if ( $db->simple_select("settings", "*", "name LIKE 'sfsbulk_%'")->num_rows ) {
    $sfsbulk_action_on_spammer = $mybb->settings['sfsbulk_action_on_spammer'];
    $sfsbulk_lastseen_after = $mybb->settings['sfsbulk_lastseen_after'];
    $sfsbulk_min_frequency = $mybb->settings['sfsbulk_min_frequency'];
    $sfsbulk_min_confidence = $mybb->settings['sfsbulk_min_confidence'];
    
    require_once MYBB_ROOT."inc/plugins/sfsbulk/sfsbulk_functions.php";
    
    $sfsbulk_conditions = "sfsbulk_checked = 1";
    $sfsbulk_conditions .= " AND sfsbulk_appears = 1";
    if ( $sfsbulk_lastseen_after && $sfsbulk_lastseen_after != -1 ) {
        $sfsbulk_conditions .= " AND sfsbulk_lastseen > " . intval($sfsbulk_lastseen_after);
    }
    $sfsbulk_conditions .= " AND sfsbulk_frequency >= " . intval($sfsbulk_min_frequency);
    $sfsbulk_conditions .= " AND sfsbulk_confidence >= " . floatval($sfsbulk_min_confidence);
    
    //echo "The condition is:\n{$sfsbulk_conditions}\n";
    $query = $db->simple_select(
                                "users",
                                "uid",
                                $sfsbulk_conditions,
                                array( 'orderby' => 'regdate' )
                                );
    
    echo "{$query->num_rows} spammers found\n";
                                
    while ( $user = $db->fetch_array( $query ) ) {
        sfsbulk_handle_spammer( $user[ 'uid' ]);    
    }
} else {
    echo "Please first install the plugin\n";
}
?>