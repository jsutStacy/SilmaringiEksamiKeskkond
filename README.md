SilmaringiEksamiKeskkond
========================

Loodud Tarkvaraprojekti aine (MTAT.03.138) raames Tartu Ülikoolis 2014 aastal.

Project Setup:
========================

First download and install XAMPP:
```
https://www.apachefriends.org/download.html
```

Then clone our project from git.

Open cmd and go to path/to/project and run:
```
php composer.phar self-update
```
```
php composer.phar install
```
(make sure you have php path added into environment variables path)

Now go to path/to/project/vhosts and make new folder named after your first name in lowercase.
Now we have to make our own vhost file. Name it vhost.conf.

It should look something like this:
```
<VirtualHost 127.0.0.2:80>
	ServerName yoururl // mine is eksamikeskkond.silmaring.dev
	DocumentRoot path/to/public
	SetEnv APPLICATION_ENV "development"

	<Directory path/to/public>
		DirectoryIndex index.php
		AllowOverride All
		Order allow,deny
		Allow from all
		Require all granted
	</Directory>
</VirtualHost>
```
Now we have to include it to apache conf.

Go to path/to/xampp/apache/conf and open httpd.conf.
Uncomment line:
```
LoadModule rewrite_module modules/mod_rewrite.so
```

At the end of conf file add line:
```
Include "path/to/project/vhosts/yourfoldername/vhost.conf"
```

Go to path/to/xampp/php and open php.ini.
Uncomment line:
```
extension=php_openssl.dll
```

Now go to C:/Windows/System32/drivers/etc and open hosts file.
Add:
```
127.0.0.2	yoururl
```
Mine is:
```
127.0.0.2	eksamikeskkond.silmaring.dev
```

Then save it and restart apache from xampp.

Now when you open your web browser and go to your url you should see the zend framework 2 opening screen.

For database connection make new local config file.
Go to path/to/project/config/autoload and make new php file called yourfirstname.local.php. Mine is andres.local.php
and add this into it:

```
<?php
	return array(
		'db' => array(
			'username' => 'yourmysqlusername',
			'password' => 'yourmysqlpassword',
		),
	);
?>
```
and save it.

For e-mail sending make new local config file.
Go to path/to/project/config/autoload and make new php file called mail.config.local.php and add this into it:

```
<?php
	return array(
		'mail' => array(
			'transport' => array(
				'options' => array(
					'host' => 'smtp.gmail.com',
					'port' => 587,
					'connection_class' => 'login',
					'connection_config' => array(
						'username' => 'yourgmailaddress',
						'password' => 'yourgmailpassword',
						'ssl' => 'tls'
					),
				),
			),
		),
	);
?>
```
and save it.

Now go and login to:
```
http://localhost/phpmyadmin
```
Click on import and select:
```
path/to/project/database/silmaring.sql
```
When you go to:
```
http://eksamikeskkond.silmaring.dev
```
You should have website working.
```
http://eksamikeskkond.silmaring.dev
```
