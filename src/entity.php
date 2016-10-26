<?
namespace RhodeCode;

abstract class Entity
{
	private $connection = null;
	protected $changedFields = array();

	protected $id = '';
	protected $name = '';
	protected $description = '';
	//protected $owner = '';

	// todo: for increasing perfomance, we need to use object pooling, so download all groups and 
	// todo: keep this pool up-to-date
	protected $pool = array();

	public function __construct($parameters = array())
	{
		// todo: there could be also id\name here
		if(!is_array($parameters))
		{
			$parameters = array();
		}

		if(array_key_exists('id', $parameters))
		{
			$this->setId($parameters['id']);
		}
		if(array_key_exists('name', $parameters))
		{
			$this->setName($parameters['name']);
		}
		if(array_key_exists('description', $parameters))
		{
			$this->setDescription($parameters['description']);
		}

		$this->clearChangedFields();
	}

	public function setConnection(Connection $connection)
	{
		$this->connection = $connection;
	}

	public function getConnection()
	{
		return $this->connection;
	}

	public static function find(array $parameters = array(), Connection $connection = null)
	{
		return new Result();
	}

	public static function get($id, Connection $connection = null)
	{
		return new Result();
	}

	protected function addChangedField($fieldName)
	{
		$this->changedFields[$fieldName] = true;
	}

	public function getChangedFields()
	{
		return array_keys($this->changedFields);
	}

	public static function exists($id, \RhodeCode\Connection $connection = null)
	{
		// todo: do smth when $connection equals to null
		return static::get($id, $connection)->isSuccessful();
	}

	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
		$this->addChangedField('name');
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		$this->addChangedField('name');
	}

	protected function clearChangedFields()
	{
		$this->changedFields = array();
	}
}