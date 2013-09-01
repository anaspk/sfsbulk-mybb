# StopForumSpam Bulk-checker for MyBB

This is a plugin for the popular open source bulletin board software, MyBB, that helps you check
the complete, existing user base of your forum for potential spam registrations by querying the
StopForumSpam.com database.

## Overview

### Motivation and Use Case

### Features

1. Filter which users to check based on:
 * Post Count
 * Registration Date
2. Select which data field of the selected users should be checked from among:
 * Username
 * Email Address
 * Registration IP Address
 * Last Visit IP Address
3. Filter which users should be deleted out of those reported as spammers by StopForumSpam.com based on other data like:
 * When was the last time the spammer was seen
 * Frequency of appearance
 * Confidence Value

### Installation Instructions

1. Download the zip archive and extract it.
2. Upload all extracted files to the root of your MyBB installation, keeping the folder structure intact.
3. Go to you MyBB Admin CP -> Configuration -> Plgins and Click on "Install and Activate" infront of "StopForumSpam Bulk-checker for MyBB".

## Configuration and Usage

### Settings

You can fine tune various aspects of the working of this plugin by going to Admin CP -> Configuration -> SFS Bulk Check Settings.

Settings on this page can be broadly devided into two categories.

### Running the tasks

You have two options to run the scripts provided with this plugin that do the actual job of scanning your
database againts that of StopForumSpam.com service to decide which of the users registered on your forum
are spammers, and delete them.

#### Run Via MyBB Tasks System

#### Run Via Cron Jobs

If you are hosting your forum on OpenShift PaaS then you can follow these instructions to run these script via cron.