<?php
/**
 * Created by PhpStorm.
 * User: stlui
 * Date: 3/26/2017
 * Time: 12:41 PM
 */

namespace App\ViewModel;


class ArrayResult extends ParsableObject
{
    /**
     * Create a new instance.
     *
     * @param mixed $elementClass The class of the array element
     *
     * @return void
     */
    public function __construct($elementClass)
    {
        $this->value = [];
        $this->mappingTable =
            [
                "nextLink" => "odata.nextLink"
            ];
        $this->elementType = $elementClass;
    }

    /**
     * Parse json data to the object
     *
     * @param string $json The json data
     *
     * @return void
     */
    public function parse($json)
    {
        parent::parse($json);
        $this->skipToken = $this->getSkipToken();

        if (array_key_exists('value', $json))
        {
            $values = $json['value'];
            if ($values && is_array($values))
            {
                foreach ($values as $obj)
                {
                    $targetObj = new $this->elementType();
                    $targetObj->parse($obj);
                    $this->value[] = $targetObj;
                }
            }
        }
    }

    /**
     * Get the skip token from the nextLink property
     *
     * @return string The skip token
     */
    private function getSkipToken()
    {
        $pattern = '/\$skiptoken=([^&]+)/';
        $match = [];
        preg_match($pattern, $this->nextLink, $match);
        if (count($match) == 2)
        {
            return $match[1];
        }
        return '';
    }

    public $value;
    public $skipToken;
    public $nextLink;
    private $elementType;
}