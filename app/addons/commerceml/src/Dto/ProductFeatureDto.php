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
 * Class ProductFeatureDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class ProductFeatureDto implements RepresentEntityDto
{
    use RepresentEntitDtoTrait;

    const REPRESENT_ENTITY_TYPE = 'product_feature';

    const TYPE_STRING = 'string';

    const TYPE_NUMBER = 'number';

    const TYPE_DATE_TIME = 'date_time';

    const TYPE_DIRECTORY = 'directory';

    const TYPE_EXTENDED = 'extended';

    const BRAND_EXTERNAL_ID = 'brand1c';

    /**
     * @var \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public $id;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\TranslatableValueDto|null
     */
    public $name;

    /**
     * @var string (string, number, date_time, directory)
     */
    public $type;

    /**
     * @psalm-var array<array-key, ProductFeatureVariantDto>
     *
     * @var \Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto[]
     */
    public $variants = [];

    /**
     * @var \Tygh\Addons\CommerceML\Dto\PropertyDtoCollection
     */
    public $properties;

    /**
     * @var bool
     */
    public $is_multiple = false;

    /**
     * ProductFeatureDto constructor.
     */
    public function __construct()
    {
        $this->properties = new PropertyDtoCollection();
    }
}
