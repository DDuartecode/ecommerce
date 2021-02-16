<?php 

namespace Hcode;

use Rain\Tpl;

/**
 * 
 */
class Page
{
	private $tpl;
	private $options = [];
	private $defaults = [
		"data"=>[]
	];
	
	public function __construct($opts = array())// $opts é um array por padrão, caso nenhum parâmetro seja definido
	{
		$this->options = array_merge($this->defaults, $opts);//Em caso de conflito com dados iguais, o parâmetro posterior sobrescreve o anterior, afim de mesclar os dois arrays($defaults+$opts) em um só ($options).

		// config
		$config = array(
						"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/",
						"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache",
						"debug"         => false // set to false to improve the speed
					);

		Tpl::configure( $config );

		$this->tpl = new Tpl;

		$this->setData($this->options["data"]);

		$this->tpl->draw("header");//Chama o arquivo que contém o desenho do header, contido na pasta views [irá carregar ao instanciar a function]
	}

	private function setData($data = array())
	{
		foreach ($data as $key => $value) {
			$this->tpl->assign($key, $value);	
		}			
	}

	public function setTpl($name, $data = array(), $returnHTML = false)
	{
		$this->setData($data);//Irá carregar o arquivo no momento que o médodo for invocado.

	    return $this->tpl->draw($name, $returnHTML);
	}

	public function __destruct()//Quando a função construct terminar, essa função e chamada
	{
		$this->tpl->draw("footer");//Chama o arquivo que contém o desenho do footer, contido na pasta views [irá carregar após a funtion setTpl for finalizada]
	}
}

 ?>