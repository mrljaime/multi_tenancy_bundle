<?php

namespace Hakam\MultiTenancyBundle\Tests\Unit\Adapter;

use Hakam\MultiTenancyBundle\Enum\DatabaseStatusEnum;
use PHPUnit\Framework\TestCase;
use Hakam\MultiTenancyBundle\Adapter\DefaultDsnGenerator;
use Hakam\MultiTenancyBundle\Config\TenantConnectionConfigDTO;
use Hakam\MultiTenancyBundle\Enum\DriverTypeEnum;

class DefaultDsnGeneratorTest extends TestCase
{
    private DefaultDsnGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new DefaultDsnGenerator();
    }

    /**
     * @return array<string, array{0: TenantConnectionConfigDTO, 1: string}>
     */
    public function provideGenerate(): array
    {
        return [
            'mysql with password' => [
                TenantConnectionConfigDTO::fromArgs(
                    identifier: 11,
                    driver: DriverTypeEnum::MYSQL,
                    dbStatus: DatabaseStatusEnum::DATABASE_CREATED,
                    host: 'example.com',
                    port: 3306,
                    dbname: 'db1',
                    user: 'foo',
                    password: 'bar'
                ),
                'mysql://foo:bar@example.com:3306/db1'
            ],
            'mysql without password' => [
                TenantConnectionConfigDTO::fromArgs(
                    identifier: 12,
                    driver: DriverTypeEnum::MYSQL,
                    dbStatus: DatabaseStatusEnum::DATABASE_CREATED,
                    host: 'host',
                    port: 3307,
                    dbname: 'testdb',
                    user: 'user',
                    password: null
                ),
                'mysql://user@host:3307/testdb'
            ],
            'postgres with password' => [
                TenantConnectionConfigDTO::fromArgs(
                    identifier: 13,
                    driver: DriverTypeEnum::POSTGRES,
                    dbStatus: DatabaseStatusEnum::DATABASE_CREATED,
                    host: 'pg.example.org',
                    port: 5432,
                    dbname: 'pgdb',
                    user: 'pguser',
                    password: 'pgpass'
                ),
                'pgsql://pguser:pgpass@pg.example.org:5432/pgdb'
            ],
            'postgres without password' => [
                TenantConnectionConfigDTO::fromArgs(
                    identifier: 14,
                    driver: DriverTypeEnum::POSTGRES,
                    dbStatus: DatabaseStatusEnum::DATABASE_CREATED,
                    host: 'localhost',
                    port: 5433,
                    dbname: 'otherdb',
                    user: 'admin',
                    password: null
                ),
                'pgsql://admin@localhost:5433/otherdb'
            ],
            'sqlite absolute path' => [
                TenantConnectionConfigDTO::fromArgs(
                    identifier: 15,
                    driver: DriverTypeEnum::SQLITE,
                    dbStatus: DatabaseStatusEnum::DATABASE_CREATED,
                    host: '',
                    port: 0,
                    dbname: '/data/tenant.sqlite',
                    user: '',
                    password: null
                ),
                'sqlite:///data/tenant.sqlite'
            ],
            'sqlite relative path' => [
                TenantConnectionConfigDTO::fromArgs(
                    identifier: 16,
                    driver: DriverTypeEnum::SQLITE,
                    dbStatus: DatabaseStatusEnum::DATABASE_CREATED,
                    host: '',
                    port: 0,
                    dbname: 'tenant.sqlite',
                    user: '',
                    password: null
                ),
                'sqlite:///tenant.sqlite'
            ],
        ];
    }

    /**
     * @dataProvider provideGenerate
     */
    public function testGenerate(TenantConnectionConfigDTO $dto, string $expected): void
    {
        $dsn = $this->generator->generate($dto);
        $this->assertSame($expected, $dsn);
    }

    /**
     * @return array<string, array{0: TenantConnectionConfigDTO, 1: string}>
     */
    public function provideGenerateMaintenance(): array
    {
        return [
            'mysql maintenance' => [
                TenantConnectionConfigDTO::fromArgs(
                    identifier: 11,
                    driver: DriverTypeEnum::MYSQL,
                    dbStatus: DatabaseStatusEnum::DATABASE_CREATED,
                    host: 'example.com',
                    port: 3306,
                    dbname: 'db1',
                    user: 'foo',
                    password: 'bar'
                ),
                'mysql://foo:bar@example.com:3306'
            ],
            'postgres maintenance' => [
                TenantConnectionConfigDTO::fromArgs(
                    identifier: 12,
                    driver: DriverTypeEnum::POSTGRES,
                    dbStatus: DatabaseStatusEnum::DATABASE_CREATED,
                    host: 'pg.example.org',
                    port: 5432,
                    dbname: 'pgdb',
                    user: 'pguser',
                    password: 'pgpass'
                ),
                'pgsql://pguser:pgpass@pg.example.org:5432/postgres'
            ],
            'sqlite maintenance absolute' => [
                TenantConnectionConfigDTO::fromArgs(
                    identifier: 13,
                    driver: DriverTypeEnum::SQLITE,
                    dbStatus: DatabaseStatusEnum::DATABASE_CREATED,
                    host: '',
                    port: 0,
                    dbname: '/data/tenant.sqlite',
                    user: '',
                    password: null
                ),
                'sqlite:///data/tenant.sqlite'
            ],
        ];
    }

    /**
     * @dataProvider provideGenerateMaintenance
     */
    public function testGenerateMaintenanceDsn(TenantConnectionConfigDTO $dto, string $expected): void
    {
        $dsn = $this->generator->generateMaintenanceDsn($dto);
        $this->assertSame($expected, $dsn);
    }
}
