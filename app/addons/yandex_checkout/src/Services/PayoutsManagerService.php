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

namespace Tygh\Addons\YandexCheckout\Services;

use Tygh\Addons\YandexCheckout\Payouts\PayoutsManager;
use Tygh\VendorPayouts;

class PayoutsManagerService
{
    /** @var \Tygh\Addons\YandexCheckout\Payouts\PayoutsManager[] */
    protected $instances = [];

    /** @var @bool */
    protected $can_collect_commission;

    public function __construct($can_collect_commission)
    {
        $this->can_collect_commission = $can_collect_commission;
    }

    /**
     * @param int  $company_id
     * @param bool $is_new_instance_required
     *
     * @return \Tygh\Addons\YandexCheckout\Payouts\PayoutsManager
     */
    public function getManager($company_id, $is_new_instance_required = false)
    {
        $company_id = (int) $company_id;

        if (!isset($this->instances[$company_id]) || $is_new_instance_required) {
            $vendor_payouts_instance = VendorPayouts::instance(['vendor' => $company_id]);
            $this->instances[$company_id] = new PayoutsManager(
                $company_id,
                $this->can_collect_commission,
                $vendor_payouts_instance
            );
        }

        return $this->instances[$company_id];
    }
}