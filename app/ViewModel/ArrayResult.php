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
     * @return void
     */
    public function __construct()
    {
        $this->values = [];
        $this->mappingTable =
            [
                "nextLink" => "odata.nextLink"
            ];
    }

    public $values;
    public $nextLink;
}