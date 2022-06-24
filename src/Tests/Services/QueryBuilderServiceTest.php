<?php
namespace LightWine\Tests\Services;

use PHPUnit\Framework\TestCase;

use LightWine\Modules\QueryBuilder\Enums\QueryExtenderEnum;
use LightWine\Modules\QueryBuilder\Enums\QueryOperatorsEnum;
use LightWine\Modules\QueryBuilder\Services\QueryBuilderService;

final class QueryBuilderServiceTest extends TestCase
{
    private QueryBuilderService $queryBuilder;

    public function setup(): void {
        $this->queryBuilder = new QueryBuilderService();
    }

    /**
     * @test Test select function of the QueryBuilder service class
     */
    public function testSelectFunction(): void {
        $this->queryBuilder->Clear();
        $this->queryBuilder->Select("table_test");
        $this->assertEquals("SELECT * FROM table_test", trim($this->queryBuilder->Render()));
    }

    /**
     * @test Test insert function of the QueryBuilder service class
     */
    public function testInsertFunction(): void {
        $this->queryBuilder->Clear();
        $this->queryBuilder->Insert("table_test", "foo", "bar");
        $this->assertEquals("INSERT INTO table_test (`foo`) VALUES ('bar')", $this->queryBuilder->Render());
    }

    /**
     * @test Test delete function of the QueryBuilder service class
     */
    public function testDeleteFunction(): void {
        $this->queryBuilder->Clear();
        $this->queryBuilder->Delete("table_test");
        $this->queryBuilder->Where(QueryExtenderEnum::Nothing, "id", QueryOperatorsEnum::EqualTo, "123");
        $this->assertEquals("DELETE FROM table_test WHERE `id` = '123'", $this->queryBuilder->Render());
    }

    /**
     * @test Test order by function of the QueryBuilder service class
     */
    public function testOrderByFunction(): void {
        $this->queryBuilder->Clear();
        $this->queryBuilder->Select("table_test");
        $this->queryBuilder->Order("foo", "ASC");

        $this->assertEquals("SELECT * FROM table_test  ORDER BY foo ASC", trim($this->queryBuilder->Render()));
    }

    /**
     * @test Test update function of the QueryBuilder service class
     */
    public function testUpdateFunction(): void {
        $this->queryBuilder->Clear();
        $this->queryBuilder->Update("table_test", "foo", "bar");

        $this->assertEquals("UPDATE table_test SET `foo` = 'bar'", trim($this->queryBuilder->Render()));
    }

    /**
     * @test Test limit function of the QueryBuilder service class
     */
    public function testLimitFunction(): void {
        $this->queryBuilder->Clear();
        $this->queryBuilder->Select("table_test");
        $this->queryBuilder->Limit(10);

        $this->assertEquals("SELECT * FROM table_test  LIMIT 10", trim($this->queryBuilder->Render()));
    }
}