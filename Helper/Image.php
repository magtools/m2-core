<?php

namespace Mtools\Core\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Image extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Custom directory relative to the "media" folder
     */
    const DIRECTORY = 'catalog/product';

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $_imageFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\Factory $imageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_imageFactory = $imageFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * First check this file on FS
     *
     * @param string $filename
     * @return bool
     */
    protected function _fileExists($filename)
    {
        if ($this->_mediaDirectory->isFile($filename)) {
            return true;
        }
        return false;
    }

    /**
     * Resize image
     * @return string
     */
    public function resize($image, $width = null, $height = null, $keepFrame = false)
    {
        $saveFolder = self::DIRECTORY;
        $savePath = $saveFolder . '/cache/mtools';

        if ($width !== null) {
            $savePath .= '/' . $width . 'x';
            if ($height !== null) {
                $savePath .= $height ;
            }
        }

        $image = (!empty($image) && substr($image, 0, 1) != '/') ? '/'.$image : $image;
        $placeholder = $this->_storeManager->getStore()->getConfig('catalog/placeholder/image_placeholder');
        $image = $this->_fileExists($image) ? $image : '/catalog/product/placeholder/'.$placeholder;
        $absolutePath = $this->_mediaDirectory->getAbsolutePath() . $image;
        $imageResized = $this->_mediaDirectory->getAbsolutePath($savePath) . $image;

        if (!$this->_fileExists($savePath . $image)) {
            $imageFactory = $this->_imageFactory->create();
            $imageFactory->open($absolutePath);
            $imageFactory->constrainOnly(true);
            $imageFactory->keepTransparency(true);
            $imageFactory->keepFrame($keepFrame);
            $imageFactory->keepAspectRatio(true);
            $imageFactory->quality(100);
            $imageFactory->resize($width, $height);
            $imageFactory->save($imageResized);
        }

        return $this->getMediaUrl() . $savePath . $image;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->_storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
