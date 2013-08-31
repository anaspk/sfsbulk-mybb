<?php

define("IN_MYBB", 1);
require_once "/var/www/iiuse/global.php";
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