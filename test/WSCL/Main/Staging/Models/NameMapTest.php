<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Models;

use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertSame;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesMethod;
use RCS\Util\ReflectionHelper;


#[CoversClass(\WSCL\Main\Staging\Models\NameMap::class)]
#[UsesMethod(\WSCL\Main\Staging\Models\NameMapEntry::class, 'getId')]
#[UsesMethod(\WSCL\Main\Staging\Models\NameMapEntry::class, 'setId')]
#[UsesMethod(\WSCL\Main\Staging\Models\NameMapEntry::class, 'update')]
#[UsesMethod(\WSCL\Main\Staging\Models\NameMapEntry::class, 'getType')]
#[UsesMethod(\WSCL\Main\Staging\Models\NameMapEntry::class, 'getInName')]
#[UsesMethod(\WSCL\Main\Staging\Models\NameMapEntry::class, 'getOutName')]
#[UsesClass(ReflectionHelper::class)]
class NameMapTest extends TestCase // NOSONAR - ignore too many methods
{
    /** @var array<string, string> */
    private array $optionsTable;

    /**
     * Runs before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();

        // reset mock db tables
        $this->optionsTable = [];

        \Brain\Monkey\Functions\when('get_option')->alias(fn($option, $default = false) => $this->getOption($option, $default));
        \Brain\Monkey\Functions\when('update_option')->alias(fn($option, $value, $autoload = null) => $this->updateOption($option, $value, $autoload));
    }

    /**
     * Runs after each test.
     */
    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();

        parent::tearDown();
    }

    private function getOption(string $option, mixed $default = false): mixed
    {
        $result = $default;

        if (isset($this->optionsTable[$option])) {
            $result = $this->optionsTable[$option];
        }

        return $result;
    }

    private function updateOption(string $option, mixed $value, ?string $autoload = null): bool
    {
        $this->optionsTable[$option] = $value;

        return true;
    }

    private function generateEntry(): NameMapEntry
    {
        $obj = new NameMapEntry();

        ReflectionHelper::setObjectProperty(NameMapEntry::class, 'id', rand(), $obj);
        ReflectionHelper::setObjectProperty(NameMapEntry::class, 'type', 'test', $obj);
        ReflectionHelper::setObjectProperty(NameMapEntry::class, 'inName', str_shuffle(MD5(microtime())), $obj);
        ReflectionHelper::setObjectProperty(NameMapEntry::class, 'outName', str_shuffle(MD5(microtime())), $obj);

        return $obj;
    }

    /**
     * Tests NameMap::getInstance()
     */
    public function testGetInstance()
    {
        $obj = NameMap::getInstance();

        assertNotNull($obj);
        assertInstanceOf(NameMap::class, $obj);

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $obj);
        assertEmpty($mappingArray);
    }

    /**
     * Tests NameMap->add()
     */
    public function testAdd_single()
    {
        $testEntry = $this->generateEntry();

        $testObj = NameMap::getInstance();
        $testObj->add($testEntry);

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $testObj);

        assertNotEmpty($mappingArray);
        assertCount(1, $mappingArray);

        assertSame($testEntry, array_shift($mappingArray));

        $secondInst = NameMap::getInstance();

        assertEquals($testEntry, $secondInst->findById($testEntry->getId()));
    }

    /**
     * Tests NameMap->add()
     */
    public function testAdd_multiple()
    {
        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();

        $testObj = NameMap::getInstance();
        $testObj->add($testEntryA);
        $testObj->add($testEntryB);

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $testObj);

        assertNotEmpty($mappingArray);
        assertCount(2, $mappingArray);

        assertSame($testEntryA, array_shift($mappingArray));
        assertSame($testEntryB, array_shift($mappingArray));
    }

    /**
     * Tests
     */
    public function testPersistance_postAdd()
    {
        $testEntry = $this->generateEntry();

        $testObj = NameMap::getInstance();
        $testObj->add($testEntry);
        unset($testObj);

        $testObj = NameMap::getInstance();

        assertEquals($testEntry, $testObj->findById($testEntry->getId()));
    }

    /**
     * Tests NameMap->update()
     */
    public function testUpdate_singleEntry()
    {
        $testObj = NameMap::getInstance();

        $testEntry = $this->generateEntry();

        ReflectionHelper::setObjectProperty(NameMap::class, 'mappings', array($testEntry), $testObj);

        $updateEntry = $this->generateEntry();
        $updateEntry->setId($testEntry->getId());

        $result = $testObj->update($updateEntry);

        assertSame($updateEntry, $result);

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $testObj);

        assertNotEmpty($mappingArray);
        assertCount(1, $mappingArray);

        assertEquals($updateEntry, array_shift($mappingArray));
    }

    /**
     * Tests NameMap->update()
     */
    public function testUpdate_beginning()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $updateEntry = $this->generateEntry();
        $updateEntry->setId($testEntryA->getId());

        $result = $testObj->update($updateEntry);

        assertSame($updateEntry, $result);

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $testObj);

        assertNotEmpty($mappingArray);
        assertCount(3, $mappingArray);

        assertEquals($updateEntry, array_shift($mappingArray));
        assertEquals($testEntryB, array_shift($mappingArray));
        assertEquals($testEntryC, array_shift($mappingArray));
    }

    /**
     * Tests NameMap->update()
     */
    public function testUpdate_middle()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $updateEntry = $this->generateEntry();
        $updateEntry->setId($testEntryB->getId());

        $result = $testObj->update($updateEntry);

        assertSame($updateEntry, $result);

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $testObj);

        assertNotEmpty($mappingArray);
        assertCount(3, $mappingArray);

        assertEquals($testEntryA, array_shift($mappingArray));
        assertEquals($updateEntry, array_shift($mappingArray));
        assertEquals($testEntryC, array_shift($mappingArray));
    }

    /**
     * Tests NameMap->update()
     */
    public function testUpdate_end()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $updateEntry = $this->generateEntry();
        $updateEntry->setId($testEntryC->getId());

        $result = $testObj->update($updateEntry);

        assertSame($updateEntry, $result);

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $testObj);

        assertNotEmpty($mappingArray);
        assertCount(3, $mappingArray);

        assertEquals($testEntryA, array_shift($mappingArray));
        assertEquals($testEntryB, array_shift($mappingArray));
        assertEquals($updateEntry, array_shift($mappingArray));
    }

    /**
     * Tests
     */
    public function testPersistance_postUpdate()
    {
        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();

        $testObj = NameMap::getInstance();
        $testObj->add($testEntryA);

        $testEntryB->setId($testEntryA->getId());
        $testObj->update($testEntryB);
        unset($testObj);

        $testObj = NameMap::getInstance();

        assertEquals($testEntryB, $testObj->findById($testEntryB->getId()));
    }

    /**
     * Tests NameMap->delete(), from the begining of the array
     */
    public function testDelete_beginning()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $result = $testObj->delete($testEntryA->getId());

        assertEquals($testEntryA, $result);

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $testObj);

        assertNotEmpty($mappingArray);
        assertCount(2, $mappingArray);

        assertEquals($testEntryB, array_shift($mappingArray));
        assertEquals($testEntryC, array_shift($mappingArray));
    }

    /**
     * Tests NameMap->delete(), from the middle of the array
     */
    public function testDelete_middle()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $result = $testObj->delete($testEntryB->getId());

        assertEquals($testEntryB, $result);

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $testObj);

        assertNotEmpty($mappingArray);
        assertCount(2, $mappingArray);

        assertEquals($testEntryA, array_shift($mappingArray));
        assertEquals($testEntryC, array_shift($mappingArray));
    }

    /**
     * Tests NameMap->delete(), from the middle of the array
     */
    public function testDelete_end()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $result = $testObj->delete($testEntryC->getId());

        assertEquals($testEntryC, $result);

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $testObj);

        assertNotEmpty($mappingArray);
        assertCount(2, $mappingArray);

        assertEquals($testEntryA, array_shift($mappingArray));
        assertEquals($testEntryB, array_shift($mappingArray));
    }

    /**
     * Tests NameMap->delete(), from the last entry in the array
     */
    public function testDelete_last()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();

        ReflectionHelper::setObjectProperty(NameMap::class, 'mappings', array($testEntryA), $testObj);

        $result = $testObj->delete($testEntryA->getId());

        assertEquals($testEntryA, $result);

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $testObj);

        assertEmpty($mappingArray);
    }

    /**
     * Tests
     */
    public function testPersistance_postDelete()
    {
        $testEntryA = $this->generateEntry();

        $testObj = NameMap::getInstance();
        $testObj->add($testEntryA);

        $testObj->delete($testEntryA->getId());
        unset($testObj);

        $testObj = NameMap::getInstance();

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $testObj);

        assertEmpty($mappingArray);
    }


    /**
     * Tests
     */
    public function testPersistance_postDeleteFirstEntry()
    {
        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();

        $testObj = NameMap::getInstance();
        $testObj->add($testEntryA);
        $testObj->add($testEntryB);

        $testObj->delete($testEntryA->getId());
        unset($testObj);

        $testObj = NameMap::getInstance();

        $mappingArray = ReflectionHelper::getObjectProperty(NameMap::class, 'mappings', $testObj);

        assertNotEmpty($mappingArray);
        assertCount(1, $mappingArray);
    }

    /**
     * Tests NameMap->findById()
     */
    public function testFindById_beginning()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $result = $testObj->findById($testEntryA->getId());

        assertEquals($testEntryA, $result);
    }

    /**
     * Tests NameMap->findById()
     */
    public function testFindById_middle()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $result = $testObj->findById($testEntryB->getId());

        assertEquals($testEntryB, $result);
    }

    /**
     * Tests NameMap->findById()
     */
    public function testFindById_end()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $result = $testObj->findById($testEntryC->getId());

        assertEquals($testEntryC, $result);
    }

    /**
     * Tests NameMap->findByName()
     */
    public function testFindByName_beginning()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $result = $testObj->findByName($testEntryA->getType(), $testEntryA->getInName());

        assertEquals($testEntryA, $result);
    }

    /**
     * Tests NameMap->findByName()
     */
    public function testFindByName_middle()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $result = $testObj->findByName($testEntryB->getType(), $testEntryB->getInName());

        assertEquals($testEntryB, $result);
    }

    /**
     * Tests NameMap->findByName()
     */
    public function testFindByName_end()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $result = $testObj->findByName($testEntryC->getType(), $testEntryC->getInName());

        assertEquals($testEntryC, $result);
    }

    /**
     * Tests NameMap->getMappedName()
     */
    public function testGetMappedName_withEntry()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $result = $testObj->getMappedName($testEntryB->getType(), $testEntryB->getInName());

        assertEquals($testEntryB->getOutName(), $result);
    }

    /**
     * Tests NameMap->getMappedName()
     */
    public function testGetMappedName_withoutEntry()
    {
        $testObj = NameMap::getInstance();

        $testEntryA = $this->generateEntry();
        $testEntryB = $this->generateEntry();
        $testEntryC = $this->generateEntry();

        ReflectionHelper::setObjectProperty(
            NameMap::class,
            'mappings',
            array($testEntryA, $testEntryB, $testEntryC),
            $testObj
            );

        $testName = str_shuffle(MD5(microtime()));

        $result = $testObj->getMappedName('test', $testName);

        assertEquals($testName, $result);
    }
}
