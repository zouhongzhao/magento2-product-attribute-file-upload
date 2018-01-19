<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iggo\AdvancedBundles\Controller\Adminhtml\Product\File;

use Magento\Framework\Controller\ResultFactory;
use Iggo\AdvancedBundles\Model\Data\File as FileData;
class Upload extends \Iggo\AdvancedBundles\Controller\Adminhtml\Product\File
{

    /**
     * Downloadable file helper.
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $_fileHelper;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    private $storageDatabase;

    /**
     *
     * Copyright © Magento, Inc. All rights reserved.
     * See COPYING.txt for license details.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Downloadable\Helper\File $fileHelper
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $storageDatabase
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Iggo\AdvancedBundles\Helper\File $fileHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\MediaStorage\Helper\File\Storage\Database $storageDatabase
    ) {
        parent::__construct($context);
        $this->_fileHelper = $fileHelper;
        $this->uploaderFactory = $uploaderFactory;
        $this->storageDatabase = $storageDatabase;
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        $tmpPath = FileData::BASE_TMP_PATH;

        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $type]);
            $uploader->setAllowedExtensions(['pdf']);
            $result = $this->_fileHelper->uploadFromTmp($tmpPath, $uploader);

            if (!$result) {
                throw new \Exception('File can not be moved from temporary folder to the destination folder.');
            }

            unset($result['tmp_name'], $result['path']);

            if (isset($result['file'])) {
                $relativePath = rtrim($tmpPath, '/') . '/' . ltrim($result['file'], '/');
                $this->storageDatabase->saveFile($relativePath);
            }

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
