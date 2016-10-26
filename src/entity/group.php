<?
namespace RhodeCode\Entity;

class Group extends \RhodeCode\Entity
{
	protected $id = null;

	//protected $permissions = '';
	protected $parentId = null;

	// todo: implement 'order' and 'filter' in $parameters
	public static function find(array $parameters = array(), \RhodeCode\Connection $connection = null)
	{
		// todo: do smth when $connection equals to null
		$res = $connection->call('get_repo_groups', array());

		$list = array();
		if($res->isSuccessful())
		{
			$data = $res->getData();

			if(is_array($data['result']))
			{
				foreach($data['result'] as $gd)
				{
					$g = new static(array(
						'name' => $gd['group_name'],
						'id' => $gd['group_id'],
						'parentId' => $gd['parent_group'],
					));
					$g->setConnection($connection);

					$list[] = $g;
				}
			}
		}
		$res->setData($list);

		return $res;
	}

	public static function get($id, \RhodeCode\Connection $connection = null)
	{
		// todo: do smth when $connection equals to null
		$res = $connection->call('get_repo_group', array('repogroupid' => $id));

		$group = null;
		if($res->isSuccessful())
		{
			$data = $res->getData();
			$gd = $data['result'];

			$group = new static(array(
				'name' => $gd['group_name'],
				'id' => $gd['group_id'],
				'parentId' => $gd['parent_group'],
				'description' => $gd['group_description']
			));
			$group->setConnection($connection);
		}
		$res->setData($group);

		return $res;
	}

	public function __construct($parameters = array())
	{
		if(!is_array($parameters))
		{
			$parameters = array();
		}

		if(array_key_exists('parentId', $parameters))
		{
			$this->setParentId($parameters['parentId']);
		}

		parent::__construct($parameters);
	}

	public function setParentId($id)
	{
		$this->parentId = $id;
		$this->addChangedField('parentId');
	}

	public function getParentId()
	{
		return $this->parentId;
	}

	public function save()
	{
		// pre-create parent directory, if needed
		$name = $this->getName();
		$path = explode('/', $name);
		if(count($path) > 1) // is a sub-group
		{
			// todo: this is too damn slow, use cacheing\object pooling here!

			$basePath = $path;
			array_pop($basePath);
			$parentName = implode('/', $basePath);

			if(!static::exists($parentName, $this->getConnection()))
			{
				$parent = new static(array(
					'name' => $parentName,
				));
				$parent->setConnection($this->getConnection());
				$parent->save();
			}
		}

		if(!$this->getId())
		{
			$res = $this->getConnection()->call('create_repo_group', array(
				'group_name' => $this->getName(),
				'description' => $this->getDescription(),
			));

			if($res->isSuccessful())
			{
				$data = $res->getData();
				$rcResult = $data['result'];

				$this->setId($rcResult['repo_group']['group_id']);
				$this->clearChangedFields();

				$res->setData(null);
			}
		}
		else
		{
			$delta = array(
				'repogroupid' => $this->getId()
			);
			foreach($this->getChangedFields() as $field)
			{
				$delta[static::mapNormalNames2Stupid($field)] = call_user_func(array($this, 'get'.$field));
			}

			$res = $this->getConnection()->call('update_repo_group', $delta);
		}

		return $res;
	}

	public function delete()
	{
		// todo: remove all sub-groups recursively
		return $this->getConnection()->call('delete_repo_group', array('repogroupid' => $this->getName()));
	}

	private static function mapNormalNames2Stupid($name)
	{
		static $map;

		if(!$map)
		{
			$map = array(
				'name' => 'group_name',
				'parentid' => 'parent',
				'description' => 'description',
			);
		}

		return $map[$name];
	}
}