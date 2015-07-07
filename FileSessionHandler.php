<?php
/**
 * User: a-le
 * Date: 2013-11-16
 * File based session storage, with no LOCK_EX on file (opposite of PHP internal session handler).
 * The objective is to improve speed for cases of concurrent session read access, as PHP default session handler is sequential.
 * read session data from file (1 file by user session), and write it when modified
 * note that as we don't set LOCK_EX on file between reading and writing :
 *    in case of concurrent access & modification to the same user session data, overwrite will happen
 *   ( AFAIK database based sessionHandlers like memcache, memcached  get that same behaviour )
 *
 * How to use :
 * -------------
 * session_set_save_handler( new FileSessionHandler(), true );
 * session_start();
 * // use session as usual...
 */
class FileSessionHandler implements SessionHandlerInterface {
    private $savePath, $data;

    // this does not create $savePath if not exists
    public function open($savePath, $sessionName) {
        $this->savePath = $savePath;
        return true;
    }

    public function close() {
        return true;
    }

    public function read($id) {
        $this->data = false;
        $filename = $this->savePath.'/sess_'.$id;
        if ( file_exists($filename) ) $this->data = @file_get_contents($filename);
        if ( $this->data === false ) $this->data = '';

        return $this->data;
    }

    public function write($id, $data) {
        $filename = $this->savePath.'/sess_'.$id;

        // check if data has changed since first read
        if ( $data !== $this->data ) {
            // write data (even if actual file session data is not the same as when we first read it)
            return @file_put_contents($filename, $data, LOCK_EX) === false ? false : true;
        }
        else return @touch($filename);// let's not forget to postpone session garbage collection
    }

    public function destroy($id) {
        $filename = $this->savePath.'/sess_'.$id;
        if ( file_exists($filename) ) @unlink($filename);

        return true;
    }

    // garbage collection, delete obsolete session files
    public function gc($maxlifetime) {
        foreach ( glob($this->savePath.'/sess_*') as $filename ) {
            if ( filemtime($filename) + $maxlifetime < time() && file_exists($filename) ) {
                @unlink($filename);
            }
        }

        return true;
    }
}
