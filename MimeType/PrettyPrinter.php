<?php

namespace Pinkeen\ApiDebugBundle\MimeType;

/**
 * Takes data and it's mimetype and tries to print it
 * nicely using HTML.
 */
class PrettyPrinter
{
    /**
     * @var PrettyPrinter
     */
    private static $instance = null;

    protected function __construct()
    {
        if(function_exists('finfo_open')) {
            $this->finfo = new \finfo(FILEINFO_MIME_TYPE);
        }
    }

    /**
     * Returns buffer pretty printed in HTML.
     *
     * Returns false on failure.
     *
     * @param string $buffer
     * @param string $mimeType
     * @return string|false
     */
    public function prettify($buffer, $mimeType) 
    {
        switch($mimeType) {
            case 'application/json':
            case 'application/x-javascript':
            case 'text/javascript':
            case 'text/x-javascript':
            case 'text/x-json':
                return $this->printJson($buffer);

            case 'application/xml':
            case 'text/xml':
                return $this->printXml($buffer);

            case 'text/html':
                return $this->printHtml($buffer);  
            case 'application/x-www-form-urlencoded':
                return $this->printFormData($buffer);
            case '...images..': /* Will be displayed using base64 :) */
        }   

        if($mimeType === 'text/plain') {
            return $buffer;
        }

        return false;
    }

    /**
     * @param string $code
     * @param string $lang
     * @return string
     */
    protected function printCode($code, $lang = null) 
    {
        $code = htmlspecialchars($code);

        if(null !== $lang) {
            return "<pre class=\"code\" data-lang=\"{$lang}\">{$code}</pre>";
        } 

        return "<pre class=\"code\">{$code}</pre>";
    }

    /**
     * @param string $buffer
     */
    protected function printJson($buffer)
    {
        if(false !== $data = json_decode($buffer)) {
            if(false !== $pretty = json_encode($data, JSON_PRETTY_PRINT)) {
                return $this->printCode($pretty, 'json');
            }
        }

        return $this->printCode($buffer);
    }    

    /**
     * @param string $buffer
     */
    protected function printXml($buffer)
    {
        if(!extension_loaded('dom')) {
            return $this->printCode($buffer);   
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        if(false !== $dom->loadXML($buffer)) {
            return $this->printCode($dom->saveXML(), 'xml');
        }

        return $this->printCode($buffer);
    }

    /**
     * @param string $buffer
     */
    protected function printHtml($buffer)
    {
        if(!extension_loaded('dom')) {
            return $this->printCode($buffer);   
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        if(false !== $dom->loadHTML($buffer)) {
            return $this->printCode($dom->saveHTML(), 'html');
        }

        return $this->printCode($buffer);
    }    

    /**
     * @param string $buffer
     */
    protected function printFormData($buffer)
    {
        parse_str($buffer, $data);

        return $this->printCode(print_r($data, true));
    }

    public static function getInstance()
    {
        if(null === self::$instance) {
            self::$instance = new PrettyPrinter();
        }

        return self::$instance;
    }
}