# 2B Test task installation

1. Clone project from GitHub
> git clone https://github.com/digitalmamkin/2BTestTask.git

2. Go to ther project folder 
> cd 2BTestTask

3. Run installing of composer packages 
> composer install

4. Run installing of npm packages
> npm install

5. Create ENV config file from example 
> cp .env.example .env

6. Generate new application key
> php artisan key:generate

7. Create new MySQL DB from project

8. Edit DB options in ENV config. Run
> nano .env

9. Fill options with your actual values

>DB_CONNECTION=mysql\
>DB_HOST=127.0.0.1\
>DB_PORT=3306\
>DB_DATABASE=laravel\
>DB_USERNAME=root\
>DB_PASSWORD=

10. Run Project migrations
> php artisan migrate

11. Run Project seeder
> php artisan db:seed

## Artisan commands for scrapping

Read description here
> php artisan parse -h

Examples:
>php artisan parse 1 false 10\
>php artisan parse 2 false 10\
>php artisan parse 3 false 10\
>php artisan parse 4 false 10\
>php artisan parse 5 false 10

## Logs
Every blog scrapper have individual log file. While artisan command in working, you can check it here: **/storage/logs**

## Vue UI

1. Run in first terminal window
> php artisan serve

2. Run in second terminal window
> npm run dev

## Congratulations
Open address in our browser by URL from "artisan serve" terminal



