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
require_once "/var/www/iiuse/global.php";
require_once MYBB_ROOT."inc/functions_post.php";

// proceed only if the plugin is installed
if ( $db->simple_select("settings", "*", "name LIKE 'sfsbulk_%'")->num_rows ) {
    $sfsbulk_max_posts = intval( $mybb->settings['sfsbulk_max_posts'] );
    $sfsbulk_regafter_date = strtotime( $mybb->settings['sfsbulk_regafter_datetime'] );
    $sfsbulk_regbefore_date = strtotime( $mybb->settings['sfsbulk_regbefore_datetime'] );
    $sfsbulk_max_per_shift = intval( $mybb->settings['sfsbulk_max_per_shift'] );
    $sfsbulk_queryfield = $mybb->settings['sfsbulk_queryfield'];
    
    require_once MYBB_ROOT."inc/plugins/sfsbulk/sfsbulk_functions.php";
    
    $sfsbulk_conditions = array();
    
    if ( $sfsbulk_max_posts >= 0 ) {
        $sfsbulk_conditions[] = "postnum <= {$sfsbulk_max_posts}";
    }
    if ( $sfsbulk_regafter_date && $sfsbulk_regafter_date != -1 ) {
        $sfsbulk_conditions[] = "regdate > {$sfsbulk_regafter_date}";
    }
    if ( $sfsbulk_regbefore_date && $sfsbulk_regbefore_date != -1 ) {
        $sfsbulk_conditions[] = "regdate < {$sfsbulk_regbefore_date}";
    }
    $sfsbulk_conditions[] = "sfsbulk_checked = 0";
    $sfsbulk_conditions = implode( " AND ", $sfsbulk_conditions );
    
    // just for testing
    $sfsbulk_max_per_shift = 20;
    $query = $db->simple_select("users", "uid, {$sfsbulk_queryfield}", $sfsbulk_conditions, array( 'limit' => intval( $sfsbulk_max_per_shift ), 'orderby' => 'regdate' ) );
    
    $processed_count = 0;
    $batch_of_users = array(
                            'uid' => array_fill( 0, 15, null ),
                            'sfsbulk_queryfield' => array_fill( 0, 15, null )
                            );
    $this_check_start_time = time();
    echo $query->num_rows . " records selected\n";
    echo "\n";                        
    
    while ( $user = $db->fetch_array($query) ) {
        $batch_of_users[ 'uid' ][ $processed_count ] = $user[ 'uid' ];
        $batch_of_users[ 'sfsbulk_queryfield' ][ $processed_count ] = $user[ $sfsbulk_queryfield ];
        if ( $processed_count < 14 ) {
            $processed_count++;
        } else {
            sfsbulk_process_batch_of_users( $batch_of_users, $this_check_start_time );
            $processed_count = 0;
        }
    }
    if ( $processed_count > 0 ) {
        sfsbulk_process_batch_of_users( $batch_of_users, $this_check_start_time, $processed_count - 1 );
    }
} else {
    echo "Please first install the plugin\n";
}
?>
