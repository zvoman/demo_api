# Demo_api

## Requisites
- Debian 10, 
- PHP 7.3, 
- Apache 2.4
- Postgres 11

## Installing

 - clone repo
 - install composer from https://getcomposer.org/download/ then composer install.
 - "composer install" in folder where you want application
 
#### create web server localy:

- cd /etc/apache2/sites-available
- sudo nano "server_name".conf

create virtual host

    <VirtualHost *:80>
        ServerName api_demo
        ServerAlias api_demo
    
        DocumentRoot /path to symfony folder/public
        <Directory /path to symfony folder/public>
            AllowOverride All
            Require all granted
            Allow from All
        </Directory>
    
        ErrorLog /var/log/apache2/log_file_name.log
        CustomLog /var/log/apache2/log_file_name.log combined
    </VirtualHost>

- if there is enabled web server from before first sudo a2dissite "old_server_name".conf 
- sudo a2ensite "new_server_name".conf
- sudo a2enmod rewrite
- restart apache2: sudo service apache2 reload
- if using windows open notepad with administrator privileges and then open  Windows\System32\drivers\etc\hosts.etc
- add new line 127.0.0.1 new_server_name

## Database

- create database user
- create .env.local and copy DATABASE_URL from .env, change db_user, db_password and set db_name
- create database with "php bin/console doctrine:database:create"
- migrate SQL with "php bin/console doctrine:migrations:migrate"

## Data

- data is from  http://jsonplaceholder.typicode.com/
- data can be fetched manualy with commands php bin/console app:fetch-users and app-fetch-posts ot with cron jobs that call this methos
- example of cron-job: */5 * * * * "user that will call cron job" php /"path to bin/console"/bin/console app:fetch-posts


## API

- api will be visible on "new_server_name/api
- showing only GET for Post and User data
- /api/users - order by name and username
- /api/users/{id}/posts - get posts for just one user
- /api/posts - order by title, filter by user.username and user.id
