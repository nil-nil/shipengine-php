<?php declare(strict_types=1);

namespace ShipEngine\Service\Test;

use PHPUnit\Framework\TestCase;

use ShipEngine\Exception\ErrorException;
use ShipEngine\Model\AddressQuery;
use ShipEngine\ShipEngine;

/**
 * @covers \ShipEngine\ShipEngine
 * @covers \ShipEngine\ShipEngineClient
 * @covers \ShipEngine\ShipEngineConfig
 * @covers \ShipEngine\Model\Address
 * @covers \ShipEngine\Model\AddressQuery
 * @covers \ShipEngine\Model\AddressQueryResult
 * @covers \ShipEngine\Service\AbstractService
 * @covers \ShipEngine\Service\AddressesService
 * @covers \ShipEngine\Service\ServiceFactory
 */
final class AddressesServiceTest extends TestCase
{
    private ShipEngine $shipengine;
    
    public static function setUpBeforeClass(): void
    {
        exec('hoverctl import simengine/v1/addresses.json');
    }
        
    public static function tearDownAfterClass(): void
    {
        exec('hoverctl delete --force simengine/v1/addresses.json');
    }
    
    protected function setUp(): void
    {
        $this->shipengine = new ShipEngine(['api_key' => 'foobar', 'base_uri' => 'http://localhost:8500/v1']);
    }

    public function testAddressQuery(): void
    {
        $yankee_stadium = new AddressQuery(['1 E 161 St'], 'The Bronx', 'NY', '10451', 'US');
        $result = $this->shipengine->addresses->query($yankee_stadium);
        
        $this->assertEmpty($result->exceptions);
    }

    public function testAddressQueryError(): void
    {
        $dodger_stadium = new AddressQuery(['1000 Elysion Ave'], 'Los Angeles', 'CA', '90012', 'US');
        $result = $this->shipengine->addresses->query($dodger_stadium);
        
        $this->assertNull($result->normalized);
        $this->assertNotEmpty($result->errors());
    }
    
    public function testAddressValidateValid(): void
    {
        $yankee_stadium = new AddressQuery(['1 E 161 St'], 'The Bronx', 'NY', '10451', 'US');
        $valid = $this->shipengine->addresses->validate($yankee_stadium);
        
        $this->assertTrue($valid);
    }

    public function testAddressValidateInvalid(): void
    {
        $dodger_stadium = new AddressQuery(['1000 Elysion Ave'], 'Los Angeles', 'CA', '90012', 'US');
        $valid = $this->shipengine->addresses->validate($dodger_stadium);

        $this->assertFalse($valid);
    }

    public function testAddressNormalizeNormal(): void
    {
        $yankee_stadium = new AddressQuery(['1 E 161 St'], 'The Bronx', 'NY', '10451', 'US');
        $normalized = $this->shipengine->addresses->normalize($yankee_stadium);

        $this->assertEquals($yankee_stadium->state_province, $normalized->state_province);
    }

    public function testAddressNormalizeAbnormal(): void
    {
        $this->expectException(ErrorException::class);
        $dodger_stadium = new AddressQuery(['1000 Elysion Ave'], 'Los Angeles', 'CA', '90012', 'US');
        $normalized = $this->shipengine->addresses->normalize($dodger_stadium);
    }
}