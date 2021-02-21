<?php 

namespace Hcode;

use Rain\Tpl;

/**
 * __construct() e __destruct(). São os métodos executados assim que a classe da página é iniciada e finalizada. Um é responsável por renderizar o header da página e outro renderiza o footer
 */
class Page
{
	private $tpl;
	private $options = [];
	private $defaults = [
		"header"=>true,
		"footer"=>true,
		"data"=>[]
	];
	
	public function __construct($opts = array(), $tpl_dir = "/views/")// $opts é um array por padrão, caso nenhum parâmetro seja definido
	{
		$this->options = array_merge($this->defaults, $opts);//Em caso de conflito com dados iguais, o parâmetro posterior sobrescreve o anterior, afim de mesclar os dois arrays($defaults+$opts) em um só ($options).

		// config [metodo que vem do slimframework]
		$config = array(
						"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
						"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
						"debug"         => false // set to false to improve the speed
					);

		Tpl::configure( $config );

		$this->tpl = new Tpl;

		$this->setData($this->options["data"]);
		//Se a opção header for verdadeira o header carrega
		if ($this->options["header"] === true) $this->tpl->draw("header");//Chama o arquivo que contém o desenho do header, contido na pasta views [irá carregar ao instanciar a function]
	}

	private function setData($data = array())// 
	{
		foreach ($data as $key => $value) { 
			$this->tpl->assign($key, $value);	
		}			
	}

	public function setTpl($name, $data = array(), $returnHTML = false)
	{
		$this->setData($data);//seta os dados que serão carregados pelo metodo setData [line:40]

	    return $this->tpl->draw($name, $returnHTML); // chama o template inserido pelo método, quando a rota é acionada.
	}

	public function __destruct()//Quando a função construct terminar, essa função e chamada
	{
		//Se a opção footer for verdadeira o header carrega
		if ($this->options["footer"] === true) $this->tpl->draw("footer");//Chama o arquivo que contém o desenho do footer, contido na pasta views [irá carregar após a funtion setTpl for finalizada]
	}
}

 ?>