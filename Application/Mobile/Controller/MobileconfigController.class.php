<?php
namespace Mobile\Controller;
use Think\Controller;
class MobileconfigController extends Controller
{
	public function index()
	{
		$settingLogic = Logic("setting");
		$ret = $settingLogic->getPublicSiteSetting();
		output_data($ret);
	}
}