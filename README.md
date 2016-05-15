Just still a sinple bridge for dzcp -> smf.
Upload all php files to your webroot, and the template files
into your template directory.
Then run the installation Script.
www.yourdomain.tld/_installer/

After running the installation script you need to modify the
"/user/case_register.php" file.

Insert:
```php
smf_register_user(up($_POST['user']), up($_POST['nick']), up($_POST['email']), $_POST['pwd']);
```
Between:
```php
sendMail($_POST['email'],re(settings('eml_reg_subj')),$message);
```
and
```php
$index = info(show($msg, array("email" => $_POST['email'])), "../user/?action=login");
```

Should look like:
```php
setIpcheck("reg(".$insert_id.")");
$message = show(bbcode_email(settings('eml_reg')), array("user" => $_POST['user'], "pwd" => $mkpwd));
sendMail($_POST['email'],re(settings('eml_reg_subj')),$message);
smf_register_user(up($_POST['user']), up($_POST['nick']), up($_POST['email']), $_POST['pwd']);
$index = info(show($msg, array("email" => $_POST['email'])), "../user/?action=login");
```
