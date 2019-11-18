# LIVE

A Webapp Like https://jindouyun.tv

## Deployment Guide

***System: Ubuntu 18.04 LTS***

#### Update
`# apt-get update && apt-get upgrade`

#### Install MySQL@5.7+
`# apt-get install mysql-server`

#### Install php@7.2+
`# apt-get install php-cli php-fpm php-common php-xml php-json php-mbstring php-tokenizer php-zip php-mysql`

#### Install Composer
`# apt-get install composer`

#### Install Nginx
`# apt-get install nginx`

#### Get Code
`# mkdir /home/webapps`

`# cd /home/webapps && git clone https://github.com/live666/live.git`

#### Checkout develop branch
`# cd /home/webapps/live && git checkout develop`

`# chown -R www-data:www-data /home/webapps/live`

`# chmod -R 755 /home/webapps/live/storage`

#### Import Database Tables
`# tar -zxvf live.sql.tar.gz`

`# mysql -uusername -p -e 'create database live'`

`# mysql -uusername -p live < live.sql`

#### Install Dependency
`# cd /home/webapps/live && composer install`

#### Copy .env
`# cd /home/webapps/live && cp .env.example .env`

#### Nginx config
`# vi /etc/nginx/sites-available/live.conf`

```
server {
    listen       80;

    root /home/webapps/live/public;
    access_log  /var/log/nginx/live.access.log;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/run/php/php7.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
 
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

`# ln -s /etc/nginx/sites-available/live.conf /etc/nginx/sites-enabled/live.conf`

`# rm  /etc/nginx/sites-enabled/default`

#### Starting The Scheduler
`# sudo crontab -u www-data -e`

```
* * * * * cd /home/webapps/live && php artisan schedule:run >> /dev/null 2>&1
```

#### Start Services
`# service mysql start`

`# service php7.2-fpm start`

`# service nginx start`

## Finished
