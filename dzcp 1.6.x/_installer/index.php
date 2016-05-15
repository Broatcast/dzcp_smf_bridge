<?php
  /**
   * DZCP - deV!L`z ClanPortal 1.6 Final
   * http://www.dzcp.de
   * Addon: Forum Bewertungen * RANG-MOD *
   */
 
  ## OUTPUT BUFFER START ##
  include("../inc/buffer.php");

  ## INCLUDES ##
  include(basePath."/inc/debugger.php");
  include(basePath."/inc/config.php");
  include(basePath."/inc/bbcode.php");

  ## SETTINGS ##
  $where = "Installer - SMF Foren Bridge";
  $title = $pagetitel." - ".$where;

  ## INSTALLER ##
  if(isset($_POST['submit']))
  {
    db("DROP TABLE IF EXISTS `".$sql_prefix."smf_bridge`;");
    db("CREATE TABLE `dzcp_smf_bridge` (
          `id` int(11) NOT NULL,
          `smf_db_host` varchar(120) NOT NULL,
          `smf_db_user` varchar(120) NOT NULL,
          `smf_db_passwd` varchar(120) NOT NULL,
          `smf_db_name` varchar(120) NOT NULL,
          `smf_db_prefix` varchar(120) NOT NULL,
          `smf_url` varchar(120) NOT NULL,
          `smf_name` varchar(120) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    db("ALTER TABLE `".$sql_prefix."smf_bridge` ADD PRIMARY KEY (`id`);");
    db("ALTER TABLE `".$sql_prefix."smf_bridge` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

    // Dummy Daten einfügen
    db("INSERT INTO `".$sql_prefix."smf_bridge` (
          `id`,
          `smf_db_host`,
          `smf_db_user`,
          `smf_db_passwd`,
          `smf_db_name`,
          `smf_db_prefix`,
          `smf_url`,
          `smf_name`
        )
        VALUES
        (
          1,
          '127.0.0.1',
          'smf',
          'passwort',
          'smf',
          'smf_',
          'http://forum.de',
          'Test Board'
        );"
      );

    if(db("SHOW COLUMNS FROM `".$sql_prefix."smf_bridge`;", true))
    {
      $show = '
        <tr>
          <td class="contentHead" align="center"><span class="fontGreen"><b>Installation erfolgreich!</b></span></td>
        </tr>
        <tr>
          <td class="contentMainFirst" align="center">
            Die ben&ouml;tigten Tabellen konnten erfolgreich erstellt werden.<br />
            <br />
            <b>L&ouml;schen unbedingt den _installer-Ordner!</b><br /><br />
            F&uuml;ge in der Datei "/user/case_register.php" folgende Zeile:<br />
            smf_register_user(up($_POST[\'user\']), up($_POST[\'nick\']), up($_POST[\'email\']), $_POST[\'pwd\']);<br />
            <br />
            zwischen:<br />
            sendMail($_POST[\'email\'],re(settings(\'eml_reg_subj\')),$message);<br />
            und:<br />
            $index = info(show($msg, array("email" => $_POST[\'email\'])), "../user/?action=login");<br />
            ein.<br />
            <br />
            Sollte so aussehen:<br />
            setIpcheck("reg(".$insert_id.")");<br />
            $message = show(bbcode_email(settings(\'eml_reg\')), array("user" => $_POST[\'user\'], "pwd" => $mkpwd));<br />
            sendMail($_POST[\'email\'],re(settings(\'eml_reg_subj\')),$message);<br />
            smf_register_user(up($_POST[\'user\']), up($_POST[\'nick\']), up($_POST[\'email\']), $_POST[\'pwd\']);<br />
            $index = info(show($msg, array("email" => $_POST[\'email\'])), "../user/?action=login");<br />
          </td>
        </tr>
        <tr>
          <td class="contentBottom"></td>
        </tr>';
    }
    else
    {
      $show = '
        <tr>
          <td class="contentHead" align="center"><span class="fontWichtig"><b>FEHLER</b></span></td>
        </tr>
        <tr>
          <td class="contentMainFirst" align="center">
            Bei der Installation des AddOns ist ein Fehler aufgetreten. Bitte &uuml;berpr&uuml;fe deine Datenbank auf Sch&auml;den und versuche die Installation erneut.
          </td>
        </tr>
        <tr>
          <td class="contentBottom"></td>
        </tr>';
    }
  }
  else
  {
    $show = '
      <tr>
        <td class="contentHead" align="center"><b>SMF Forum Bridge</b></td>
      </tr>
      <tr>
        <td class="contentMainFirst" align="center">
          Hallo und herzlichen Dank, dass du dieses Addon f&uuml;r das deV!L´z Clanportal 1.6 heruntergeladen hast.<br />
          Dieser Installer soll dir die Arbeit abnehmen, die ben&ouml;tigten Tabellen in der Datenbank manuell erstellen zu m&uuml;ssen.
          <br /><br />
          <b><span style="text-align:center"><u>!!!WICHTIG!!!</u></span>
          <br />
          Erstell vor dem Ausf&uuml;hren des Installers ein Datenbank Backup. Haftungsanspr&uuml; f&uuml;r Sch&auml;den durch fehlerhafte
          Einbindung oder Nutzung sind ausgeschlossen!</b>
        </td>
      </tr>
      <tr>
        <td class="contentBottom" align="center">
          <form action="index.php?action=install" method="POST">
            <input class="submit" type="submit" name="submit" value="Tabellen anlegen" />
          </form>
        </td>
      </tr>';
  }

  ## SETTINGS ##
  page($show, $title, $where);