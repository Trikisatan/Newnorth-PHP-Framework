<?
namespace Framework\Newnorth;

abstract class ADataMember {
	/* Instance methods */

	public abstract function Parse($Value);

	public abstract function ToDbExpression($Value);

	public abstract function Set(\Framework\Newnorth\DataType $DataType, array $Parameters);

	public abstract function Increment(\Framework\Newnorth\DataType $DataType, array $Parameters);

	public abstract function Decrement(\Framework\Newnorth\DataType $DataType, array $Parameters);
}
?>