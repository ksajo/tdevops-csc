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
 * Class ProductDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class ProductDto implements RepresentEntityDto
{
    use RepresentEntitDtoTrait;

    const REPRESENT_ENTITY_TYPE = 'product';

    /**
     * @var \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public $id;

    /**
     * @var bool
     */
    public $is_variation = false;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public $parent_id;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDtoCollection
     */
    public $variation_feature_values;

    /**
     * @var string|null
     */
    public $variation_group_code;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\TranslatableValueDto|null
     */
    public $name;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\TranslatableValueDto|null
     */
    public $description;

    /**
     * @var array<\Tygh\Addons\CommerceML\Dto\IdDto>
     */
    public $categories = [];

    /**
     * @var string|null
     */
    public $product_code;

    /**
     * @var float|null
     */
    public $price;

    /**
     * @var float|null
     */
    public $list_price;

    /**
     * @psalm-var array<\Tygh\Addons\CommerceML\Dto\PriceValueDto>
     *
     * @var \Tygh\Addons\CommerceML\Dto\PriceValueDto[]
     */
    public $prices = [];

    /**
     * @var string|null
     */
    public $status;

    /**
     * @var int|null
     */
    public $quantity;

    /**
     * @var array<\Tygh\Addons\CommerceML\Dto\IdDto>
     */
    public $taxes = [];

    /**
     * @var \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDtoCollection
     */
    public $product_feature_values;

    /**
     * @var array<\Tygh\Addons\CommerceML\Dto\ImageDto>
     */
    public $images = [];

    /**
     * @var \Tygh\Addons\CommerceML\Dto\PropertyDtoCollection
     */
    public $properties;

    /**
     * @var bool
     */
    public $is_creatable = true;

    /**
     * @var bool
     */
    public $is_removed = false;

    /**
     * @var bool
     */
    public $is_new = true;

    /**
     * ProductDto constructor.
     */
    public function __construct()
    {
        $this->product_feature_values = new ProductFeatureValueDtoCollection();
        $this->variation_feature_values = new ProductFeatureValueDtoCollection();
        $this->properties = new PropertyDtoCollection();
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $this->product_feature_values = clone $this->product_feature_values;
        $this->variation_feature_values = clone $this->variation_feature_values;
        $this->properties = clone $this->properties;
    }
}
