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
    public const TEMPLATE = 'Mtools_Core::system/config/modules.phtml';

    /**
     * @var Json
     */
    protected $json;

    /**
     * Modules constructor.
     *
     * @param Context              $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Json                 $json
     * @param array                $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Json $json,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->json = $json;

        parent::__construct($context, $data);
    }

    /**
     * @return $this|Modules
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
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setModules($this->json->unserialize($element->getValue(), true));
        $this->setNamePrefix($element->getName())
            ->setHtmlId($element->getHtmlId());

        return $this->_toHtml();
    }
}
