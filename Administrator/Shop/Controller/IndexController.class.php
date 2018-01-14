<?php
namespace Shop\Controller;

use Think\Controller;

class IndexController extends Controller
{
	public function __construct()
	{
		header("Location:" . U("Home/Index/index"));
		exit;
	}	
}