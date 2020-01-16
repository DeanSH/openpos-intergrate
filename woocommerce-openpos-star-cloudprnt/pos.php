<?php
$base_dir = dirname(dirname(dirname(__DIR__)));
require_once ($base_dir.'/wp-load.php');
$result = array('message' => '','status' => 0);
$receipt_html = isset($_REQUEST['html_str']) ? $_REQUEST['html_str'] : '';
$cashdrawer_id = isset($_REQUEST['cashdrawer_id']) ? $_REQUEST['cashdrawer_id'] : 0;
$copy = isset($_REQUEST['copy']) ? $_REQUEST['copy'] : 1;
global $_op_printer;
$setting = $_op_printer->get_setting();
$mac = $setting['default_printer'];
$kitchen_mac = $setting['default_printer'];
$use_format = 'html';

if(isset($setting['active']) && $setting['active'] )
{
   
    if($cashdrawer_id)
    {
        $register_mac = get_post_meta($cashdrawer_id,'_op_register_star_cloudprnt',true);
        $register_mac_kitchen = get_post_meta($cashdrawer_id,'_op_register_star_cloudprnt_kitchen',true);
        if($register_mac)
        {
            $mac  = $register_mac;
        }
        $kitchen_mac = $mac;
        if($register_mac_kitchen)
        {
            $kitchen_mac  = $register_mac_kitchen;
        }
    }
    
   
    if($_FILES && $kitchen_mac ){
        foreach($_FILES as $file_key => $file)
        {
            $file_name = isset($file["name"]) ? $file["name"] : '';
            
            if($file_name && strpos($file_name,'kitchen') == 0)
            {
                $mac = $kitchen_mac;
            }
            if($file_key == 'file_html')
            {
                if($use_format == 'html')
                {
                    $target_file = rtrim(OPENPOS_CLOUDPRNT_DIR,'/').'/files/'.time().'.html';//.$file["name"];
                    $image_base64 = file_get_contents($file["tmp_name"]);

                    if (file_put_contents($target_file,$image_base64)) {
                        star_cloudprnt_queue_add_print_job($mac,$target_file,$copy);

                        $result['message'] =  "The file ". basename( $file["name"]). " has been uploaded.";
                        $result['status'] = 1;
                    } else {
                        $result['message'] = "Sorry, there was an error uploading your file.";
                    }
                    // if you want use print html receipt
                }

            }else{
                if($use_format == 'image')
                {
                    $type = $file['type'];
                    if($type == 'image/png')
                    {
                        $target_file = rtrim(OPENPOS_CLOUDPRNT_DIR,'/').'/files/'.time().'.png';//.$file["name"];
                        $image_base64 = file_get_contents($file["tmp_name"]);

                        if (file_put_contents($target_file,$image_base64)) {
                            star_cloudprnt_queue_add_print_job($mac,$target_file,$copy);

                            $result['message'] =  "The file ". basename( $file["name"]). " has been uploaded.";
                            $result['status'] = 1;
                        } else {
                            $result['message'] = "Sorry, there was an error uploading your file.";
                        }
                    }
                }


            }


        }
    }
}

echo json_encode($result);exit;