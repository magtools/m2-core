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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;

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
     * @var Json
     */
    protected $json;

    /**
     * Version constructor.
     *
     * @param Context               $context
     * @param Registry              $registry
     * @param ScopeConfigInterface  $config
     * @param TypeListInterface     $cacheTypeList
     * @param ResourceInterface     $moduleResource
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param ModuleList            $moduleList
     * @param Manager               $moduleManager
     * @param Json                  $json
     * @param array                 $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        Json $json,
        array $data = []
    ) {
        $this->moduleResource = $moduleResource;
        $this->moduleList = $moduleList;
        $this->moduleManager = $moduleManager;
        $this->json = $json;

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
     * @return bool|string
     * @throws LocalizedException
     */
    protected function getCustomModules()
    {
        $result = [];
        $modules = $this->moduleList->getNames();
        if ($modules === null) {
            throw new LocalizedException(__('Could not get module list.'));
        }

        foreach ($modules as $module) {
            if (strpos($module, self::MTOOLS_VENDOR) !== false) {
                $result[] = [
                    'name' => $module,
                    'version' => $this->moduleResource->getDbVersion($module),
                    'active' => (int)(bool)$this->moduleManager->isEnabled($module)
                ];
            }
        }

        return $this->json->serialize($result);
    }
}
