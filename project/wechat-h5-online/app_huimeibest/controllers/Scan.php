<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 扫一扫相关
 */

class Scan extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}

	public function follow() {
		$this->load->view('scan/follow');
	}


}
