<?php

namespace Mtools\Core\Model\Config;

use Magento\Framework\Registry;
use Magento\Framework\Model\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\Manager;


class Version extends \Magento\Framework\App\Config\Value
{
    /**
     * @const
     */
    const MTOOLS_VENDOR = 'Mtools';

    /**
     * @var ResourceInterface
     */
    protected $moduleResource;

    /**
     * @var ModuleList
     */
    protected $moduleList;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ResourceInterface $moduleResource
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param ModuleList $moduleList
     * @param Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ResourceInterface $moduleResource,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        ModuleList $moduleList,
        Manager $moduleManager,
        array $data = []
    ) {
        $this->moduleResource = $moduleResource;
        $this->moduleList = $moduleList;
        $this->moduleManager = $moduleManager;

        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     */
    public function afterLoad()
    {
        $this->setValue($this->getCustomModules());
    }

    /**
     * @return string
     * @throws \Safe\Exceptions\JsonException
     */
    protected function getCustomModules()
    {
        $result = [];

        $modules = $this->moduleList->getNames();
        foreach ($modules as $_module) {
            if(strpos($_module, self::MTOOLS_VENDOR) !== false)
            {
                $result[] = [
                    'name' => $_module,
                    'version' => $this->moduleResource->getDbVersion($_module),
                    'active' => (int)(bool)$this->moduleManager->isEnabled($_module)
                ];
            }
        }

        return \Safe\json_encode($result);
    }
}
