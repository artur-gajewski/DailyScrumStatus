DailyScrumStatus
================

Web app to save daily scrum information for team members. Minimal setup and no database needed.

Feature displays for developer's computer as well as office wide big screens.

Demo: http://www.arturgajewski.com/daily

## Installation

Obtain Composer:

	$ curl -s https://getcomposer.org/installer | php

Install all needed third party components:

	$ php composer.phar install

Make database folder accessible by the application:

	$ sudo chmod -Rf +a "daemon allow delete,write,append,file_inherit,directory_inherit" database
	$ sudo chmod -Rf +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" database

Fire up the app!
