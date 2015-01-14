<?php
namespace Pmp\Sdk;

use Guzzle\Parser\UriTemplate\UriTemplate;

/**
 * PMP CollectionDoc link
 *
 * A single, follow-able CollectionDoc link
 *
 */
class CollectionDocJsonLink
{
    const PMP_AND = ',';
    const PMP_OR  = ';';

    private $_link;
    private $_auth;

    /**
     * Constructor
     *
     * @param stdClass $link the raw link data
     * @param AuthClient $auth authentication client for the API
     */
    public function __construct(\stdClass $link, AuthClient $auth = null) {
        $this->_link = $link;
        $this->_auth = $auth;

        // set properties
        $props = get_object_vars($link);
        foreach ($props as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * Expand this link into a complete url
     *
     * @param array $options optional array of href-template params
     * @return string the complete url
     */
    public function expand(array $options = null) {
        if (!empty($this->href)) {
            return $this->href;
        }
        else if (!empty($this->{'href-template'})) {
            $parser = new UriTemplate();
            return $parser->expand($this->{'href-template'}, $this->_convertOptions($options));
        }
        else {
            $e = new Exception('Cannot expand link because no href or href-template defined');
            $e->setDetails($this->_link);
            throw $e;
        }
    }

    /**
     * Follow the link href to retrieve a document
     *
     * @param array $options optional array of href-template params
     * @return CollectionDocJson a loaded document
     */
    public function follow(array $options = null) {
        $url = $this->expand($options);
        return new CollectionDocJson($url, $this->_auth);
    }

    /**
     * Follow the link href to retrieve a document
     *
     * @param array $options array of href-template params
     * @return CollectionDocJson a loaded document
     */
    public function submit(array $options) {
        return $this->follow($options);
    }

    /**
     * Get the available options for an href-template
     *
     * @return Object options object
     */
    public function options() {
        if (empty($this->{'href-template'}) || empty($this->{'href-vars'})) {
            $e = new Exception('Cannot give link options because link is not a properly defined href template');
            $e->setDetails($this->_link);
            throw $e;
        }
        else {
            return $this->{'href-vars'};
        }
    }

    /**
     * Converts the set of options into API-compatible query string forms.
     *
     * Mainly to convert:
     *     array('profile' => array('AND' => array('foo', 'bar')))
     * into:
     *     array('profile' => 'foo,bar')
     *
     * @param array $options
     * @return array
     */
    private function _convertOptions(array $options = null) {
        $converted = array();
        if (!empty($options)) {
            foreach ($options as $name => $value) {
                if (is_array($value)) {
                    if (!empty($option['AND'])) {
                        $converted[$name] = implode(self::PMP_AND, $options['AND']);
                    }
                    else if (!empty($option['OR'])) {
                        $converted[$name] = implode(self::PMP_OR, $options['OR']);
                    }
                    else {
                        $converted[$name] = ''; // bad params
                    }
                }
                else {
                    $converted[$name] = $value;
                }
            }
        }
        return $converted;
    }

}
