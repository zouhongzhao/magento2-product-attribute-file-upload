<?php 
namespace Iggo\AdvancedBundles\Model\Attribute\Backend\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Iggo\AdvancedBundles\Model\Data\File as FileData;
class File extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    protected $_filesystem;
    protected $_fileUploaderFactory;
    protected $_logger;
    protected $_fileHelper;

    public function __construct(
        RequestInterface $request,
        \Iggo\AdvancedBundles\Helper\File $fileHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->request = $request;
        $this->_filesystem = $filesystem;
        $this->_logger = $logger;
        $this->_fileHelper = $fileHelper;
    }

    public function beforeSave($object)
    {
        //var_dump($this->request->getPost());die;
        $postProduct = $this->request->getPost('product');
        //$postValue = $this->request->getPost('product')[$this->getAttribute()->getName()];
        $value = $object->getData($this->getAttribute()->getName());
        if(!isset($postProduct[$this->getAttribute()->getName()]) && $value){
            $object->setData($this->getAttribute()->getName(), '');
            return $this;
        }
        if (empty($value) && empty($_FILES)) {
            return $this;
        }
        try {
            $result = $value;
            if(isset($result[0]['file'])){
                $flie = $result[0]['file'];
                $this->_fileHelper->moveFileFromTmp(FileData::BASE_TMP_PATH,FileData::BASE_PATH,$result);
                $object->setData($this->getAttribute()->getName(), $flie);
            }
        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                $this->_logger->critical($e);
            }
        }
        return $this;
    }
}
?>