<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iggo\AdvancedBundles\Controller\Adminhtml\Product;

/**
 * Downloadable File upload controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class File extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';
}
