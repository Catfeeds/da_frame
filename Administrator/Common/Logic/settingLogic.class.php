<?php
namespace Common\Logic;
class settingLogic
{
	static $MOBILE_CONF_FIELDS = array("site_name", "site_phone", "site_status");
	public function getPublicSiteSetting()
	{
		$model_setting = Model('setting');
		$list_setting = $model_setting->getListSetting();
		foreach ($list_setting as $key => $val)
		{
			if (in_array($key, self::$MOBILE_CONF_FIELDS))
			{
				$ret[$key] = $val;
			}
		}
		return $ret;
	}
}