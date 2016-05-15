<?php
  if(_adminMenu != 'true') exit;

  $where = $where.': '._config_smfbridge;

  if($chkMe != 4)
  {
    $show = error(_error_wrong_permissions, 1);
  } else {
    $action = (isset($_GET['do'])) ? $_GET['do'] : "";

    switch($action) {
      case "update":
        smf_update_settings($_POST['db_host'], $_POST['db_user'], $_POST['db_passwd'], $_POST['db_name'], $_POST['db_prefix'], $_POST['board_name'], $_POST['board_url']);
        $show = info(_config_set, "?admin=smfbridge", 5);
        break;
      case "check_db":
        $exists = smf_check_db();

        if($exists == true)
        {
          $show = info(_smf_db_check_ok, "?admin=smfbridge", 5);
        }
        else
        {
          $show = info(_smf_db_check_fail, "?admin=smfbridge", 5);
        }
        break;
      case "check_users":
        $user_list = smf_check_users();

        foreach($user_list AS $user_id => $user)
        {
          $class = ($color % 2) ? "contentMainSecond" : "contentMainFirst"; $color++;
          if($user['status'] == 1) $status = '<b style="color:green">'._smf_status_already_synced.'</b>';
          elseif($user['status'] == 2) $status = '<b style="color:red">'._smf_status_nick_exists.'</b>';
          elseif($user['status'] == 3) $status = '<b style="color:red">'._smf_status_email_exists.'</b>';
          elseif($user['status'] == 4) $status = '<b style="color:lightgreen">'._smf_status_synced.'</b>';

          $show .= show($dir."/smfbridge_compare_show", array(
            "class" => $class,
            "dzcp_user" => $user['dzcp_user'],
            "dzcp_nick" => autor($user_id), //$user['dzcp_nick'],
            "dzcp_email" => $user['dzcp_email'],
            "smf_user" => $user['smf_user'],
            "smf_nick" => $user['smf_nick'],
            "smf_user" => $user['smf_user'],
            "smf_email" => $user['smf_email'],
            "status" => $status
          ));
        }

        $show = show($dir."/smfbridge_compare", array(
          "head" => _config_smfbridge." - "._button_compare_bridge_users,
          "show" => $show,
          "dzcp_user" => _smf_compare_dzcp_user,
          "dzcp_nick" => _smf_compare_dzcp_nick,
          "dzcp_email" => _smf_compare_dzcp_email,
          "smf_user" => _smf_compare_smf_user,
          "smf_nick" => _smf_compare_smf_nick,
          "smf_email" => _smf_compare_smf_email,
          "status" => _smf_compare_status
        ));

        break;
      case "show":
      default:
        $qry = db("SELECT * FROM ".$sql_prefix."smf_bridge");
        $get = _fetch($qry);

        $show_prepare = show($dir."/form_smfbridge", array(
          "head" => _config_global_head,
          "save" => _button_value_config,
          "db_host" => $get['smf_db_host'],
          "db_user" => $get['smf_db_user'],
          "db_passwd" => $get['smf_db_passwd'],
          "db_name" => $get['smf_db_name'],
          "db_prefix" => $get['smf_db_prefix'],
          "smf_db_settings" => _smf_db_settings,
          "smf_db_settings_info" => _smf_db_settings_info,
          "smf_db_host" => _smf_db_host,
          "smf_db_user" => _smf_db_user,
          "smf_db_passwd" => _smf_db_passwd,
          "smf_db_name" => _smf_db_name,
          "smf_db_prefix" => _smf_db_prefix,
          "smf_board_name" => _smf_board_name,
          "smf_board_url" => _smf_board_url,
          "board_name" => $get['smf_name'],
          "board_url" => $get['smf_url']
        ));

        $show_form = show($dir."/form", array(
          "head" => _config_smfbridge,
          "what" => "smfbridge",
          "value" => _button_value_config,
          "show" => $show_prepare
        ));

        $show = show($dir."/smfbridge_actions", array(
          "smf_settings_form" => $show_form,
          "head2" => _smf_actions,
          "check_db" => _button_db_test,
          "check_users" => _button_compare_bridge_users,
          "run_cron" => _button_run_cron
        ));
        break;
    }
  }