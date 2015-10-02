Developer's Notebook
====================

Developer's Notebook is a tool created by a developer dissatisfied with other organizational tools on offer. It is designed to enhance a developer's workflow by giving them all of the common organizational tools necessary to get their job done in a single location.

It was created as a Symfony2-based PHP web application rather than a native program because developers often use multiple platforms and want access to their information from anywhere. PHP offers the flexibility to run on just about any server environment and is easily understandable and customizable. Its web-based nature also makes it easier for development teams to take advantage of.

The project is still in its infancy, but already has a basic set of usable functions that can be taken advantage of.


Requirements
------------

- PHP 5.3.9 or higher

For more specific requirements, see http://symfony.com/doc/current/reference/requirements.html

Installation Instructions
-------------------------

Before you get started with either a development environment or a production environment, you will need to configure the application to use a database, then populate it with the correct tables.

To create a database perform the following steps:

1. Browse to app/config and make a copy of the "parameters.yml.dist" file. Name it "parameters.yml".
2. Change the parameters to connect to your database.
3. Choose a name for your database and add it here.
4. Browse to the root of Developer's Notebook and run the following command to create your database:

```bash
php app/console doctrine:database:create
```

Once you have done that, you can have Developer's Notebook automatically populate the database with the necessary tables:

1. Browse to the root of Developer's Notebook.
2. Run the following command:

```bash
php app/console doctrine:schema:update --force
```

For more information about Symfony2/Doctrine and databases, see Symfony2's documentation:

http://symfony.com/doc/current/book/doctrine.html

Now you have setup the database and can proceed with the environment-specific steps.


### Development Environment

Since PHP 5.4 or later comes with a built-in server, it is recommended to use this during the development stage.

Simply navigate to the root directory of Developer's Notebook and run the following command:

```bash
php app/console server:start
```

To stop the server, run:

```bash
php app/console server:stop
```

For more details, see Symfony2's documentation:

http://symfony.com/doc/current/cookbook/web_server/built_in.html


### Production Environment

Setting up a production environment is slightly different than a development environment. Apache, Nginx or other production-ready web servers should be used rather than PHP's built-in server.

To see how to setup your production server, see Symfony2's documentation:

http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html