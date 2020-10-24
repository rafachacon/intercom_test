# Intercom test app

## Introduction

This is the backend of the application, built in Symfony 4 as a RESTFUL API.

In the following, PATH_TO_APP will be the local path where you will test this application. Replace it by the name of your choice.

Once installed, the API documentation can be found [here](http://PATH_TO_APP/public/index.php/api/doc).

## Install

I will assume we're working in a Linux machine. The installation processes for Windows or Mac are very 
similar.

* Clone the repository

And get to the project directory.

* Install the dependencies

`composer install`

This will make the `vendor` directory appear.

* Create the database

Once the application is installed, create the database schema.

To do this, copy the `.env` files by adding `.env.local` and `.env.test`. setting the correct database name and credentials for each environment:

`DATABASE_URL=mysql://bb_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7`

Please, note that the databases must exist.

Then, in console, run the script to create the schema:

`php bin/console doctrine:schema:create --env=local`

or

`php bin/console doctrine:schema:create --env=test` for the test enviroment. 

And that's it.

## Unit and functional tests

To run the test, in console, run

`php bin/phpunit`

You may receive some deprecation notices. This is on TODO list.

## Using the API

The API documentation is in

`http://PATH_TO_APP/public/index.php/api/doc`

You can use a REST client like Postman to use the API.


