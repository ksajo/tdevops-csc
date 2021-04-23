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


namespace Tygh\Addons\CommerceML\Dto;

/**
 * Class ProductFeatureValueDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class ProductFeatureValueDto
{
    /**
     * @var \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public $feature_id;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\IdDto|null
     */
    public $value_id;

    /**
     * @var string|null
     */
    public $value;

    /**
     * ProductFeatureValueDto constructor.
     *
     * @param \Tygh\Addons\CommerceML\Dto\IdDto      $feature_id Freature ID
     * @param string|null                            $value      Feature value
     * @param \Tygh\Addons\CommerceML\Dto\IdDto|null $value_id   Feature value variant id
     */
    public function __construct(IdDto $feature_id, $value = null, IdDto $value_id = null)
    {
        $this->feature_id = $feature_id;
        $this->value = $value;
        $this->value_id = $value_id;
    }

    /**
     * @param \Tygh\Addons\CommerceML\Dto\IdDto      $feature_id Freature ID
     * @param string|null                            $value      Feature value
     * @param \Tygh\Addons\CommerceML\Dto\IdDto|null $value_id   Feature value variant id
     *
     * @return \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto
     */
    public static function create(IdDto $feature_id, $value = null, IdDto $value_id = null)
    {
        return new self($feature_id, $value, $value_id);
    }
}
