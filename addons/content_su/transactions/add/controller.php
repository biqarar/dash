<?php
namespace addons\content_su\transactions\add;

class controller extends \addons\content_su\transactions\controller
{
	public function _route()
	{
		parent::_route();

		$this->get(false, "add")->ALL();

		$this->get(false, "add")->ALL("/transactions\/add\/transactions\=(\d+)/");

		$this->post('add')->ALL();
		$this->post('add')->ALL("/transactions\/add\/transactions\=(\d+)/");
	}
}
?>