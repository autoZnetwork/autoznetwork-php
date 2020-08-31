<?php

namespace AutozNetwork\Tests\Resource;

use AutozNetwork\AutozNetwork;
use PHPUnit\Framework\TestCase;

class InventoryTest extends TestCase
{
    /**
     * @var string[]
     */
    private array $inventoryParams;
    /**
     * @var AutozNetwork
     */
    private AutozNetwork $autozNetwork;

    protected function setUp() : void
    {
        $this->autozNetwork = new AutozNetwork(AUTOZNETWORK_TEST_API_KEY);
        $this->inventoryParams = [
            'name' => 'Larry Lobster',
            'address_line1' => '185 Berry St',
            'address_line2' => 'Ste 6100',
            'address_city' => 'San Francisco',
            'address_state' => 'CA',
            'address_country' => 'US',
            'address_zip' => '94107',
            'email' => 'larry@lob.com',
        ];
    }

    public function testCreate()
    {
        $vehicle = $this->autozNetwork->inventory()->create($this->inventoryParams);

        $this->assertTrue(is_array($vehicle));
        $this->assertTrue(array_key_exists('id', $vehicle));
    }

    public function testDelete()
    {
        $vehicle = $this->autozNetwork->inventory()->create($this->inventoryParams);
        $id = $vehicle['id'];
        $deleted = $this->autozNetwork->inventory()->delete($id);
        $this->assertTrue(is_array($deleted));
    }

    public function testGet()
    {
        $id = $this->autozNetwork->inventory()->create($this->inventoryParams)['id'];
        $vehicles = $this->autozNetwork->inventory()->get($id);

        $this->assertTrue(is_array($vehicles));
        $this->assertTrue($vehicles['id'] === $id);
    }

    public function testAll()
    {
        $vehicles = $this->autozNetwork->inventory()->all();

        $this->assertTrue(is_array($vehicles));
    }
}
