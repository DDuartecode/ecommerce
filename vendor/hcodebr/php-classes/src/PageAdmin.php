<?php 

namespace Hcode;
use Rain\Tpl;

class PageAdmin extends Page //Herda todos os metodos da classe Page
{
	public function __construct($opts = array(), $tpl_dir = "/views/admin/")
	{
		parent::__construct($opts, $tpl_dir);//Invoca o metodo consturct da classe Page, passando os parametros do Page Admin, com isso executando a mesma tarefa de carregar o header, conteúdo e footer, porém com o layout do admin.
	}
}

 ?>