<?php 

namespace Hcode;

class Model {

	private $values = [];

	public function __call($name, $args) //executa sempre que um metodo for chamado
	{
		$method = substr($name, 0, 3);//tras os três primeiros caracteres a partir da posição zero
		$fieldName = substr($name, 3, strlen($name));//tras os caracteres da posição 3 até a posição final da string [strlen conta a quantidade de caracteres da string]

		switch ($method) {
			case 'get':
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
				break;

			case 'set':
				$this->values[$fieldName] = $args[0];
				break;
		}

	}

	public function setData($data = array())//cria set's dinamicamente de acordo com a quantidade de dados.
	{
		foreach ($data as $key => $value){//realiza os sets, de acordo com a quantidade de dados no array.
			
			$this->{"set".$key}($value);// cria os métodos set's Ex: banco retorna id = 1, função cria o método setid(1);
		}
	}

	public function getValues()
	{
		return $this->values;
	}
}


 ?>