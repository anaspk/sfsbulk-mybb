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

function sfsbulk_handle_spammer( $uid )  {
    global $db, $sfsbulk_action_on_spammer;
    echo "Asked to handle: {$uid}\nI will ";
    if ( $sfsbulk_action_on_spammer == 'delete') {
        echo "DELETE";
    } else {
        echo "BAN";
    }
    echo " it.\n";
}

function sfsbulk_delete_user( $uid ) {
    global $db, $cache;
    
    $query = $db->simple_select("users", "*", "uid='". intval($uid) ."'");
    $user = $db->fetch_array($query);
    
    // if the user exists
    if ( $user['uid'] ) {
        $db->delete_query("userfields", "ufid='{intval($uid)}'");
        $db->delete_query("privatemessages", "uid='{intval($uid)}'");
        $db->delete_query("events", "uid='{intval($uid)}'");
        $db->delete_query("forumsubscriptions", "uid='{intval($uid)}'");
        $db->delete_query("threadsubscriptions", "uid='{intval($uid)}'");
        $db->delete_query("sessions", "uid='{intval($uid)}'");
        $db->delete_query("banned", "uid='{intval($uid)}'");
        $db->delete_query("threadratings", "uid='{intval($uid)}'");
        $db->delete_query("users", "uid='{intval($uid)}'");
        $db->delete_query("joinrequests", "uid='{intval($uid)}'");
        $db->delete_query("warnings", "uid='{intval($uid)}'");
        $db->delete_query("reputation", "uid='{intval($uid)}' OR adduid='{intval($uid)}'");
        $db->delete_query("awaitingactivation", "uid='{intval($uid)}'");
        $db->delete_query("posts", "uid = '{intval($uid)}' AND visible = '-2'");
        $db->delete_query("threads", "uid = '{intval($uid)}' AND visible = '-2'");

        // Update forum stats
        update_stats(array('numusers' => '-1'));

        // Update forums & threads if user is the lastposter
        $db->update_query("posts", array('uid' => 0), "uid='{intval($uid)}'");
        $db->update_query("forums", array("lastposteruid" => 0), "lastposteruid = '{intval($uid)}'");
        $db->update_query("threads", array("lastposteruid" => 0), "lastposteruid = '{intval($uid)}'");

        // Did this user have an uploaded avatar?
        if($user['avatartype'] == "upload")
        {
                // Removes the ./ at the beginning the timestamp on the end...
                @unlink("../".substr($user['avatar'], 2, -20));
        }

        // Was this user a moderator?
        if(is_moderator(intval($uid)))
        {
                $db->delete_query("moderators", "id='{intval($uid)}' AND isgroup = '0'");
                $cache->update_moderators();
        }
    }
}

function sfsbulk_ban_user( $uid  ) {
    
}

function sfsbulk_urlencode_callback( &$value, $index, $data ) {
    if ( $index <= $data[ 'processed_count' ] ) {
        $value = urlencode( $value );
    }
}

function sfsbulk_process_batch_of_users( $batch_of_users, $this_check_start_time, $processed_count = 14 ) {
    global $sfsbulk_queryfield, $db;
    
    array_walk( $batch_of_users[ 'sfsbulk_queryfield' ], 'sfsbulk_urlencode_callback', array( 'processed_count' => $processed_count) );
    
    $query_string = $sfsbulk_queryfield . "[]=";
    if ( $processed_count == 14 ) {
        $query_string .= implode( "&{$sfsbulk_queryfield}[]=", $batch_of_users[ 'sfsbulk_queryfield' ] );
    } else {
        for ( $i = 0; $i <= $processed_count; $i++ ) {
            if ( $i > 0 ) {
                $query_string .= "&{$sfsbulk_queryfield}[]=";
            }
            $query_string .= $batch_of_users[ 'sfsbulk_queryfield' ][ $i ];
        }
    }
    
    // get response in the JSON format
    $query_string .= "&f=json";
    $url = "http://www.stopforumspam.com/api?" . $query_string;
    
    if ( $connection = curl_init($url) ) {
        curl_setopt( $connection, CURLOPT_RETURNTRANSFER, TRUE );
        $json_response = curl_exec( $connection );
        $json_processed = json_decode( $json_response, true );
        if ( $json_processed[ 'success' ] ) {
            $results = $json_processed[ $sfsbulk_queryfield ];            
            
            $i = 0;
            foreach ( $results as $result ) {
                $fields_values_array = array();
                $fields_values_array[ 'sfsbulk_checked' ] = intval( 1 );
                $fields_values_array[ 'sfsbulk_last_checked' ] = intval( $this_check_start_time );
                if ( $result[ 'appears' ] ) {
                    $fields_values_array[ 'sfsbulk_appears' ] = intval( $result[ 'appears' ] );
                    $fields_values_array[ 'sfsbulk_lastseen' ] = strtotime( $result[ 'lastseen' ] );
                    $fields_values_array[ 'sfsbulk_frequency' ] = intval( $result[ 'frequency' ] );
                    $fields_values_array[ 'sfsbulk_confidence' ] = floatval( $result[ 'confidence' ] );
                }
                // mark user as checked
                $db->update_query(
                                  'users',
                                  $fields_values_array, 
                                  'uid = ' . intval( $batch_of_users['uid'][$i] ),
                                  '1'
                                  );
                
                $i++;
            }
        }
    } else {
        echo "<p>curl_init() failed for {$url}</p>";
    }

}

?>