<?php
/**
 * Post Likes Plugin for MyBB
 * Version: 1.0
 * Author: Matty Wjeisz
 */

if(!defined("IN_MYBB")) { die("Direct initialization of this file is not allowed."); }

// Hooks
$plugins->add_hook("postbit", "likes_plugin_postbit");
$plugins->add_hook("postbit_classic", "likes_plugin_postbit");
$plugins->add_hook("misc_start", "likes_plugin_handle");
$plugins->add_hook("member_profile_end", "likes_plugin_profile_load");


function likes_plugin_info() {
    return [
        "name"          => "Post Likes",
        "description"   => "Allows users to like/unlike posts.",
        "website"       => "https://www.curvesui.com/",
        "author"        => "Matty Wjeisz",
        "authorsite"    => "https://www.curvesui.com/",
        "version"       => "1.0",
        "compatibility" => "18*"
    ];
}


function likes_plugin_activate() {
    global $db;

    
    if(!$db->table_exists("post_likes")) {
        $collation = $db->build_create_table_collation();
        $db->query("
            CREATE TABLE ".TABLE_PREFIX."post_likes (
                lid INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                pid INT(10) UNSIGNED NOT NULL,
                uid INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY(lid),
                UNIQUE KEY pid_uid (pid, uid)
            ) ENGINE=MyISAM{$collation};
        ");
    }

    
    if(!$db->field_exists("post_likes", "users")) {
        $db->query("ALTER TABLE ".TABLE_PREFIX."users ADD COLUMN post_likes INT(10) UNSIGNED NOT NULL DEFAULT 0");
    }

    
    $group_exists = $db->fetch_field(
        $db->simple_select("templategroups", "COUNT(*) AS count", "prefix='postlikes'"),
        "count"
    );
    if(!$group_exists) {
        $db->insert_query("templategroups", [
            "prefix" => "postlikes",
            "title"  => "Post Likes"
        ]);
    }

    
    $templates = [
        'postlikes_like_link'           => '<a href="{$mybb->settings[\'bburl\']}/misc.php?action=post_like&pid={$post[\'pid\']}" class="like_link">Like</a>',
        'postlikes_unlike_link'         => '<a href="{$mybb->settings[\'bburl\']}/misc.php?action=post_like&unlike=1&pid={$post[\'pid\']}" class="unlike_link">Unlike</a>',
        'postlikes_like_count'          => '<span class="like_count">({$like_count})</span>',
        'postlikes_like_count_guest'    => '<span class="like_count_guest">{$like_count} Likes</span>'
    ];

    foreach($templates as $title => $content) {
        $exists = $db->fetch_field(
            $db->simple_select("templates", "COUNT(*) AS count", "title='{$db->escape_string($title)}'"),
            "count"
        );
        if(!$exists) {
            $db->insert_query("templates", [
                "title"    => $title,
                "template" => $db->escape_string($content),
                "sid"      => 0,
                "version"  => 11000,
                "dateline" => TIME_NOW
            ]);
        }
    }
}


function likes_plugin_deactivate() {
    global $db;

    
    if($db->table_exists("post_likes")) {
        $db->drop_table("post_likes");
    }
    if($db->field_exists("post_likes", "users")) {
        $db->query("ALTER TABLE ".TABLE_PREFIX."users DROP COLUMN post_likes");
    }

   
}


function likes_plugin_postbit(&$post) {
    global $templates, $db, $mybb;

    $like_count = $db->fetch_field(
        $db->simple_select("post_likes", "COUNT(*) AS count", "pid='{$post['pid']}'"),
        "count"
    );

   
    if($mybb->user['uid'] == 0) {
        $like_count_template = str_replace('{$like_count}', $like_count, stripslashes($templates->get("postlikes_like_count_guest")));
        $post['like_link'] = $like_count_template;
        return;
    }

    
    $user_liked = $db->fetch_field(
        $db->simple_select("post_likes", "COUNT(*) AS count", "pid='{$post['pid']}' AND uid='{$mybb->user['uid']}'"),
        "count"
    );

    $bburl = $mybb->settings['bburl'];
    $pid = $post['pid'];

    if($user_liked) {
        $like_link = str_replace(
            ['{$mybb->settings[\'bburl\']}', '{$post[\'pid\']}'],
            [$bburl, $pid],
            stripslashes($templates->get("postlikes_unlike_link"))
        );
    } else {
        $like_link = str_replace(
            ['{$mybb->settings[\'bburl\']}', '{$post[\'pid\']}'],
            [$bburl, $pid],
            stripslashes($templates->get("postlikes_like_link"))
        );
    }

    $like_count_template = str_replace('{$like_count}', $like_count, stripslashes($templates->get("postlikes_like_count")));

    $post['like_link'] = $like_link . " " . $like_count_template;
}


function likes_plugin_handle() {
    global $mybb, $db;

    if($mybb->input['action'] == "post_like") {
        $pid = intval($mybb->input['pid']);
        $uid = $mybb->user['uid'];

        if(!$uid || !$pid) {
            error("Invalid request");
        }

        $post_user_id = $db->fetch_field(
            $db->simple_select("posts", "uid", "pid='{$pid}'"),
            "uid"
        );

        $liked = $db->fetch_field(
            $db->simple_select("post_likes", "COUNT(*) AS count", "pid='{$pid}' AND uid='{$uid}'"),
            "count"
        );

       
        $current = $db->fetch_field(
            $db->simple_select("users", "post_likes", "uid='{$post_user_id}'"),
            "post_likes"
        );

        if(isset($mybb->input['unlike']) && $liked) {
            $db->delete_query("post_likes", "pid='{$pid}' AND uid='{$uid}'");
            $db->update_query("users", ["post_likes" => max(0, $current - 1)], "uid='{$post_user_id}'");
        } elseif(!$liked) {
            $db->insert_query("post_likes", ["pid" => $pid, "uid" => $uid]);
            $db->update_query("users", ["post_likes" => $current + 1], "uid='{$post_user_id}'");
        }

        redirect("showthread.php?pid={$pid}#pid{$pid}");
    }
}


function likes_plugin_profile_load() {
    global $db, $memprofile;

    if(!empty($memprofile['uid'])) {
        $uid = intval($memprofile['uid']);
        $data = $db->fetch_array(
            $db->simple_select("users", "post_likes", "uid='{$uid}'")
        );
        $memprofile['post_likes'] = isset($data['post_likes']) ? $data['post_likes'] : 0;
    }
}
