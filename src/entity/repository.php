<?
namespace RhodeCode\Entity;

class Repository extends \RhodeCode\Entity
{
	const REPO_TYPE_GIT = 'git';
	const REPO_TYPE_HG = 'hg';

	protected $type = 0;
	protected $url = '';
	//protected $private = true;

	// todo: implement 'order' and 'filter' in $parameters
	public static function find(array $parameters = array(), \RhodeCode\Connection $connection = null)
	{
		// todo: do smth when $connection equals to null
		$res = $connection->call('get_repos', array());

		$list = array();
		if($res->isSuccessful())
		{
			$data = $res->getData();

			if(is_array($data['result']))
			{
				foreach($data['result'] as $rd)
				{
					$r = new static(array(
						'name' => $rd['repo_name'],
						'type' => $rd['repo_type'],
						'id' => $rd['repo_id'],
						'description' => $rd['description'],
					));
					$r->setConnection($connection);

					$list[] = $r;
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

		if(array_key_exists('type', $parameters))
		{
			$this->setType($parameters['type']);
		}

		parent::__construct($parameters);
	}

	public function isGIT()
	{
		return $this->getType() == static::REPO_TYPE_GIT;
	}

	public function isHG()
	{
		return $this->getType() == static::REPO_TYPE_HG;
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getURL()
	{
		return $this->url;
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

			if(!Group::exists($parentName, $this->getConnection()))
			{
				$parent = new Group(array(
					'name' => $parentName,
				));
				$parent->setConnection($this->getConnection());
				$parent->save();
			}
		}

		if(!$this->getId())
		{
			$res = $this->getConnection()->call('create_repo', array(
				'repo_name' => $this->getName(),
				'repo_type' => $this->getType(),
				'description' => $this->getDescription(),
			));

			if($res->isSuccessful())
			{
				$data = $res->getData();
				$rcResult = $data['result'];

				//$this->setId($rcResult['repo_group']['group_id']);
				$this->clearChangedFields();

				$res->setData(null);
			}
		}
		else
		{
			$delta = array(
				'repoid' => $this->getName(),
			);
			foreach($this->getChangedFields() as $field)
			{
				$delta[static::mapNormalNames2Stupid($field)] = call_user_func(array($this, 'get'.$field));
			}

			$res = $this->getConnection()->call('update_repo', $delta);
		}

		return $res;
	}

	public function delete()
	{
		return $this->getConnection()->call('delete_repo', array('repoid' => $this->getName()));
	}

	public function dropCache()
	{
		return $this->getConnection()->call('invalidate_cache', array('repoid' => $this->getName()));
	}

	private static function mapNormalNames2Stupid($name)
	{
		static $map;

		if(!$map)
		{
			$map = array(
				'name' => 'name',
				'description' => 'description',
			);
		}

		return $map[$name];
	}
}