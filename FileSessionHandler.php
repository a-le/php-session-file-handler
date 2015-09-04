<?php
/**
 * User: a-le
 * Date: 2013-11-16
 * version : 1.0
 * PHP >= 5.4
 * File based session storage, with no session locking.
 * PHP internal session handler do session locking.
 *
 * This script is based on php example on how to implement SessionHandlerInterface see http://php.net/manual/en/class.sessionhandlerinterface.php
 * with a notable difference :
 * - we do not write data back to file when data did not change
 *
 * Objectives :
 * ------------
 * When loading images (and other static content) the browser may decide to use multiple TCP connections (concurrent) to get the data as fast as possible.
 * This also happens when using AJAX.
 * This may (and will most likely) lead to different workers (threads) on the web server answering these concurrent requests concurrently.
 *
 * The objective of this script is to improve speed for those cases of concurrent requests.
 * The drawback is race conditions may happen if you need to write data to session.
 *
 *
 * References :
 * ------------
 * - http://php.net/manual/en/function.session-set-save-handler.php
 * - about session locking : https://www.leaseweblabs.com/2013/10/symfony2-memcache-session-locking/
 * - about race conditions : http://thwartedefforts.org/2006/11/11/race-conditions-with-ajax-and-php-sessions/
 *
 *
 * How to use :
 * -------------
 * session_set_save_handler( new FileSessionHandler(), true );
 * session_start();
 *
 * // use session as usual...
 *
 */
class FileSessionHandler implements \SessionHandlerInterface {
    private $savePath, $data;

    public function open($savePath, $sessionName) {
        $this->savePath = $savePath;
        if ( !is_dir($this->savePath) ) {
            mkdir($this->savePath, 0777);
        }
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
            // write data
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
?>
