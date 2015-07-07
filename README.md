# php-session-file-handler
File based session storage, wich permits concurrent access. No LOCK_EX on file (opposite of PHP internal session handler).

The objective is to improve speed for cases of concurrent session read access, as PHP default session handler is sequential.
- read session data from file (1 file by user session), and write it when modified.
- please note that, as we don't set LOCK_EX on file between reading and writing :
-   in case of concurrent access & modification to the same user session data, overwrite will happen
-   ( AFAIK database based sessionHandlers like memcache, memcached  get that same behaviour ).
 
 How to use :
 -------------
 ```
 session_set_save_handler( new FileSessionHandler(), true );
 session_start();
 // then use php session functions like you usually do...
```
