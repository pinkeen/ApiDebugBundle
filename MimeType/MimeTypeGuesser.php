<?php

namespace Pinkeen\ApiDebugBundle\MimeType;

/**
 * We cannot use Symfony's mime-type guesser
 * because it works on files and we need to work
 * on buffer.
 */
class MimeTypeGuesser
{
    /**
     * @var MimeTypeGuesser
     */
    private static $instance = null;

    /**
     * @var \finfo
     */
    private $finfo = null;

    protected function __construct()
    {
        if(function_exists('finfo_open')) {
            $this->finfo = new \finfo(FILEINFO_MIME_TYPE);
        }
    }

    /**
     * Returns guessed mimetype or false if not possible.
     *
     * @param string $buffer
     * @return string|false
     */
    public function guess($buffer) 
    {
        if(null !== $this->finfo) {
            $mime = $this->finfo->buffer($buffer);

            /* If text/plain then keep guessing because 
             * finfo does not recognize json or xml if does
             * not contain <?xml ... */
            if(false !== $mime && $mime !== 'text/plain') {
                return $mime;
            }
        }

        /* Resort to dirty tricks... :) */

        if(!ctype_print($buffer)) {
            return 'application/octet-stream';
        }

        if($buffer[0] == '{') {
            return 'application/json';
        }

        if(mb_substr($buffer, 0, 5) == '<?xml') {
            return 'application/xml';
        }

        if(mb_strtolower(mb_substr($buffer, 0, 5)) == '<html') {
            return 'text/html';
        }        

        if(mb_strtolower(mb_substr($buffer, 0, 9)) == '<!doctype') {
            return 'text/html';
        }

        return 'text/plain';
    }

    public static function getInstance()
    {
        if(null === self::$instance) {
            self::$instance = new MimeTypeGuesser();
        }

        return self::$instance;
    }
}