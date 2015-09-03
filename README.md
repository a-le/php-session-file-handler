# php-session-file-handler

This script is based on php example on how to implement SessionHandlerInterface see http://php.net/manual/en/class.sessionhandlerinterface.php
with a notable difference :
- we do not write data back to file when data did not change

Objectives :
------------
When loading images (and other static content) the browser may decide to use multiple TCP connections (concurrent) to get the data as fast as possible.
This also happens when using AJAX.
This may (and will most likely) lead to different workers (threads) on the web server answering these concurrent requests concurrently.

The objective of this script is to improve speed for those cases of concurrent requests.
The drawback is race conditions may happen if you need to write data to session.


References :
------------
- http://php.net/manual/en/function.session-set-save-handler.php
- about session locking : https://www.leaseweblabs.com/2013/10/symfony2-memcache-session-locking/
- about race conditions : http://thwartedefforts.org/2006/11/11/race-conditions-with-ajax-and-php-sessions/

 
 How to use :
 -------------
 ```
 require('FileSessionHandler.php');
 session_set_save_handler( new FileSessionHandler(), true );
 session_start();
 // then use php session functions like you usually do...
```
