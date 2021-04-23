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


namespace Tygh\Addons\CommerceML\Commands;


use Tygh\Addons\CommerceML\Formators\OrderFormator;
use Tygh\Addons\CommerceML\Storages\OrderStorage;
use Tygh\Common\OperationResult;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;
use Tygh\Tools\DateTimeHelper;
use XMLWriter;

/**
 * Class ExportOrdersCommandHandler
 *
 * @package Tygh\Addons\CommerceML\Commands
 */
class ExportOrdersCommandHandler
{
    /**
     * @var \XMLWriter
     */
    private $xml_writer;

    /**
     * @var \Tygh\Addons\CommerceML\Storages\OrderStorage
     */
    private $order_storage;

    /**
     * @var array<string>
     */
    private $order_statuses;

    /**
     * @var \Tygh\Addons\CommerceML\Formators\OrderFormator
     */
    private $order_formator;

    /**
     * @var array<string, int|string|bool|array>
     */
    private $settings;

    /**
     * ExportOrdersCommandHandler constructor.
     *
     * @param \XMLWriter                                      $xml_writer     XML writer
     * @param \Tygh\Addons\CommerceML\Storages\OrderStorage   $order_storage  Order storage
     * @param \Tygh\Addons\CommerceML\Formators\OrderFormator $order_formator Order formator
     * @param array<string>                                   $order_statuses Orders statuses to export
     * @param array<string, int|string|bool|array>            $settings       Settings
     */
    public function __construct(
        XMLWriter $xml_writer,
        OrderStorage $order_storage,
        OrderFormator $order_formator,
        array $order_statuses,
        array $settings
    ) {
        $this->xml_writer = $xml_writer;
        $this->order_storage = $order_storage;
        $this->order_formator = $order_formator;
        $this->order_statuses = $order_statuses;
        $this->settings = $settings;
    }

    /**
     * Executes orders export
     *
     * @param \Tygh\Addons\CommerceML\Commands\ExportOrdersCommand $command Command instance
     *
     * @return \Tygh\Common\OperationResult
     */
    public function handle(ExportOrdersCommand $command)
    {
        $result = new OperationResult();

        $params = $this->getParams($command);

        list($orders) = $this->order_storage->getOrders($params);

        $this->xml_writer->openMemory();
        $this->xml_writer->startDocument();
        $this->xml_writer->startElement(SimpleXmlElement::findAlias('commerceml'));

        /** @var array<string, int|string|array> $data */
        foreach ($orders as $data) {
            $order_data = $this->order_storage->getOrderInfo((int) $data['order_id']);

            if (empty($order_data)) {
                continue;
            }

            $this->xml_writer = $this->order_formator->form($this->xml_writer, $order_data);
        }

        $this->xml_writer->endElement();

        $result->setData($this->xml_writer->outputMemory(), 'exported_orders');

        return $result;
    }

    /**
     * Gets orders params
     *
     * @param ExportOrdersCommand $command Command instance
     *
     * @return array<string, string|bool|int|array>
     */
    private function getParams(ExportOrdersCommand $command)
    {
        $params = [
            'company_id'   => $command->company_id,
            'company_name' => true,
        ];

        if (!empty($this->settings['orders_exporter.export_from_order_id'])) {
            $params['from_order_id'] = (int) $this->settings['orders_exporter.export_from_order_id'];
        }

        if ($this->settings['orders_exporter.strategy'] === OrderFormator::STRATEGY_NEW) {
            $params['updated_at_from'] = $command->last_export_time;
        }

        if (!empty($this->order_statuses)) {
            $params['status'] = $this->order_statuses;
        }

        return $params;
    }
}
