<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/


namespace Tygh\Commerceml\Dto\Warehouses;


class Warehouse
{
    /** @var string */
    protected $uid;

    /** @var string */
    protected $name;

    /** @var int|null */
    protected $id;

    /** @var string */
    protected $city = '';

    /** @var string */
    protected $address = '';

    /**
     * Warehouse constructor.
     *
     * @param string $uid
     * @param string $name
     * @param string $address
     */
    protected function __construct($uid, $name, $address)
    {
        $this->uid = (string) $uid;
        $this->name = (string) $name;
        $this->address = (string) $address;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $warehouse_uid
     * @param string $name
     * @param string $address
     *
     * @return \Tygh\Commerceml\Dto\Warehouses\Warehouse
     */
    public static function create($warehouse_uid, $name, $address)
    {
        return new self($warehouse_uid, $name, $address);
    }
}