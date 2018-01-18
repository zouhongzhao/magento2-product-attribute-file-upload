<?php
namespace Iggo\AdvancedBundles\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;

/**
 * Data provider for "Custom Attribute" field of product page
 */
class BundlePdf extends AbstractModifier
{
    const FIELD_CODE = 'bundle_pdf';
    protected $_helper;
    /**
     * @param LocatorInterface            $locator
     * @param UrlInterface                $urlBuilder
     * @param ArrayManager                $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        \Iggo\AdvancedBundles\Helper\Data $helper,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->arrayManager = $arrayManager;
        $this->_helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->customiseBundlePdfField($meta);
        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        $modelId = $product->getId();
        if (isset($data[$modelId]['product'][self::FIELD_CODE])){
            $bundlePdf = $data[$modelId]['product'][self::FIELD_CODE];
            unset($data[$modelId]['product'][self::FIELD_CODE]);
            $url = $this->_helper->getBundlePdfUrl($bundlePdf);
            $data[$modelId]['product'][self::FIELD_CODE][0]['name'] = basename($bundlePdf);
            $data[$modelId]['product'][self::FIELD_CODE][0]['url'] = $url;
            $path = $this->_helper->getBundlePdfPath($bundlePdf);
            if(is_file($path)){
                $fileSize = filesize($path);
            }else{
                $fileSize = 0;
            }
            $data[$modelId]['product'][self::FIELD_CODE][0]['size'] = $fileSize;
        }
        return $data;
    }

    /**
     * Customise Custom Attribute field
     *
     * @param array $meta
     *
     * @return array
     */
    protected function customiseBundlePdfField(array $meta)
    {
        $fieldCode = self::FIELD_CODE;
        $elementPath = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
        $containerPath = $this->arrayManager->findPath(static::CONTAINER_PREFIX . $fieldCode, $meta, null, 'children');

        if (!$elementPath) {
            return $meta;
        }

        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => __('Bundle PDF'),
                            'dataScope'     => '',
                            'breakLine'     => false,
                            'formElement'   => 'container',
                            'componentType' => 'container',
                            'component'     => 'Magento_Ui/js/form/components/group',
                            'scopeLabel'    => __('[GLOBAL]'),
                        ],
                    ],
                ],
                'children'  => [
                    $fieldCode => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => 'fileUploader',
                                    'componentType' => 'fileUploader',
                                    'component' => 'Magento_Ui/js/form/element/file-uploader',
                                    //'component' => 'Magento_Downloadable/js/components/file-uploader',
                                    //'elementTmpl' => 'Magento_Downloadable/components/file-uploader',
                                    //'elementTmpl' => 'ui/form/element/uploader/uploader',
                                    //"previewTmpl" => 'ui/form/element/uploader/preview',
                                    'elementTmpl' => 'Iggo_AdvancedBundles/components/file-uploader',
                                    "previewTmpl" => 'Iggo_AdvancedBundles/components/preview',
                                    'fileInputName' => self::FIELD_CODE,
                                    //'multFileInputName' => 'bundlepdf[]',
                                    'uploaderConfig' => [
                                        'url' => $this->urlBuilder->addSessionParam()->getUrl(
                                            'advancedbundlesadmin/product_file/upload',
                                            ['type' => self::FIELD_CODE, '_secure' => true]
                                        ),
                                    ],
                                    'dataScope' => self::FIELD_CODE,
                                    // 'validation' => [
                                    //     'required-entry' => true,
                                    // ],
                                ],
                            ],
                        ],
                    ]
                ]
            ]
        );

        return $meta;
    }

    /**
     * Retrieve custom attribute collection
     *
     * @return array
     */
    protected function getOptions()
    {
        // Get your options
    }
}