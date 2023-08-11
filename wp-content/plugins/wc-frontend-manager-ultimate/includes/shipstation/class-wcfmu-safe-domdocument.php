<?php

/**
 * Drop in replacement for DOMDocument that is secure against XML eXternal Entity (XXE) Injection.
 * Bails if any DOCTYPE is found
 *
 * Comments in quotes come from the DOMDocument documentation: http://php.net/manual/en/class.domdocument.php
 */
class WCFMu_Safe_DOMDocument extends DOMDocument
{


    /**
     * When called non-statically (as an object method) with malicious data, no Exception is thrown, but the object is emptied of all DOM nodes.
     *
     * @since 1.0.0
     *
     * @param string  $filename The path to the XML document.
     * @param integer $options  Bitwise OR of the libxml option constants. http://us3.php.net/manual/en/libxml.constants.php
     *
     * @return boolean|DOMDocument true on success, false on failure.  If called statically (E_STRICT error), returns DOMDocument on success.
     */
    public function load($filename, $options=0)
    {
        if ('' === $filename) {
            // "If an empty string is passed as the filename or an empty file is named, a warning will be generated."
            // "This warning is not generated by libxml and cannot be handled using libxml's error handling functions."
            trigger_error('WCFMu_Safe_DOMDocument::load(): Empty string supplied as input', E_USER_WARNING);

            return false;
        }

        if (! is_file($filename) || ! is_readable($filename)) {
            // This warning probably would have been generated by libxml and could have been handled handled using libxml's error handling functions.
            // In WCFMu_Safe_DOMDocument, however, we catch it before libxml, so it can't.
            // The alternative is to let file_get_contents() handle the error, but that's annoying.
            trigger_error('WCFMu_Safe_DOMDocument::load(): I/O warning : failed to load external entity "'.$filename.'"', E_USER_WARNING);

            return false;
        }

        if (is_object($this)) {
            return $this->loadXML(file_get_contents($filename), $options);
        } else {
            // "This method *may* be called statically, but will issue an E_STRICT error."
            return self::loadXML(file_get_contents($filename), $options);
        }

    }//end load()


    /**
     * When called non-statically (as an object method) with malicious data, no Exception is thrown, but the object is emptied of all DOM nodes.
     *
     * @since 1.0.0
     *
     * @param string  $source  The string containing the XML.
     * @param integer $options Bitwise OR of the libxml option constants. http://us3.php.net/manual/en/libxml.constants.php
     *
     * @return boolean|DOMDocument true on success, false on failure.  If called statically (E_STRICT error), returns DOMDocument on success.
     */
    public function loadXML($source, $options=0)
    {
        if ('' === $source) {
            // "If an empty string is passed as the source, a warning will be generated."
            // "This warning is not generated by libxml and cannot be handled using libxml's error handling functions."
            trigger_error('WCFMu_Safe_DOMDocument::loadXML(): Empty string supplied as input', E_USER_WARNING);
            return false;
        }

        $old = null;

        if (function_exists('libxml_disable_entity_loader')) {
            $old = libxml_disable_entity_loader(true);
        }

        $return = parent::loadXML($source, $options);

        if (! is_null($old)) {
            libxml_disable_entity_loader($old);
        }

        if (! $return) {
            return $return;
        }

        // "This method *may* be called statically, but will issue an E_STRICT error."
        $is_this = is_object($this);

        $object = $is_this ? $this : $return;

        if (isset($object->doctype)) {
            if ($is_this) {
                // Get rid of the dangerous input by removing *all* nodes
                while ($this->firstChild) {
                    $this->removeChild($this->firstChild);
                }
            }

            trigger_error('WCFMu_Safe_DOMDocument::loadXML(): Unsafe DOCTYPE Detected', E_USER_WARNING);

            return false;
        }

        return $return;

    }//end loadXML()


}//end class
