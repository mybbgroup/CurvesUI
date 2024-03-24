<?php

/*
MyBB Default Avatar Fix Plugin v0.1
Copyright (C) 2015 SvePu

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function defaultavatarfix_info()
{
    global $mybb, $db, $lang;
    
    return array(
        'name'          => 'MyBB Default Avatar Fix',
        'description'   => 'Simple MyBB 1.8 plugin to fix the empty default avatar in custom themes member menu',
        'website'       => 'https://github.com/SvePu/MyBB-Default-Avatar-Fix',
        'author'        => 'SvePu',
        'authorsite'    => 'https://github.com/SvePu',
        'version'       => '0.1',
        'codename'      => 'defaultavatarfix',
        'compatibility' => '18*'
    );
}

function defaultavatarfix_activate()
{

}


function defaultavatarfix_deactivate()
{

}

function defaultavatarfix()
{
	 global $mybb;
	 
	 if(!$mybb->user['avatar'] && !empty($mybb->settings['useravatar']))
	 {
		$mybb->user['avatar'] = $mybb->settings['useravatar'];
	 }
}
$plugins->add_hook("global_start", "defaultavatarfix");
?>