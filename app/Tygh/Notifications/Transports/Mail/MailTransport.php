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

namespace Tygh\Notifications\Transports\Mail;

use Tygh\Exceptions\DeveloperException;
use Tygh\Mailer\Mailer;
use Tygh\Notifications\Transports\BaseMessageSchema;
use Tygh\Notifications\Transports\ITransport;

/**
 * Class MailTransport implements a transport that send emails based on an event message.
 *
 * @package Tygh\Events\Transports
 */
class MailTransport implements ITransport
{
    /**
     * @var \Tygh\Mailer\Mailer
     */
    protected $mailer;

    /**
     * @var \Tygh\Notifications\Transports\Mail\ReceiverFinderFactory
     */
    protected $receiver_finder_factory;

    public function __construct(Mailer $mailer, ReceiverFinderFactory $receiver_finder_factory)
    {
        $this->mailer = $mailer;
        $this->receiver_finder_factory = $receiver_finder_factory;
    }

    public static function getId()
    {
        return 'mail';
    }

    /**
     * @inheritDoc
     */
    public function process(BaseMessageSchema $schema, array $receiver_search_conditions)
    {
        if (!$schema instanceof MailMessageSchema) {
            throw new DeveloperException('Input data should be instance of MailMessageSchema');
        }

        $receivers = $this->getReceivers($receiver_search_conditions, $schema);

        return $this->mailer->send(
            [
                'to'            => $receivers,
                'from'          => $schema->from,
                'reply_to'      => $schema->reply_to,
                'data'          => $schema->data,
                'template_code' => $schema->template_code,
                'tpl'           => $schema->legacy_template,
                'company_id'    => $schema->company_id,
                'attachments'   => $schema->attachments,
                'storefront_id' => $schema->storefront_id,
            ],
            $schema->area,
            $schema->language_code
        );
    }

    /**
     * Gets message receivers.
     *
     * @param \Tygh\Notifications\Receivers\SearchCondition[]  $receiver_search_conditions
     * @param \Tygh\Notifications\Transports\BaseMessageSchema $schema
     *
     * @return string[]
     */
    protected function getReceivers($receiver_search_conditions, BaseMessageSchema $schema)
    {
        $emails = [];

        foreach ($receiver_search_conditions as $condition) {
            $finder = $this->receiver_finder_factory->get($condition->getMethod());
            $emails = array_merge($emails, $finder->find($condition->getCriterion(), $schema));
        }

        $emails = array_unique($emails);

        if (!$emails) {
            $emails = (array) $schema->to;
        }

        return $emails;
    }
}
