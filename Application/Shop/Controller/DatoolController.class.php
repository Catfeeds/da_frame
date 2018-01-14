<?php
namespace Shop\Controller;
use Think\Controller;
use Common\Lib\UploadFile;

class DatoolController extends Controller
{
	/**
	 * 文件上传
	 * */
	public function image_upload()
	{
		$ext_dir = $this->getParam("ext_dir");
		
		$image_base_dir = "/ext";
		
		$full_dir = $image_base_dir;

		if (!empty($ext_dir))
		{
			$ext_dir_arr = explode(",", $ext_dir);
			foreach ($ext_dir_arr as $item)
			{
				$full_dir = $full_dir . DS . $item;
			}
		}
		
		$realPath = BASE_UPLOAD_PATH . $full_dir;
		if (!is_dir($realPath))
		{
			mk_dir($realPath);
		}
		
		$upload = new UploadFile();
		//$upload->setIfremove(true);
		$upload->set('default_dir', $full_dir);
		$result = $upload->upfile('file_data');

		$ret = array("errno" => 0);
		if ($result){
			
			$ret['data'] = $full_dir . DS . $upload->file_name;
			$ret['data'] = str_replace("\\", "/", $ret['data']);
			$ret['data'] = str_replace("//", "/", $ret['data']);
			$ret['data'] = UPLOAD_SITE_URL . $ret['data'];
			
		} else {
			$ret['errno'] = -1;
		}
		//{"file_id":181,"id":"file_0","file_name":"05636582734192366.jpg"}
		
		echo json_encode($ret);
	}
	
}