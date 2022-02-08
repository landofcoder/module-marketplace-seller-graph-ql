# Mage2 Module Lof SellerGraphQl

    ``landofcoder/module-marketplace-seller-graphql
``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
magento 2 marketplace graphql extension

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Lof`
 - Enable the module by running `php bin/magento module:enable Lof_SellerGraphQl`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require landofcoder/module-marketplace-seller-graphql`
 - enable the module by running `php bin/magento module:enable Lof_SellerGraphQl`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### TODO
- Refactor Graphql queries
- Refactor Resolvers
- Add documendation for Graphql queries

## Queries

1. Get Seller Profile Info By Id

```
{
    lofSellerById(seller_id: Int!) {
        address
        banner_pic
        city
        company_description
        company_locality
        contact_number
        country
        customer_id
        email
        gplus_active
        gplus_id
        group
        image
        name
        page_layout
        region
        return_policy
        sale
        seller_id
        shipping_policy
        shop_title
        status
        store_id
        thumbnail
        seller_rates {
            items {
                created_at
                customer_id
                detail
                email
                nickname
                rate1
                rate2
                rate3
                rating_id
                seller_id
                status
                title
            }
            total_count
        }
  }
}
```

2. Get List Sellers with Filter options

```
{
    lofSellerList (
        filter: SellerFilterInput,
        pageSize: Int = 20,
        currentPage: Int = 1,
        sort: SellerSortInput
    ) {
        total_count
        items {
            seller_rates {
                items {
                    created_at
                    customer_id
                    detail
                    email
                    nickname
                    rate1
                    rate2
                    rate3
                    rating_id
                    seller_id
                    status
                    title
                }
                total_count
            }
            sale
            seller_id
            name
            thumbnail
            country
            address
            group
            products {
                items {
                    sale
                    id
                    name
                    url_key
                    rating_summary
                    sku
                    image {
                        url
                        label
                    }
                    description {
                        html
                    }
                    short_description {
                        html
                    }
                    product_brand
                    price_range {
                        maximum_price {
                            discount {
                                amount_off
                                percent_off
                            }
                            final_price {
                                currency
                                value
                            }
                            regular_price {
                                currency
                                value
                            }
                        }
                        minimum_price {
                            discount {
                                amount_off
                                percent_off
                            }
                            final_price {
                                currency
                                value
                            }
                            regular_price {
                                currency
                                value
                            }
                        }
                    }
                    price {
                        regularPrice {
                            amount {
                                currency
                            }
                        }
                    }
                }
                total_count
            }
        }
    }
}
```

3. Filter products by seller ID

```
fragment ShopProduct on ProductInterface {
  id
  rating_summary
  description {
    html
  }
  name
  image {
    url
  }
  url_key
  price_range {
    minimum_price {
      regular_price {
        value
        currency
      }
    }
    maximum_price {
      discount {
        percent_off
      }
      final_price {
        value
        currency
      }
      regular_price {
        value
      }
    }
  }
}

fragment PageInfo on SearchResultPageInfo {
  current_page
  page_size
  total_pages
}

lofProductBySellerId(
    seller_id: Int!
    search: String = ""
    filter: ProductAttributeFilterInput
    pageSize: Int = 20
    currentPage: Int = 1
    sort: ProductAttributeSortInput
  ) {
    items {
      ...ShopProduct
    }
    page_info {
      ...PageInfo
    }
    total_count
}
```


