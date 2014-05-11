<?php

namespace Pinkeen\ApiDebugBundle\MimeType;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser as SymfonyMimeTypeGuesser;

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
     * @var SymfonyMimeTypeGuesser
     */
    private $symfonyGuesser = null;

    protected function __construct()
    {
        $this->symfonyGuesser = SymfonyMimeTypeGuesser::getInstance();
    }

    /**
     * Returns guessed mimetype or false if not possible.
     *
     * @param string $filename
     * @return string|false
     */
    public function guess($filename) 
    {
        $mime = $this->symfonyGuesser->guess($filename);

        /* If text/plain then keep guessing because 
         * finfo does not recognize json or xml if does
         * not contain <?xml ... */
        if(false !== $mime && $mime !== 'text/plain') {
            return $mime;
        }

        /* Resort to dirty tricks... :) */

        $file = fopen($filename, 'r');

        /* Read only the first 2KiB not to waste mem */
        $buffer = trim(fread($file, 2048));

        fclose($file);

        if($buffer[0] == '{' || $buffer[0] == '[') {
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

        if(preg_match('/^[\w&%\=]*([\w%]+\=[\w%]+)[\w&%\=]*$/', $buffer)) {
            return 'application/x-www-form-urlencoded';
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