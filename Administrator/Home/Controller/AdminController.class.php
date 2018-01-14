<?php
namespace Home\Controller;
use Think\Controller;


class AdminController extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getEnterPath()
	{
		return "admin.php";
	}
}