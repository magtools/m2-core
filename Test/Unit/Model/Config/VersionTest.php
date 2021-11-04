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

    protected $versionObject;
    protected $versionReflection;

    protected function setUp():void
    {
        $this->getMockedDependency('context', 'Magento\Framework\Model\Context');
        $this->getMockedDependency('registry', 'Magento\Framework\Registry');
        $this->getMockedDependency('config', 'Magento\Framework\App\Config\ScopeConfigInterface');
        $this->getMockedDependency('cacheTypeList', 'Magento\Framework\App\Cache\TypeListInterface');
        $this->getMockedDependency('moduleResource', 'Magento\Framework\Module\ResourceInterface');
        $this->getMockedDependency('resource', 'Magento\Framework\Model\ResourceModel\AbstractResource');
        $this->getMockedDependency('resourceCollection', 'Magento\Framework\Data\Collection\AbstractDb');
        $this->getMockedDependency('moduleList', 'Magento\Framework\Module\ModuleList');
        $this->getMockedDependency('moduleManager', 'Magento\Framework\Module\Manager');

        $this->versionObject = new \Mtools\Core\Model\Config\Version(
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

        $this->versionReflection = new \ReflectionClass(\Mtools\Core\Model\Config\Version::class);
    }

    /**
     * @param $propertyName
     * @param $className
     */
    protected function getMockedDependency($propertyName, $className)
    {
        $this->{$propertyName} = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @throws \ReflectionException
     */
    public function testGetCustomValues($list, $version, $status, $expected)
    {
        $method = $this->versionReflection->getMethod('getCustomModules');
        $method->setAccessible(true);

        $this->moduleList->method('getNames')
            ->willReturn($list);

        $this->moduleResource->method('getDbVersion')
            ->willReturn($version);

        $this->moduleManager->method('isEnabled')
            ->willReturn($status);

        $getCustomModules = $method->invoke($this->versionObject);
        $this->assertEquals($getCustomModules, $expected);
    }

    public function dataProvider()
    {
        /* data : [
            'test case description' => [
                list, version, status, expected_result
                ],
        ] */
        return [
            'Empty module list' => [
                [], '1.2.0', 1, '[]'
            ],
            'Module list without matches' => [
                ['Test_Customer','Test_Cms'], '1.2.0', 1, '[]'
            ],
            'Module list with match enabled' => [
                ['Test_Customer','Test_Cms','Mtools_Core'],
                '1.2.0',
                1,
                '[{"name":"Mtools_Core","version":"1.2.0","active":1}]'
            ],
            'Module list with match disabled' => [
                ['Test_Customer','Test_Cms','Mtools_Core'],
                '1.2.0',
                0,
                '[{"name":"Mtools_Core","version":"1.2.0","active":0}]'
            ],
            'Module list with multiple matches' => [
                ['Test_Customer','Test_Cms','Mtools_Core','Mtools_CronRun'],
                '1.2.0',
                1,
                '[{"name":"Mtools_Core","version":"1.2.0","active":1},{"name":"Mtools_CronRun","version":"1.2.0","active":1}]'
            ]
        ];
    }
}
