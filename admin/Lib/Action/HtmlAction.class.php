<?php
	class htmlAction extends CommonAction{
		public function index(){
			$this->display('html:index');
		}

		public function add(){
			$this->display('html:add');
		}
	}

?>