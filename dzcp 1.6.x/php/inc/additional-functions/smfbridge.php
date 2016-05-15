<?php
  function generatePassword($passwordlength = 8, $numNonAlpha = 0, $numNumberChars = 0, $useCapitalLetter = false)
  {
    $numberChars = '123456789';
    $specialChars = '!$%&=?*-:;.,+~@_';
    $secureChars = 'abcdefghjkmnpqrstuvwxyz';
    $stack = '';

    $stack = $secureChars;

    if ($useCapitalLetter == true)
      $stack .= strtoupper($secureChars);

    $count = $passwordlength - $numNonAlpha - $numNumberChars;
    $temp = str_shuffle($stack);
    $stack = substr($temp , 0, $count);

    if ($numNonAlpha > 0)
    {
      $temp = str_shuffle($specialChars);
      $stack .= substr($temp , 0, $numNonAlpha);
    }

    if ($numNumberChars > 0)
    {
      $temp = str_shuffle($numberChars);
      $stack .= substr($temp , 0, $numNumberChars);
    }

    $stack = str_shuffle($stack);

    return $stack;
  }

  function smf_update_settings($database_host, $database_user, $database_passwd, $database_name, $database_prefix, $board_name, $board_url)
  {
    global $sql_prefix;

    db("UPDATE `".$sql_prefix."smf_bridge`
            SET
              `smf_db_host` = '".up($database_host)."',
              `smf_db_user` = '".up($database_user)."',
              `smf_db_passwd` = '".up($database_passwd)."',
              `smf_db_name` = '".up($database_name)."',
              `smf_db_prefix` = '".up($database_prefix)."',
              `smf_name` = '".up($board_name)."',
              `smf_url` = '".up($board_url)."'
            WHERE
              `id` = '1';"
      );
  }

  function smf_db_query($query)
  {
    global $sql_prefix;

    $qry = db("SELECT `smf_db_host`, `smf_db_user`, `smf_db_passwd`, `smf_db_name`, `smf_db_prefix` FROM ".$sql_prefix."smf_bridge;");
    while($data = _fetch($qry))
    {
      $smf_db = $data;
    }

    $smf_link = mysql_connect($smf_db['smf_db_host'], $smf_db['smf_db_user'], $smf_db['smf_db_passwd']);
    mysql_select_db($smf_db['smf_db_name'], $smf_link);

    $qry = str_replace("[[pfx]]", $smf_db['smf_db_prefix'], $query);
    $result = mysql_query($qry);

    return $result;
  }

  function smf_check_db()
  {
    $exists = false;

    $qry = smf_db_query("SHOW TABLES LIKE '[[pfx]]members'");

    if(mysql_num_rows($qry) == 1)
    {
      $exists = true;
    }

    return $exists;
  }

  function smf_register_user($loginname, $nick, $email, $passwd)
  {
    global $sql_prefix;

    $qry = smf_db_query("SELECT * FROM `[[pfx]]members` WHERE `member_name` = '".$loginname."';");
    if(mysql_num_rows($qry) == 0)
    {
      $qry = smf_db_query("SELECT * FROM `[[pfx]]members` WHERE `real_name` = '".$nick."';");
      if(mysql_num_rows($qry) == 0)
      {
        $qry = smf_db_query("SELECT * FROM `[[pfx]]members` WHERE `email_address` = '".$email."';");
        if(mysql_num_rows($qry) == 0)
        {
          smf_db_query("INSERT INTO `[[pfx]]members` (
              `id_member`,
              `member_name`,
              `date_registered`,
              `lngfile`,
              `last_login`,
              `real_name`,
              `pm_ignore_list`, `mod_prefs`,
              `passwd`,
              `email_address`,
              `personal_text`, `website_title`, `website_url`, `location`, `icq`, `aim`, `yim`, `msn`,
              `hide_email`,
              `time_format`, `avatar`,
              `pm_email_notify`,
              `usertitle`, `member_ip`, `member_ip2`, `secret_question`, `secret_answer`, `validation_code`,
              `id_msg_last_visit`,
              `additional_groups`, `smiley_set`,
              `password_salt`,
              `passwd_flood`
            )
            VALUES
            (
              NULL,
              '".$loginname."',
              UNIX_TIMESTAMP(),
              '',
              UNIX_TIMESTAMP(),
              '".$nick."',
              '', '',
              '".sha1(strtolower($loginname).$passwd)."',
              '".$email."',
              '', '', '', '', '', '', '', '',
              '1',
              '', '',
              '1',
              '', '', '', '', '', '',
              '1',
              '', '',
              '".substr(md5(mt_rand()), 0, 4)."',
              ''
            );");

          smf_db_query("INSERT INTO `[[pfx]]themes` (
              `id_member`,
              `id_theme`,
              `variable`,
              `value`
            )
            VALUES
            (
              '".mysql_insert_id()."',
              '1',
              'display_quick_reply',
              '1'
            );");
        }
      }
    }
  }

  function smf_check_users()
  {
    global $sql_prefix, $db;

    $dzcp_users = array();
    $user_list = array();

    $qry = db("SELECT `id`, `user`, `nick`, `email` FROM `".$sql_prefix."users`;");
    while($data = _fetch($qry))
    {
      $dzcp_users[$data['id']]['user'] = $data['user'];
      $dzcp_users[$data['id']]['nick'] = $data['nick'];
      $dzcp_users[$data['id']]['email'] = $data['email'];
    }

    foreach($dzcp_users AS $id => $user_data)
    {
      $qry = smf_db_query("SELECT `member_name`, `real_name`, `email_address` FROM `[[pfx]]members` WHERE `member_name` = '".$user_data['user']."' ;");
      if(mysql_num_rows($qry) == 1)
      {
        $user_list[$id]['dzcp_user'] = $user_data['user'];
        $user_list[$id]['dzcp_nick'] = $user_data['nick'];
        $user_list[$id]['dzcp_email'] = $user_data['email'];
        $user_list[$id]['smf_user'] = mysql_result($qry, 0, 'member_name');
        $user_list[$id]['smf_nick'] = mysql_result($qry, 0, 'real_name');
        $user_list[$id]['smf_email'] = mysql_result($qry, 0, 'email_address');
        $user_list[$id]['status'] = 1;
      }
      else
      {
        $qry = smf_db_query("SELECT `member_name`, `real_name`, `email_address` FROM `[[pfx]]members` WHERE `real_name` = '".$user_data['nick']."' ;");
        if(mysql_num_rows($qry) == 1)
        {
          $user_list[$id]['dzcp_user'] = $user_data['user'];
          $user_list[$id]['dzcp_nick'] = $user_data['nick'];
          $user_list[$id]['dzcp_email'] = $user_data['email'];
          $user_list[$id]['smf_user'] = mysql_result($qry, 0, 'member_name');
          $user_list[$id]['smf_nick'] = mysql_result($qry, 0, 'real_name');
          $user_list[$id]['smf_email'] = mysql_result($qry, 0, 'email_address');
          $user_list[$id]['status'] = 2;
        }
        else
        {
          $qry = smf_db_query("SELECT `member_name`, `real_name`, `email_address` FROM `[[pfx]]members` WHERE `email_address` = '".$user_data['email']."' ;");
          if(mysql_num_rows($qry) == 1)
          {
            $user_list[$id]['dzcp_user'] = $user_data['user'];
            $user_list[$id]['dzcp_nick'] = $user_data['nick'];
            $user_list[$id]['dzcp_email'] = $user_data['email'];
            $user_list[$id]['smf_user'] = mysql_result($qry, 0, 'member_name');
            $user_list[$id]['smf_nick'] = mysql_result($qry, 0, 'real_name');
            $user_list[$id]['smf_email'] = $user_data['email'];
            $user_list[$id]['status'] = 3;
          }
          else
          {
            $password = generatePassword(8,2,2, true);

            smf_register_user($user_data['user'], $user_data['nick'], $user_data['email'], $password);

            $qry = db("SELECT `smf_name`, `smf_url` FROM ".$sql_prefix."smf_bridge;");
            while($data = _fetch($qry))
            {
              $smf_data = $data;
            }

            $msg = str_replace(array("[[url]]", "[[board_name]]", "[[username]]", "[[password]]"), array($smf_data['smf_url'], $smf_data['smf_name'], $user_data['user'], $password), _smf_msg);
            db("INSERT INTO ".$db['msg']." SET `datum` = '".((int)time())."', `von` = '0', `an` = '".((int)$id)."', `titel` = '"._smf_msg_title."', `nachricht` = '".up($msg,1)."'");

            $user_list[$id]['dzcp_user'] = $user_data['user'];
            $user_list[$id]['dzcp_nick'] = $user_data['nick'];
            $user_list[$id]['dzcp_email'] = $user_data['email'];
            $user_list[$id]['smf_user'] = $user_data['user'];
            $user_list[$id]['smf_nick'] = $user_data['nick'];
            $user_list[$id]['smf_email'] = $user_data['email'];
            $user_list[$id]['status'] = 4;
          }
        }
      }
    }

    return $user_list;
  }
?>