<?php
namespace LightWine\Modules\QueryBuilder\Enums;

abstract class QueryOperatorsEnum
{
    const EqualTo = "=";
    const GreaterThan = ">";
    const LessThan = "<";
    const GreaterThanOrEqualTo = ">=";
    const LessThanOrEqualTo = "<=";
    const NotEqualTo = "<>";
    const FindInSet = "IN_SET";
    const Like = "LIKE";
}
?>