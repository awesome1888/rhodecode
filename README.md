# RhodeCode integration

A simple PHP binding to [RhodeCode server API](https://rhodecode.com/).

## Usage

### Class \RhodeCode\Connection

`\RhodeCode\Connection` class represents the connection with the chosen server.

Example:
~~~~
$connection = new \RhodeCode\Connection([
    'host' => 'your-server-domain.net',
    'apiKey' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'useSSL' => true,
]);
~~~~

Option details:

* `host` - domain your `rhodecode` server is accessible by
* `apiKey` - access token generated for the user in server admin panel
* `useSSL` - set to `true` if you want to access your server over `SSL`
* `port` - server port. By default port equals to `80` for insecure connections, and to `443` for secure ones

### Class \RhodeCode\Entity\Group

`\RhodeCode\Entity\Group` class represents the abstraction over the `rhodecode repository group`.

Some examples.

Create new `group`:
~~~~
$group = new \RhodeCode\Entity\Group(['name' => 'my/new/folder', 'description' => 'My first group']);
$group->setConnection($connection);
$result = $group->save();
if($result->isSuccessful())
{
    print($group->getId().' created'.PHP_EOL);
}
else
{
    print_r($result->getErrors());
}
~~~~

Get `group` list:
~~~~
$result = \RhodeCode\Entity\Group::find([], $connection); // first argument is future-reserved
if($result->isSuccessful())
{
    foreach($result->getData() as $group)
    {
        print($group->getName().' ['.$group->getId().']'.PHP_EOL);  
    }
}
else
{
    print_r($result->getErrors());
}
~~~~

Remove `group`:
~~~~
$group = new \RhodeCode\Entity\Group(['name' => 'my/new/folder']);
$group->setConnection($connection);
$result = $group->delete();
if($result->isSuccessful())
{
    print($group->getName().' deleted'.PHP_EOL);
}
else
{
    print_r($result->getErrors());
}
~~~~

### Class \RhodeCode\Entity\Repository

`\RhodeCode\Entity\Repository` class represents the abstraction over the `rhodecode repository`.

Some examples.

Create new (`GIT`) `repository`:
~~~~
$repository = new \RhodeCode\Entity\Repository([
    'name' => 'my/new/folder/newrepo',
    'description' => 'My first repository',
    'type' => \RhodeCode\Entity\Repository::REPO_TYPE_GIT, // also could be REPO_TYPE_HG
]);
$repository->setConnection($connection);
$result = $repository->save();
if($result->isSuccessful())
{
    print($repository->getName().' ['.$repository->getId().'] created'.PHP_EOL);
}
else
{
    print_r($result->getErrors());
}
~~~~

Get `repository` list:
~~~~
$result = \RhodeCode\Entity\Repository::find([], $connection); // first argument is future-reserved
if($result->isSuccessful())
{
    foreach($result->getData() as $repository)
    {
        print($repository->getName().' ['.$repository->getId().']'.PHP_EOL);  
    }
}
else
{
    print_r($result->getErrors());
}
~~~~

Remove `repository`:
~~~~
$repository = new \RhodeCode\Entity\Repository([
    'name' => 'my/new/folder/newrepo'
]);
$group->setConnection($connection);
$result = $repository->delete();
if($result->isSuccessful())
{
    print($repository->getName().' deleted'.PHP_EOL);
}
else
{
    print_r($result->getErrors());
}
~~~~

## Current status

v0.0.1a

See [vendor`s API Reference](https://docs.rhodecode.com/RhodeCode-Enterprise/api/api.html) for further details.