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

use Lof\MarketPlace\Api\SellerProductsRepositoryInterface;
use Lof\MarketPlace\Api\SellersFrontendRepositoryInterface;
use Lof\MarketPlace\Api\SellerGroupRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\InputException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\Query;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Filter;

/**
 * Class SellerGroups
 *
 * @package Lof\SellerGraphQl\Model\Resolver
 */
class SellerGroups extends AbstractSellerQuery implements ResolverInterface
{

    /**
     * @var SellerGroupRepositoryInterface
     */
    private $sellerGroup;

    /**
     * @var string
     */
    private const SPECIAL_CHARACTERS = '-+~/\\<>\'":*$#@()!,.?`=%&^';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Sellers constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SellersFrontendRepositoryInterface $seller
     * @param SellerProductsRepositoryInterface $productSeller
     * @param ProductRepositoryInterface $productRepository
     * @param SellerGroupRepositoryInterface $sellerGroup
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SellersFrontendRepositoryInterface $seller,
        SellerProductsRepositoryInterface $productSeller,
        ProductRepositoryInterface $productRepository,
        SellerGroupRepositoryInterface $sellerGroup,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->sellerGroup = $sellerGroup;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($searchCriteriaBuilder, $seller, $productSeller, $productRepository);
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
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        $store = $context->getExtensionAttributes()->getStore();
        $args[Filter::ARGUMENT_NAME] = $this->formatMatchFilters($args['filter'], $store);
        $searchCriteria = $this->searchCriteriaBuilder->build('lof_marketplace_seller_group', $args);
        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);

        $searchResult = $this->sellerGroup->getList($searchCriteria);
        $totalPages = $args['pageSize'] ? ((int)ceil($searchResult->getTotalCount() / $args['pageSize'])) : 0;
        $resultItems = $searchResult->getItems();
        $items = [];
        if($resultItems){
            foreach($resultItems as $_item){
                $items[] = $_item->__toArray();
            }
        }
        return [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $items,
            'page_info' => [
                'page_size' => $args['pageSize'],
                'current_page' => $args['currentPage'],
                'total_pages' => $totalPages
            ]
        ];
    }

    /**
     * Format match filter to behave like fuzzy match
     *
     * @param array $filter
     * @param StoreInterface $store
     * @return array
     * @throws InputException
     */
    private function formatMatchFilters(array $filters, StoreInterface $store): array
    {
        $minQueryLength = $this->scopeConfig->getValue(
            Query::XML_PATH_MIN_QUERY_LENGTH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        foreach ($filters as $filter => $condition) {
            $conditionType = current(array_keys($condition));
            $tmpminQueryLength = $minQueryLength;
            if ($conditionType === 'match') {
                $searchValue = trim(str_replace(self::SPECIAL_CHARACTERS, '', $condition[$conditionType]));
                $matchLength = strlen($searchValue);
                if ($matchLength < $tmpminQueryLength) {
                    throw new InputException(__('Invalid match filter. Minimum length is %1.', $tmpminQueryLength));
                }
                unset($filters[$filter]['match']);
                $filters[$filter]['like'] = '%' . $searchValue . '%';
            }
        }
        return $filters;
    }
}
