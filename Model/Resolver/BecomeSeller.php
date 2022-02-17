<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_SellerGraphQl
 * @copyright  Copyright (c) 2022 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

declare(strict_types=1);

namespace Lof\SellerGraphQl\Model\Resolver;

use Lof\MarketPlace\Api\Data\SellerInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Catalog\Model\Product\Url;


/**
 * Class BecomeSeller
 * @package Lof\SellerGraphQl\Model\Resolver
 */
class BecomeSeller implements ResolverInterface
{

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var DataProvider\CreateSeller
     */
    private $_createSeller;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var SellerInterface
     */
    private $sellerInterface;

    /**
     * CreateSeller constructor.
     * @param DataProvider\CreateSeller $createSeller
     * @param GetCustomer $getCustomer
     * @param SellerInterface $sellerInterface
     * @param Url $url
     */
    public function __construct(
        DataProvider\CreateSeller $createSeller,
        GetCustomer $getCustomer,
        SellerInterface $sellerInterface,
        Url $url
    ) {
        $this->_createSeller = $createSeller;
        $this->getCustomer = $getCustomer;
        $this->sellerInterface = $sellerInterface;
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if (!$context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        if (!($args['input']) || !isset($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }
        $args = $args['input'];
        $customer = $this->getCustomer->execute($context);
        $args['customer_id'] = $customer->getId();
        $args['name'] = $customer->getFirstname().' '.$customer->getLastname();
        $args['email'] = $customer->getEmail();

        $sellerInterface = $this->sellerInterface;
        $sellerInterface->setEmail($args['email'])
            ->setName($args['name'])
            ->setGroup($args['group_id'])
            ->setUrl($args['url_key'])
            ->setCustomerId($args['customer_id']);

        return $this->_createSeller->createSeller($sellerInterface, $args['customer_id']);
    }


}
