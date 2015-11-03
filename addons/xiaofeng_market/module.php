<?php
/**
 * 二手交易模块定义
 *
 * @author 晓锋
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Xiaofeng_marketModule extends WeModule {

	public function settingsDisplay($settings) {

			global $_GPC, $_W;

			if(checksubmit()) {

				$cfg = array(

					'tel' => $_GPC['tel'],
					'status' => $_GPC['status'],
				);

				$this->saveSettings($cfg);
				message('保存成功', 'refresh');
			}

			include $this->template('setting');

		}
}