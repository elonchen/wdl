﻿<?php


defined('IN_IA') or exit('Access Denied');

class bb_askModule extends WeModule {

  public function settingsDisplay($settings) {
		global $_GPC, $_W;
		if(checksubmit()) {
			$data = $_GPC['data'];
			$this->saveSettings($data);
			message('设置成功！', referer(), 'success'); 
		}
		
		include $this->template('setting');
	}


}
 