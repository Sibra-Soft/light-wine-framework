<p align="center" >
  <img src="https://www.sibra-soft.nl/images/light_wine_logo.png" style="width:128px;" />
</p>

# LightWine Framework
The LightWine framework is a PHP framework that can be used for creating websites and webapplications. The framework has various useful functions and classes that make it easy creating websites.

## Getting started
* [Installation](https://github.com/Sibra-Soft/light-wine-framework/wiki)

## Features
* [Database Service](https://github.com/Sibra-Soft/LightWineFramework/wiki/Database-Service)
* [Data Bindings](https://github.com/Sibra-Soft/light-wine-framework/wiki/Data-Bindings)
* [Helpers](https://github.com/Sibra-Soft/LightWineFramework/wiki/Helpers)
* [Template Service](https://github.com/Sibra-Soft/LightWineFramework/wiki/Template-Service)
* [Templating Engine Service](https://github.com/Sibra-Soft/LightWineFramework/wiki/Templating-Engine-Service)
* [Cache Service](https://github.com/Sibra-Soft/LightWineFramework/wiki/Cache-Service)
* [Routing Service](https://github.com/Sibra-Soft/LightWineFramework/wiki/Routing-Service)
* [Components](https://github.com/Sibra-Soft/LightWineFramework/wiki/Components)

## Application configuration file
Below you find a example of a configuration file for a website/webapplication created with the framework

```json 
{
  "Name": "Exmaple Project", // The name of the project
  "Domain": "exmaple.com", // The main domain name of your website/webapplication,
  "Connections": {
    // Here you can add connectionstring of your databases
  },
  "AutoUpdate": false, // Specifies if the framework must be automatically updated
  "CacheFolder": "~/cache/", // Specifies the location of the cache folder
  "Environment": "dev", // Specifies the current enviroment (dev, test or live)
  "Tracing": false, // Enables the tracing of the framework for easy debugging
  "LogTraffic": true, // Logs website visitor details to a file
  "CreateDebugLog": false, // Creates all log entries including debug log entries
  "LogDatabase": false, // Enables logging to the database instead of a file
  "GzipEncode": true, // Enables gzip compression for all content provided by the framework
  "Smtp": {
    "Host": "smtp.example.com", // Hostname of your smtp server
    "Port": 587, // Port number of your smtp server
    "Username": "example@example.com", // Username of your smtp server
    "Password": "", // Password of your smtp server
    "FromName": "Exmaple", // The name you want to use to send the mail
    "FromAddress": "example@example.com" // The emailaddress you want to use to send the mail
  }
}
```
