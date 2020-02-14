<?php
ini_set("allow_url_fopen", true);
header('Content-Type: application/json');
include('../../../config/return_function.php');
include('../../../config/conn.php');
include('../../../model/CHANNEL.php');
$channel = new CHANNEL($conn);
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
	case 'POST':
        $request_data = $_POST;
        if(!isset($request_data['ops_type'])){
            return_fail('ops_type has to be provided in request');
        }
	$ops_type = (string) $request_data['ops_type'];
	//$jwt = $request_data['jwt'];
        switch ($ops_type){
            case 'register':
                $channel->register($request_data);
                break;
            case 'select_all':
                $channel->select_all();
                break;
            case 'update':
                $channel->update($request_data);
                break;
            case 'delete':
                $channel->delete($request_data);
                break;
            default :
                return_fail('unknow_ops_type',$ops_type);
                break;
        }
	default:
		return_fail("unknow_method",$method);
		break;
}
?>