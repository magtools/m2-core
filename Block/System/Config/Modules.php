<?php

namespace Mtools\Core\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Serialize\Serializer\Json;

class Modules extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @const
     */
    const TEMPLATE = 'Mtools_Core::system/config/modules.phtml';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var string
     */
    protected $modules;

    /**
     * @var string
     */
    protected $namePrefix;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Json $json,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::TEMPLATE);
        }
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->modules = $this->json->unserialize($element->getValue());

        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getModules()
    {
        return $this->modules;
    }
}
