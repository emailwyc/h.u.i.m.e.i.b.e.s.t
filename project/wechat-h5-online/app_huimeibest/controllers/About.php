<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * About.php
 * 关于我们
 */

class About extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}

    /** 
     * 关于我们
     */
	public function index() {
		$this->load->view('about/index');
	}

    /** 
     * 常见问题
     */
	public function question() {
		$this->load->view('about/question');
	}

    /** 
     * 客服热线 
     */
	public function csphone()
	{
		$this->load->view('about/csphone');
	}

    /** 
     * 海外医疗
     */
	public function overseas()
	{
		$this->load->view('about/overseas');
	}

    /** 
     * 就医咨询
     */
	public function seedoc()
	{
		$this->Login_model->weixinLoginCheck("about/seedoc");
		$this->load->view('about/seedoc');
	}

    /** 
     * 企业合作
     */
	public function join()
	{
		$this->load->view('about/join');
	}

    /** 
     * 协议
     */
	public function agreement()
	{
		$this->load->view('about/agreement');
	}

}
