<?php

namespace SEOPressPro\Services\StopWords;

class StopWords
{
    public function __construct($languages = [])
    {
        $this->setLanguages($languages);
    }

    public function setLanguages($languages = []){
        $this->languages = $languages;
        $this->setStopWords();
        return $this;
    }

    public function setStopWords(){
        $finder = new FinderStopWords();
        $this->words = [];

        foreach ($this->languages as $key => $value) {
            $this->words = array_merge($this->words, $finder->find($value));
        }
    }

    public function clean($message)
    {
        $message = $this->sanitize($message);

        $iterable = preg_split("/\s+/", $message);
        foreach ($iterable as $pos => $item) {
            if (in_array(mb_strtolower($item), $this->words) || strlen(trim($item)) === 0) {
                unset($iterable[$pos]);
            }
        }

        return implode(' ', $iterable);
    }

    private function sanitize($message)
    {
        return preg_replace("/[^a-zA-Z0-9\s]/", " ", $message);
    }


}
