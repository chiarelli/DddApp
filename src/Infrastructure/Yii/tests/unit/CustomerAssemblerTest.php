<?php

use PHPUnit\Framework\TestCase;
use app\models\Customer as CustomerAR;
use Chiarelli\DddApp\Infrastructure\Assembler\CustomerAssembler;
use Chiarelli\DddApp\Domain\Entity\Customer as DomainCustomer;

final class CustomerAssemblerTest extends TestCase
{
    public function testToDomainMapsFieldsCorrectly(): void
    {
        $ar = new CustomerAR();
        $ar->id = 7;
        $ar->fullname = 'Alice Example';
        $ar->birthdate = '1990-02-03';

        $domain = CustomerAssembler::toDomain($ar);

        $this->assertInstanceOf(DomainCustomer::class, $domain);
        $this->assertSame(7, $domain->getId());
        $this->assertSame('Alice Example', $domain->getFullName());
        $this->assertSame('1990-02-03', $domain->getBirthdate()->format('Y-m-d'));
    }

    public function testToDomainThrowsWhenBirthdateMissing(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $ar = new CustomerAR();
        $ar->id = 8;
        $ar->fullname = 'No Birth';

        CustomerAssembler::toDomain($ar);
    }
}