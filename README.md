SilmaringiEksamiKeskkond
========================

Loodud Tarkvaraprojekti aine (MTAT.03.138) raames Tartu Ülikoolis 2014 aastal.

Project Setup:
========================

Get python version 3.4.1(I got the 32-bit version) from https://www.python.org/downloads/. During your installation make sure you instal/check "Add python.exe to Path" and "pip". They should be in the same menu.

Next we're going to install a database. Go to http://www.enterprisedb.com/products-services-training/pgdownload#windows and install Postgres 9.3.5.1. Set postgres/postgres as username/password and leave the port to 5432. There is no need to do anything with StackBuilder at the end of the installation. Now open PgAdmin and create a new user role(right click PostgresSQL 9.3(localhost:5432) - New object - New login role. Set username/password as silmaring/silmaring and check the superuser checkbox.
Right click "Databases" - New Database..  Name it also "silmaring" and set the owner to silmaring.

* virtualenv
On command-line go to the direction you want to install the virtualenvironment and run
"python -m venv virtuanenv"
This will create the virtual environment folder named "virtuanenv". Activate it by using the script in you new virtual-environment folder "virtuanenv\Scripts\activate". You should now see a name of the virtualenv in you commandline in brackets

Now we need to install pcyopg2. Download the .exe file from http://stickpeople.com/projects/python/win-psycopg/. While you have your virtualenv activated in cmd run 
"easy_install psycopg2-2.5.4.win32-py3.4-pg9.3.5-release.exe"

Do a git pull

Now run these commands:
pip install --upgrade -r requirements.txt
python manage.py syncdb
python manage.py migrate
python manage.py runserver 0.0.0.0:7000

After this if everything went as planned, you should have a server running and be able to access this url:
http://localhost:8000/api/randomthing/randomthing/.

If you see the form and can enter new persons then everything should be working
