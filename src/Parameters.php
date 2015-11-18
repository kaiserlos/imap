<?php

namespace Ddeboer\Imap;

use Ddeboer\Transcoder\Transcoder;
use Ddeboer\Transcoder\Exception\IllegalCharacterException;

class Parameters
{
    protected $parameters = [];
    
    public function __construct(array $parameters = [])
    {
        $this->add($parameters);
    }
    
    public function add(array $parameters = [])
    {
        foreach ($parameters as $parameter) {
            $key = strtolower($parameter->attribute);
            $value = $this->decode($parameter->value);
            $this->parameters[$key] = $value;
        }
    }
    
    public function get($key)
    {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }
        
        return null;
    }
    
    protected function decode($value)
    {
        $decoded = '';
        $parts = imap_mime_header_decode($value);
        foreach ($parts as $part) {
            $charset = 'default' == $part->charset ? 'auto' : $part->charset;
            // imap_utf8 doesn't seem to work properly, so use Transcoder instead
            
            // Got from: https://github.com/Sawered/imap/commit/e739b7221c6e57521b38f7b56f78ba399acda888
            try{
                $decoded .= Transcoder::create()->transcode($part->text, $charset);
            } catch(IllegalCharacterException $e){
                //no warn, it is reality, handle it somehow
                $decoded = imap_utf8($part->text);
            }
        }
        
        return $decoded;
    }
}
