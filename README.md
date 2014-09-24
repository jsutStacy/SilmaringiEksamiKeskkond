SilmaringiEksamiKeskkond
========================

Loodud Tarkvaraprojekti aine (MTAT.03.138) raames Tartu Ülikoolis 2014 aastal.

Project Setup:
========================

First download and install XAMPP:
https://www.apachefriends.org/download.html

Then clone our project from git.

Now go to path/to/project/vhosts and make new folder named after your first name in lowercase.
Now we have to make our own vhost file. Name it vhost.conf.

It should look something like this:

<VirtualHost 127.0.0.2:80>
	ServerName yoururl // mine is eksamikeskond.silmaring.dev
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

Now we have to include it to apache conf.

Go to path/to/xampp/apache/conf and open httpd.conf.
At the end of conf file add line:
Include "path/to/project/vhosts/yourfoldername/vhost.conf"

Now go to C:/Windows/System32/drivers/etc and open hosts file.
Add:
127.0.0.2		yoururl
Mine is:
127.0.0.2   silmaring.eksamikeskkond.dev

Then save it and restart apache from xampp.

Now when you open your web browser and go to your url you should see the zend framework 2 opening screen.

For database connection make new local config file.
Go to path/to/project/config/autoload and make new php file called yourfirstname.local.php. Mine is andres.local.php
and add this into it:
<?php
	return array(
		'db' => array(
			'username' => 'yourmysqlusername',
			'password' => 'yourmysqlpassword',
		),
	);
?>
and save it.

When you go to:
http://eksamikeskkond.silmaring.dev/test
It should give errors about missing database and missing table.
Don't mind it at the moment.
