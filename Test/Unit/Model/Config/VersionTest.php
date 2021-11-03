<?php

namespace Mtools\Core\Test\Unit\Model\Config;


class VersionTest extends \PHPUnit\Framework\TestCase
{

    protected $context;
    protected $registry;
    protected $config;
    protected $cacheTypeList;
    protected $moduleResource;
    protected $resource;
    protected $resourceCollection;
    protected $moduleList;
    protected $moduleManager;
    protected $data = [];

    protected $versionObj;
    protected $versionRef;

    protected function setUp():void
    {
        $this->context = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheTypeList = $this->getMockBuilder('Magento\Framework\App\Cache\TypeListInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->moduleResource = $this->getMockBuilder('Magento\Framework\Module\ResourceInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\AbstractResource')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceCollection = $this->getMockBuilder('Magento\Framework\Data\Collection\AbstractDb')
            ->disableOriginalConstructor()
            ->getMock();
        $this->moduleList = $this->getMockBuilder('Magento\Framework\Module\ModuleList')
            ->disableOriginalConstructor()
            ->getMock();
        $this->moduleManager = $this->getMockBuilder('Magento\Framework\Module\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->versionObj = new \Mtools\Core\Model\Config\Version(
            $this->context,
            $this->registry,
            $this->config,
            $this->cacheTypeList,
            $this->moduleResource,
            $this->resource,
            $this->resourceCollection,
            $this->moduleList,
            $this->moduleManager,
            $this->data
        );
        $this->versionRef = new \ReflectionClass(\Mtools\Core\Model\Config\Version::class);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @throws \ReflectionException
     */
    public function testGetCustomValues($list, $version, $status, $expected)
    {
        $method = $this->versionRef->getMethod('getCustomModules');
        $method->setAccessible(true);

        $this->moduleList->method('getNames')
            ->willReturn($list);

        $this->moduleResource->method('getDbVersion')
            ->willReturn($version);

        $this->moduleManager->method('isEnabled')
            ->willReturn($status);

        $getCustomModules = $method->invoke($this->versionObj);
        $this->assertEquals($getCustomModules, $expected);
    }

    public function dataProvider()
    {
        /*
         data : [
            'test case description' => [
                list,
                version,
                status,
                expected_result
                ],
        ]
         */
        return [
            'Empty module list' => [
                [], '1.2.0', 1, '[]'
            ]
        ];
    }
}
