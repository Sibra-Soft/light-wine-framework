<p align="center" >
  <img src="https://www.moviedos.nl/images/light_wine_logo.png" style="width:128px;" />
</p>

# LightWine Framework
The LightWine framework is a PHP framework that can be used for creating websites and webapplications. The framework has various useful functions and classes that make it easy creating websites.

## Getting started
* [Installation]()

## Features
* [Database connection]()
* [Helpers]()
* [Templates service]()
* [Templating engine]()
* [Caching]()
* [Routing]()
* [Components]()

## Application configuration file
Below you find a example of a configuration file for a website/webapplication created with the framework

```json 
{
  "domain":"exmaple.com" // The main domain name of your website/webapplication,
  "connections": {
    // Here you can add connectionstring of your databases
  },
  "cache_folder": "~/cache/", // Specifies the location of the cache folder
  "environment": "dev", // Specifies the current enviroment (dev, test or live)
  "tracing": false, // Enables the tracing of the framework for easy debugging
  "log_traffic": true, // Logs website visitor details to a file
  "create_debug_log": false, // Creates all log entries including debug log entries
  "log_database": false, // Enables logging to the database instead of a file
  "gzip_encode": true, // Enables gzip compression for all content provided by the framework
  "smtp": {
    "host": "smtp.example.com", // Hostname of your smtp server
    "port": 587, // Port number of your smtp server
    "username": "example@example.com", // Username of your smtp server
    "password": "", // Password of your smtp server
    "from_name": "Exmaple", // The name you want to use to send the mail
    "from_address": "example@example.com" // The emailaddress you want to use to send the mail
  }
}
```
