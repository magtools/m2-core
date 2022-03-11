<?php

namespace Mtools\Core\Test\Unit\Model\Config;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
//use Safe\Exceptions\JsonException; only 2.4.x
use Mtools\Core\Model\Config\Version;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;

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
    protected $json;
    protected $data = [];

    protected $testObject;
    protected $testReflection;

    protected function setUp():void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->json = $objectManager->getObject(Json::class);

        $this->testObject = new Version(
            $this->getMockedDependency('context', 'Magento\Framework\Model\Context'),
            $this->getMockedDependency('registry', 'Magento\Framework\Registry'),
            $this->getMockedDependency('config', 'Magento\Framework\App\Config\ScopeConfigInterface'),
            $this->getMockedDependency('cacheTypeList', 'Magento\Framework\App\Cache\TypeListInterface'),
            $this->getMockedDependency('moduleResource', 'Magento\Framework\Module\ResourceInterface'),
            $this->getMockedDependency('resource', 'Magento\Framework\Model\ResourceModel\AbstractResource'),
            $this->getMockedDependency('resourceCollection', 'Magento\Framework\Data\Collection\AbstractDb'),
            $this->getMockedDependency('moduleList', 'Magento\Framework\Module\ModuleList'),
            $this->getMockedDependency('moduleManager', 'Magento\Framework\Module\Manager'),
            $this->json,
            $this->data
        );

        $this->testReflection = new \ReflectionClass(Version::class);
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
     */
    public function testAfterLoad()
    {
        $this->doesNotPerformAssertions();
        $this->moduleList->method('getNames')
            ->willReturn([]);
        $this->testObject->afterLoad();
    }

    /**
     * @test
     */
    public function testAfterLoadLocalizedException()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Could not get module list.');
        $this->testObject->afterLoad();
    }

    /**
     * @test
     */
    public function testAfterLoadJsonException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->moduleList->method('getNames')->willReturn(['Mtools_Core']);
        $this->moduleResource->method('getDbVersion')->willReturn("\xB1\x31");
        $this->moduleManager->method('isEnabled')->willReturn(1);
        $this->expectExceptionMessage('Unable to serialize value. Error: ' . json_last_error_msg());
        $this->testObject->afterLoad();
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @throws \ReflectionException
     */
    public function testGetCustomValues($list, $status, $expected)
    {
        $method = $this->testReflection->getMethod('getCustomModules');
        $method->setAccessible(true);

        $this->moduleList->method('getNames')
            ->willReturn($list);

        $this->moduleResource->method('getDbVersion')
            ->willReturn($this->returnCallback([$this,'getDbVersionCallback']));

        $this->moduleManager->method('isEnabled')
            ->willReturn($status);

        $result = $method->invoke($this->testObject);
        $this->assertEquals($result, $expected);
    }

    /**
     * @return array[]
     */
    public function dataProvider()
    {
        return [
            'Empty module list' => [ /* test case description */
                [], 1, '[]' /* params: list, status, expected_result */
            ],
            'Module list without matches' => [
                ['Test_Customer','Test_Cms'], 1, '[]'
            ],
            'Module list with match enabled' => [
                ['Test_Customer','Test_Cms','Mtools_Core'],
                1,
                '[{"name":"Mtools_Core","version":"1.2.0","active":1}]'
            ],
            'Module list with match disabled' => [
                ['Test_Customer','Test_Cms','Mtools_Core'],
                0,
                '[{"name":"Mtools_Core","version":"1.2.0","active":0}]'
            ],
            'Module list with multiple matches' => [
                ['Test_Customer','Test_Cms','Mtools_Core','Mtools_CronRun'],
                1,
                '[{"name":"Mtools_Core","version":"1.2.0","active":1},{"name":"Mtools_CronRun","version":"1.2.0","active":1}]'
            ]
        ];
    }

    /**
     * @return string
     */
    public function getDbVersionCallback()
    {
        /* The purpose of this callback is just to implement some logic in a mocked method for documentation */
        $args = func_get_args();
        $version = '1.2.0';
        if (!is_string($args[0])) {
            $version .= '.1';
        }
        return $version;
    }
}
