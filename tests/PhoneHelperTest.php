<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PhoneHelperTest extends KernelTestCase
{
    private $phoneHelper;

    protected function setUp()
    {
        self::bootKernel();
        $this->phoneHelper = self::$container->get('App\Service\PhoneHelper');
    }

    /**
     * Data provider for 'testValidatePhone'.
     * @return array[]
     */
    public static function phoneNumbers()
    {
        return [
            '0034666123123' => ['0034666123123', true],
            '0034665555456' => ['0034666123123', true],
            '9999654654654' => ['9999654654654', false],
            '34654654654' => ['34654654654', true],
        ];
    }

    /**
     * @dataProvider phoneNumbers
     * @test
     * @param $phone
     * @param $expected
     */
    public function testValidatePhone($phone, $expected)
    {
        $valid = $this->phoneHelper->validate($phone);

        $this->assertEquals($expected, $valid);
    }

    /**
     * @test
     */
    public function testGenerateCode() {
        $phone = '0034677589104';
        $timestamp = strtotime('2020-10-24 16:16:47');
        $code = '7892';

        $generatedCode = $this->phoneHelper->generateCode($phone, $timestamp);

        $this->assertEquals($code, $generatedCode);
    }
}