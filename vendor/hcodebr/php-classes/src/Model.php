<?php 

namespace Hcode;

class Model {

	private $values = [];

	public function __call($name, $args)
	{
		$method = substr($name, 0, 3);//tras os três primeiros caracteres a partir da posição zero
		$fieldName = substr($name, 3, strlen($name));//tras os caracteres da posição 3 até a posição final da string [strlen conta a quantidade de caracteres da string]

		var_dump($method, $fieldName);
		exit;
	}

	public function setData($data = array())
	{
		foreach ($data as $key => $value) {
			
			$this->{"set".$key}($value);
		}
	}

	public function getValues()
	{
		return $this->values;
	}
}


 ?>