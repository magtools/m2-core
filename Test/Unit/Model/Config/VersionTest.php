<?php

namespace Mtools\Core\Test\Unit\Model\Config;

use PHPUnit\Framework\TestCase;
use Mtools\Core\Model\Config\Version;

class VersionTest extends TestCase
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
        $this->versionObject = new Version(
            $this->getMockedDependency('context', 'Magento\Framework\Model\Context'),
            $this->getMockedDependency('registry', 'Magento\Framework\Registry'),
            $this->getMockedDependency('config', 'Magento\Framework\App\Config\ScopeConfigInterface'),
            $this->getMockedDependency('cacheTypeList', 'Magento\Framework\App\Cache\TypeListInterface'),
            $this->getMockedDependency('moduleResource', 'Magento\Framework\Module\ResourceInterface'),
            $this->getMockedDependency('resource', 'Magento\Framework\Model\ResourceModel\AbstractResource'),
            $this->getMockedDependency('resourceCollection', 'Magento\Framework\Data\Collection\AbstractDb'),
            $this->getMockedDependency('moduleList', 'Magento\Framework\Module\ModuleList'),
            $this->getMockedDependency('moduleManager', 'Magento\Framework\Module\Manager'),
            $this->data
        );

        $this->versionReflection = new \ReflectionClass(Version::class);
    }

    /**
     * @param $propertyName
     * @param $className
     */
    protected function getMockedDependency($propertyName, $className)
    {
        if (empty($this->{$propertyName})) {
            $this->{$propertyName} = $this->getMockBuilder($className)
                ->disableOriginalConstructor()
                ->getMock();
        }
        return $this->{$propertyName};
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
        return [
            'Empty module list' => [ /* test case description */
                [], '1.2.0', 1, '[]' /* params: list, version, status, expected_result */
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
