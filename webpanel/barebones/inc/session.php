<?php

if ($_POST['username'] == $login_username && $_POST['password'] == $login_password) {
    $_SESSION['logged_in'] = true;
}

function session_function() {
    global $db;

    if(!$_GET['steamid'] || !$_GET['filename']) {
        return;
    }
    if (!$_GET['ban'] && !$_GET['unban']) {
        return;
    }
    if(!($stmt = $db->prepare("UPDATE `sprays` SET `banned` = ? WHERE `steamid` = ? AND `filename` = ?"))) {
        return;
    }

    if($_GET['ban']) {
        $stmt->execute([1, $_GET['steamid'], $_GET['filename']]);
    } else if ($_GET['unban']) {
        $stmt->execute([0, $_GET['steamid'], $_GET['filename']]);
    }

    $stmt->closeCursor();
}

session_function();