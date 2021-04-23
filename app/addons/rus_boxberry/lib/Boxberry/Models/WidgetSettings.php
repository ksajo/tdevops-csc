<?php
/**
 *
 *  * This file is part of Boxberry Api.
 *  *
 *  * (c) 2016, T. I. R. Ltd.
 *  * Evgeniy Mosunov, Alexander Borovikov
 *  *
 *  * For the full copyright and license information, please view LICENSE
 *  * file that was distributed with this source code
 *  *
 *  * File: WidgetSettings.php
 *  * Created: 26.07.2016
 *  *
 */

namespace Boxberry\Models;

/**
 * Class Point
 * @package Boxberry\Models
 */
class WidgetSettings extends  AbstractModel
{
    /**
     * @var
     */
    protected $prepaid = false;
    /**
     * @var
     */
    protected $CityCode = array();
    /**
     * @var
     */
    protected $Code = array();
    /**
     * @var
     */
    protected $hide_delivery_day = null;
    /**
     * @var
     */
    protected $add_delivery_day = null;
	/**
    * @return string
    */
   	public function getPrepaid()
    {
        return $this->prepaid;
    }
    /**
     * @param string $prepaid
     */
    public function setPrepaid($prepaid)
    {
        $this->prepaid = $prepaid;
    }
	  /**
    * @return string
    */
   	public function getCityCode()
    {
        return $this->CityCode;
    }
    /**
     * @param string $prepaid
     */
    public function setCityCode($CityCode)
    {
        $this->CityCode = $CityCode;
    }
	/**
    * @return string
    */
   	public function getCode()
    {
        return $this->Code;
    }
    /**
     * @param string $Code
     */
    public function setCode($Code)
    {
        $this->Code = $Code;
    }
	/**
    * @return string
    */
   	public function getHide_delivery_day()
    {
        return $this->hide_delivery_day;
    }
    /**
     * @param string $hide_delivery_day
     */
    public function setHide_delivery_day($hide_delivery_day)
    {
        $this->hide_delivery_day = $hide_delivery_day;
    }
	/**
    * @return string
    */
   	public function getAdd_delivery_day()
    {
        return $this->add_delivery_day;
    }
    /**
     * @param string $add_delivery_day
     */
    public function setAdd_delivery_day($add_delivery_day)
    {
        $this->add_delivery_day = $add_delivery_day;
    }
	
    
}