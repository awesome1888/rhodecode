<?
namespace RhodeCode;

final class Connection
{
	protected $host = null;
	protected $port = 5000;
	protected $apiKey = null;
	protected $useSSL = false;

	protected $frameIndex = 1;

	public function __construct(array $options = array())
	{
		if(array_key_exists('host', $options))
		{
			$this->setHost($options['host']);	
		}
		if(array_key_exists('port', $options))
		{
			$this->setPort($options['port']);
		}
		if(array_key_exists('apiKey', $options))
		{
			$this->setAPIKey($options['apiKey']);
		}
		if(array_key_exists('useSSL', $options))
		{
			$this->useSSL = !!$options['useSSL'];
		}
	}

	public function setAPIKey($key)
	{
		$key = trim((string) $key);

		if(!preg_match('#^[a-z0-9]+$#i', $key))
		{
			throw new ArgumentException('Illegal API Key');
		}

		$this->apiKey = $key;
	}

	public function getAPIKey()
	{
		return $this->apiKey;
	}

	public function setHost($host)
	{
		$host = trim((string) $host);

		if($host)
		{
			$this->host = $host;
		}
		else
		{
			throw new ArgumentException('Illegal host');
		}
	}

	public function getHost()
	{
		return $this->host;
	}

	public function setPort($port)
	{
		$port = intval($port);

		if($port)
		{
			$this->port = $port;
		}
	}

	public function getPort()
	{
		return $this->port;
	}

	public function call($method, array $arguments = array())
	{
		$host = $this->getHost();
		$port = $this->getPort();
		$key = $this->getAPIKey();

		$result = new Result();

		if($host == null)
		{
			$result->addError('Host was not defined');
		}
		if($key == null)
		{
			$result->addError('API key was not defined');
		}

		if(!$result->isSuccessful())
		{
			return $result;
		}

		$request = json_encode(array(
			'id' => $this->frameIndex,
			'api_key' => $this->apiKey,
			'method' => $method,
			'args' => $arguments
		));

		$ch = curl_init();

		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, ($this->useSSL ? 'https' : 'http')."://".$this->getHost()."/_admin/api");
		curl_setopt($ch, CURLOPT_PORT, $this->getPort());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: '.strlen($request)
		));

		// grab URL and pass it to the browser
		$responce = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$result = new Result();

		if($httpCode != 200)
		{
			$result->addError('HTTP request failed: '.intval($httpCode));
		}
		elseif($responce == '' || $responce == '""')
		{
			$result->addError('Empty response, check your id in the request JSON');
		}
		else
		{
			$responce = json_decode($responce, true);

			if(!empty($responce['error']))
			{
				$result->addError('Rhode API request failed ('.$method.'): '.$responce['error']);
			}

			$this->frameIndex++;

			$result->setData($responce);
		}

		return $result;
	}
}

final class Result
{
	private $errors = array();
	private $data = array();

	public function isSuccessful()
	{
		return empty($this->errors);
	}

	public function addError($message, $code = '')
	{
		$this->errors[] = array('CODE' => $code, 'MESSAGE' => $message);
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function getData()
	{
		return $this->data;
	}

	public function setData($data)
	{
		$this->data = $data;
	}
}

class ArgumentException extends \Exception {}