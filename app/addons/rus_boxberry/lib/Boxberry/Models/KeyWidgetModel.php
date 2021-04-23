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
 *  * File: DeliveryCosts.php
 *  * Created: 26.07.2016
 *  *
 */

namespace Boxberry\Models;

/**
 * Class DeliveryCosts
 * @package Boxberry\Models
 */
class DeliveryCosts extends AbstractModel
{
    /**
     * @var string
     */
    protected $WidgetToken = null;

    public function __construct(array $data)
    {
        $this->apiToken = $data['apiToken'];
        parent::__construct();
    }
    /**
     * @return string
     */
    public function getWidgetToken()
    {
        return $this->WidgetToken;
    }

    /**
     * @param string $price
     */
    public function setWidgetToken($WidgetToken)
    {
        $this->WidgetToken = $WidgetToken;
    }

}