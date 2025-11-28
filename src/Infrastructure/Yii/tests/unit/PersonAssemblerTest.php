<?php

use PHPUnit\Framework\TestCase;
use app\models\Person as PersonAR;
use Chiarelli\DddApp\Infrastructure\Assembler\PersonAssembler;
use Chiarelli\DddApp\Domain\Entity\Person as DomainPerson;

final class PersonAssemblerTest extends TestCase
{
    public function testToDomainMapsFieldsCorrectly(): void
    {
        $ar = new PersonAR();
        $ar->id = 11;
        $ar->first_name = 'John';
        $ar->middle_name = 'M';
        $ar->last_name = 'Doe';
        $ar->birthdate = '2000-05-20';
        $ar->gender = 'M';

        $domain = PersonAssembler::toDomain($ar);

        $this->assertInstanceOf(DomainPerson::class, $domain);
        $this->assertSame(11, $domain->getId());
        $this->assertSame('John', $domain->getFirstName());
        $this->assertSame('M', $domain->getMiddleName());
        $this->assertSame('Doe', $domain->getLastName());
        $this->assertSame('2000-05-20', $domain->getBirthdate()->format('Y-m-d'));
        $this->assertSame('M', $domain->getGender());
    }

    public function testToDomainThrowsWhenBirthdateMissing(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $ar = new PersonAR();
        $ar->id = 12;
        $ar->first_name = 'Missing Birth';

        PersonAssembler::toDomain($ar);
    }
}