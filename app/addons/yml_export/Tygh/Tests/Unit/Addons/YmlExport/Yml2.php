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

namespace Tygh\Tests\Unit\Addons\YmlExport;

class Yml2 extends \Tygh\Ym\Yml2
{
    protected function getStorageData($key)
    {
        return null;
    }

    protected function getPriceList($price_id)
    {
        return array();
    }

    protected function getOptions($price_id)
    {
        return array();
    }

    public function getFilePath()
    {
        return '';
    }

    public function getTempFilePath()
    {
        return '';
    }

    protected function getYMLCategories($field_name)
    {
        return array();
    }

    protected function formatDate($timestamp)
    {
        $dt = new \DateTime('now', new \DateTimeZone('UTC'));
        $dt->setTimestamp($timestamp);

        return $dt->format('d.m.Y');
    }

    protected function createLogger($format = 'csv', $price_id = 0)
    {
        return null;
    }

    /**
     * Adapter method to call protected method.
     *
     * @param array $product_features_data
     * @param array $features
     *
     * @return array
     */
    public function getProductFeaturesAdapter($product_features_data, $features)
    {
        return $this->getProductFeatures($product_features_data, $features);
    }
}