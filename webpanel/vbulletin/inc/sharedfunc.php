<?php
if(!function_exists('steam2friend')){
	function steam2friend($steam_id){
		$steam_id=strtolower($steam_id);
		if(substr($steam_id,0,7)=='steam_0'){
			$tmp=explode(':',$steam_id);
			if((count($tmp)==3) && is_numeric($tmp[1]) && is_numeric($tmp[2])){
				return bcadd((($tmp[2]*2)+$tmp[1]),'76561197960265728');
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}