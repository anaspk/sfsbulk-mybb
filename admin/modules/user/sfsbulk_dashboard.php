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

$page->add_breadcrumb_item("SFSBulk Dashboard", "index.php?module=user-sfsbulk_dashboard");
$page->output_header( "SFSBulk Dashboard" );

// add page content here

$page->output_footer();

?>