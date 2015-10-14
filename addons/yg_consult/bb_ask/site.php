<?php


defined('IN_IA') or exit('Access Denied');
class bb_askModuleSite extends WeModuleSite {
	
	

	private function getMobileTitle($mobilemethod){
		$titles = array(
			'bb_ask'=>'我的提问',
						'register'=>'信息登记'
		);
		return $titles[strtolower($mobilemethod)];

	}

	public function doMobileRegister(){
				
	global $_GPC, $_W;
	$title = $this->getMobileTitle('register');
	$fields =array('酒精肝','脂肪肝','肝炎','糖尿病','心脑血管疾病','其他');
	$selects = $_GPC['select'];
	if (checksubmit('submit')) {
			$data = array(
				'weid' => $_W['weid'],
				'from_user'=>$_W['fans']['from_user'],
				'zhusu' => $_GPC['zhusu'],
				'age' => intval($_GPC['age']),
				'bingzhong' => 	implode(',', $selects)
			);
			
						$datafans = array(
				'realname' => $_GPC['realname'],
							'address' => $_GPC['address']
			);

				if (empty($datafans['realname'])) {
					message('请填写您的真实姓名！', $this->createMobileUrl('Register'), 'error');
				}
             if(empty($data['zhusu'])) {
                message('请输入病情','','error');
            }
				 if(empty($datafans['address'])) {
                message('请输入地址','','error');
            }
         				 if($data['age']<=0) {
                message('请输入年龄','','error');
            } 	
         			 	

		$userinfo = pdo_fetch("select * from ".tablename('bb_ask_member')."  WHERE weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}' ");
         	 if(empty($userinfo)) {
					
							 pdo_insert('bb_ask_member', $data);
							 
							    
					
					}else
					{
						        
						            	 pdo_update('bb_ask_member', $data, array('weid' => $_W['weid'], 'from_user' => $_W['fans']['from_user']));
						           
				
				
						
						}
						
						 	 pdo_update('fans', $datafans, array('weid' => $_W['weid'], 'from_user' => $_W['fans']['from_user']));
						           
						   		message('成功登记用户信息！', $this->createMobileUrl('bb_ask'), 'success');
						        }
         
        $userinfo = pdo_fetch("select * from ".tablename('bb_ask_member')."  WHERE weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}' ");
         
		$select = pdo_fetchcolumn("select bingzhong from ".tablename('bb_ask_member')."  WHERE weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}' ");
        $select =explode(",",$select);
		$fans = pdo_fetch("select realname,mobile,address from ".tablename('fans')."  WHERE weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}' ");
		include $this->template('userinfo');
			}

	private function checkAuth() {
		global $_W, $_GPC;
	
		$member = $this->getMember();
		
		if (empty($member)) {
			$this->do = 'register';
			$this->doMobileRegister();
			exit;
		}
			$fans = pdo_fetch("select * from ".tablename('fans')."  WHERE weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}' ");
        
		return $fans;
	}
				private function getMember($openid = '') {
		global  $_W, $_GPC;
		$paras = array();
		$paras[':weid'] = $_W['weid'];
		if (!empty($openid)){
			$paras[':from_user'] = $openid;
		}else {
			$paras[':from_user'] = $_W['fans']['from_user'];
		}
		$member = pdo_fetch("SELECT * FROM ".tablename('bb_ask_member')." WHERE `weid` = :weid AND `from_user` = :from_user", $paras);
		return $member;
	}
			
			
				public function doMobilebb_ask()
			{
			

				global $_GPC, $_W;
		$title = $this->getMobileTitle('bb_ask');				
		$fans = $this->checkAuth();
	
		
						$paras = array(
							':weid'=> $_W['weid'],
							':from_user' => $_W['fans']['from_user']
						);
		
						$list = pdo_fetchall("SELECT * FROM ".tablename('bb_ask_qa')." WHERE `weid`=:weid AND `from_user`=:from_user  ORDER BY `id` DESC ", $paras);

		
    
	
				
				
		include $this->template('bb_ask');
			
			}
		public function doMobileDelbb_ask()
			{
									global $_GPC, $_W;
				
													$id = intval($_GPC['id']);
													   if($id>0){
              pdo_delete('bb_ask_qa', array('id' => $id, 'weid' => $_W['weid'], 'from_user' => $_W['fans']['from_user']));
              				message('删除成功.', $this->createMobileUrl('bb_ask'));
            }
				
		}
		
		
			
			
			
			private function doCheckCredit()
			{
					if(($this->module['config']['openwacredit'])==1)
					{
								$fans = $this->checkAuth();
							if(intval($this->module['config']['wacredit'])>intval($fans['credit1']))
							{
								
					
				     message('您的剩余积分是'.$fans['credit1'].'，提问所需积分是'.($this->module['config']['wacredit']).'，无法提问！','','error');
				     		}
					}
			}
	public function doMobilePostbb_ask()
			{
				
						global $_GPC, $_W;
							$title = $this->getMobileTitle('bb_ask');
		 	$fans =$this->checkAuth();
		 $this->doCheckCredit();
		 	if(($this->module['config']['openwacredit'])==1)
					{
							$openwacredit=1;
		 		$wacredit=intval($this->module['config']['wacredit']);
				  }
				        if(checksubmit('submit')){
          
							if(empty($_GPC['question'])) {
								message('未填写问题.');
							}
								
				$item = array( 
					'question' => trim($_GPC['question']), 
							'createtime' => TIMESTAMP
				);
				
				$id = intval($_GPC['id']);
				if(empty($id)) {
					$item['weid'] = $_W['weid'];
					$item['from_user'] = $_W['fans']['from_user'];
				pdo_insert('bb_ask_qa', $item);
				
		
				
							$datafans = array(
				'credit1' =>	($fans['credit1']-intval($this->module['config']['wacredit']))
			);
					 pdo_update('fans', $datafans, array('weid' => $_W['weid'], 'from_user' => $_W['fans']['from_user']));
						  
				
				
									message('提问成功.', $this->createMobileUrl('bb_ask'));
								} else {
								
									pdo_update('bb_ask_qa', $item, array('id'=>$id,'weid'=>$_W['weid'],'from_user'=>$_W['fans']['from_user']));
									message('修改成功.', $this->createMobileUrl('bb_ask'));
								}
            }

     	$id = intval($_GPC['id']);
				if (!empty($id)) {
					$item = pdo_fetch("SELECT * FROM ".tablename('bb_ask_qa')." where `id`=:id ", array(':id'=>$id));
				}
					include $this->template('postbb_ask');
				}
				
				
				
				
				
				
				

	
				
				
				
	
	   private function getWebTitle($mobilemethod){
		$titles =  array(
        'bb_ask' => '问答管理',
          'member' => '会员管理'
    );
		return $titles[strtolower($mobilemethod)];

	}



	
    public function doWebMember(){
    	    global $_W, $_GPC;
        checklogin();
    	  $action = 'member';
      $title = $this->getWebTitle($action);
			        
      $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $where = "WHERE  ".tablename('bb_ask_member').".weid = '{$_W['weid']}'";
        
  		if (!empty($_GPC['realname'])) {
				$where .= " AND ".tablename('fans').".realname LIKE '%{$_GPC['realname']}%'";
			} 
			$where2=$where;
			
			  $type = $_GPC["type"];
			  	if (!empty($type)) {
            $keyword = $_GPC["keyword"];
            switch($type){
                case 'realname':
                    $where .= " and ".tablename('fans').".realname like '%".$keyword."%' ";
                    break;
                 case 'mobile':
                    $where .= " and ".tablename('fans').".mobile like '%".$keyword."%' ";
                    break;
            }
}
        
        $members = pdo_fetchall("SELECT * FROM ".tablename('bb_ask_member')." left join  ".tablename('fans')." on  ".tablename('fans').".from_user=".tablename('fans').".from_user   {$where} order by id desc LIMIT ".($pindex - 1) * $psize.",{$psize}");
        if (!empty($members)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('bb_ask_member')." $where2");
            $pager = pagination($total, $pindex, $psize);
   		  }
    	
        include $this->template('member');   
			
    }

	

    public function doWebbb_ask(){
    	    global $_W, $_GPC;
        checklogin();
    	  $action = 'bb_ask';
      $title = $this->getWebTitle($action);
			        
      $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $where = "WHERE weid = '{$_W['weid']}'";
        
        			if (!empty($_GPC['answer'])) {
				$where .= " AND answer LIKE '%{$_GPC['answer']}%'";
			} 

			if (isset($_GPC['status'])) {
				$where .= " AND status = '".intval($_GPC['status'])."'";
			}else
			{
					$where .= " AND status = '0'";
				}
        
        
        $webbb_askqa = pdo_fetchall("SELECT * FROM ".tablename('bb_ask_qa')." {$where} order by id desc LIMIT ".($pindex - 1) * $psize.",{$psize}");
        if (!empty($webdepartments)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('bb_ask_qa')." $where");
            $pager = pagination($total, $pindex, $psize);
   		  }
    	
        include $this->template('webqa');   
			
    }




		      public function doWebbb_askDelete(){
        global $_GPC, $_W;
        checklogin();
        $action = 'bb_ask';
       $title = $this->getWebTitle($action);
        $url = create_url('site/module', array('do' => $action, 'name' => 'bb_ask'));
        $id = intval($_GPC['id']);
        if($id>0){
              pdo_delete('bb_ask_qa', array('id' => $id, 'weid' => $_W['weid']));
        }
        message('操作成功!', $url);
    }
    


    public function doWebbb_askForm(){
        global $_W, $_GPC;
        checklogin();
        $action = 'bb_ask';
      $title = $this->getWebTitle($action);
        $url = create_url('site/module', array('do' => $action, 'name' => 'bb_ask'));
        $id = intval($_GPC['id']);
       if(!empty( $id )) {
        $reply = pdo_fetch("select * from ".tablename('bb_ask_qa')." where id =".$id);
    }

     
        if(checksubmit('submit')){
            $data = array();
            $data['weid'] = intval($_W['weid']);
            $data['answer'] = trim($_GPC['answer']);
              $data['status']=1;
            if(istrlen($data['answer']) == 0){
                message('没有输入解答内容.','','error');
            }
   		     if(!empty($reply)){
                
	           
                	 pdo_update('bb_ask_qa', $data, array('id' => $id, 'weid' => $_W['weid']));
                  message('操作成功!', $url);
            }else
            {
            	message('数据不存在.','','error');
            	}
      
        }
        include $this->template('webqa_form');
    }
	
	
	
	
	

}
