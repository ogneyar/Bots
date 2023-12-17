<?php

	$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
	$host = $url["host"];
	$username = $url["user"];
	$password = $url["pass"];
	$dbname = substr($url["path"], 1);
	
	$tokenInfoUsers = getenv("TOKEN_INFOUSERS");
    $tokenMadeline = getenv("TOKEN_MADELINE");

	$admin_group_InfoUsers = getenv('ADMIN_GROUP_INFOUSERS');	
	$test_group = getenv('TEST_GROUP');

	$master = getenv("MASTER");

	$passSMTP = getenv("PASSWORD_SMTP");
	
	$mail_api_key = getenv("MAILGUN_API_KEY");
	$mail_domain = getenv("MAILGUN_DOMAIN");
	$mail_public_key = getenv("MAILGUN_PUBLIC_KEY");
	$mail_smtp_login = getenv("MAILGUN_SMTP_LOGIN");
	$mail_smtp_pass = getenv("MAILGUN_SMTP_PASSWORD");
	$mail_smtp_port = getenv("MAILGUN_SMTP_PORT");
	$mail_smtp_server = getenv("MAILGUN_SMTP_SERVER");

?>
