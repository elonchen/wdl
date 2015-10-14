<?php
/**
 * 二手交易模块微站定义
 *
 * @author 晓锋
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Xiaofeng_marketModuleSite extends WeModuleSite {
	public $goods = 'xfmarket_goods';
	public function doMobileNav() {
		//这个操作被定义用来呈现 微站首页导航图标
		global $_W;
		$url = $this->createMobileUrl('list');
		header("Location:$url");exit();
	}
	public function doMobileAdd(){
		global $_W,$_GPC;
		$categorys = pdo_fetchall("SELECT * FROM".tablename('xfmarket_category')."WHERE weid='{$_W['weid']}' limit 4");
		$data = array(
			'weid'        => $_W['weid'],
			'openid'      => $_W['fans']['from_user'],
			'title'       => $_GPC['title'],
			'rolex'       => $_GPC['rolex'],
			'price'       => $_GPC['price'],
			'realname'    => $_GPC['realname'],
			'sex'         => $_GPC['sex'],
			'mobile'      => $_GPC['mobile'],
			'description' => $_GPC['description'],
			'createtime'  => TIMESTAMP,
			'pcate'       => $_GPC['pcate'],
			'status'      => 0,
			'images' => serialize($_GPC['picIds']),
		);
		
		if (!empty($_GPC['id'])) {
			$good = pdo_fetch("SELECT * FROM".tablename($this->goods)."WHERE id='{$_GPC['id']}'");
		}
		if ($_W['ispost']) {
			if (empty($_GPC['id'])) {
				pdo_insert($this->goods,$data);
				message('发布成功',$this->createMobileUrl('list'),'success');
			}else{
				pdo_update($this->goods,$data,array('id' => $_GPC['id']));
				message('更新成功',$this->createMobileUrl('list'),'success');
			}

		}
		

		include $this->template('add');
	}
	public function doMobileList(){
		global $_GPC,$_W;
		$pcate = intval($_GPC['pcate']);
		//分类显示
		$categorys = pdo_fetchall("SELECT * FROM".tablename('xfmarket_category')."WHERE weid='{$_W['weid']}' AND enabled='1' limit 4");

		$condition = '';
		if(!empty($_GPC['keyword'])){
			$keyword = "%{$_GPC['keyword']}%";
			$condition .= " AND title LIKE '{$keyword}'";
		}
		if (!empty($this->module['config']['status'])) {
			$condition .= " AND status='1' ";
		}
		if ($pcate) {
			$condition .= "AND pcate = '{$pcate}'";
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize  = 10;

		$list = pdo_fetchall("SELECT * FROM ".tablename($this->goods)." WHERE weid='{$_W['weid']}' $condition LIMIT ".($pindex - 1) * $psize.','.$psize);
		foreach ($list as $key => $value) {
			$images = unserialize($value['images']);
			if ($images) {
				$picid = implode(',', $images);
				$imgs = pdo_fetchall("SELECT * FROM".tablename('xfmarket_images')."WHERE id in({$picid})");
				$list[$key]['img'] = $imgs;
			}

		}
		//print_r($list);
		$total = pdo_fetchcolumn("SELECT * FROM ".tablename($this->goods)."WHERE weid='{$_W['weid']}' $condition");
		$pager  = pagination($total, $pindex, $psize);
		//print_r($list);
		

		include $this->template('list');
	}
	public function doMobileDetail(){
		global $_W,$_GPC;
		$id = intval($_GPC['id']);
		$detail = pdo_fetch("SELECT * FROM".tablename($this->goods)."WHERE id='{$id}'");
		//print_r($detail);
		$title = $detail['title'];
		$images = unserialize($detail['images']);
		if ($images) {
			$picid = implode(',', $images);
			$imgs = pdo_fetchall("SELECT * FROM".tablename('xfmarket_images')."WHERE id in({$picid})");
		}
		include $this->template('detail');
	}
	public function doMobileMygoods(){
		global $_W,$_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize  = 10;
		$list = pdo_fetchall("SELECT * FROM".tablename($this->goods)."WHERE openid='{$_W['fans']['from_user']}' LIMIT ".($pindex - 1) * $psize.','.$psize);
		foreach ($list as $key => $value) {
			$images = unserialize($value['images']);
			if ($images) {
				$picid = implode(',', $images);
				$imgs = pdo_fetchall("SELECT * FROM".tablename('xfmarket_images')."WHERE id in({$picid})");
				$list[$key]['img'] = $imgs;
			}

		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename($this->goods)."WHERE openid='{$_W['fans']['from_user']}'");
		$pager  = pagination($total, $pindex, $psize);
		if ($_GPC['op'] == 'delete') {
			pdo_delete($this->goods,array('id' => $_GPC['id']));
			message('删除成功',referer(),'success');
		}
		include $this->template('mygoods');
	}
	public function doWebGoods(){
		global $_GPC,$_W;
		$goods = pdo_fetchall("SELECT * FROM".tablename($this->goods)."WHERE weid='{$_W['weid']}'");
		if ($_GPC['foo'] == 'delete') {
			pdo_delete($this->goods,array('id' => $_GPC['id']));
			message('删除成功',referer(),'success');
		}
		if ($_GPC['foo'] == 'update') {
			//echo $_GPC['id'].$_GPC['status'];exit;
			pdo_query("UPDATE ".tablename('xfmarket_goods')." SET status='{$_GPC['status']}' WHERE id='{$_GPC['id']}'");
			message('更新成功',referer(),'success');
		}
		include $this->template('goods');
	}
	//分类
	public function doWebCategory(){
		global $_GPC,$_W;
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		$id = intval($_GPC['id']);
		if ($op == 'post') {
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM".tablename('xfmarket_category')."WHERE id='{$id}'");
			}
			if ($_W['ispost']) {
				$data = array(
					'weid'    => $_W['weid'],
					'name'    => $_GPC['cname'],
					'enabled' => $_GPC['enabled'],
					);
				if (empty($id)) {
					pdo_insert('xfmarket_category',$data);
				}else{
					//print_r($data);exit;
					pdo_update('xfmarket_category',$data,array('id' => $id));
				}
				message('更新成功',referer(),'success');
			}
		}elseif($op == 'display'){
			$row = pdo_fetchall("SELECT * FROM".tablename('xfmarket_category')."WHERE weid='{$_W['weid']}'");
		}
		if(checksubmit('delete')){
			pdo_delete('xfmarket_category', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功',referer(),'success');
		}
		include $this->template('category');
	}
	//处理图片上传;
	 public function doMobileimgupload(){
			global $_W,$_GPC;
				
			if(!empty($_GPC['pic'])){
				preg_match("/data\:image\/([a-z]{1,5})\;base64\,(.*)/",$_GPC['pic'],$r);
				$imgname = 'bl'.time().rand(10000,99999).'.'.$r[1];
				$path = IA_ROOT.'/'.$_W['config']['upload']['attachdir'].'/images/';
				$f =fopen($path.$imgname,'w+');
				fwrite($f,base64_decode($r[2]));
				fclose($f);
				$imgurl = $_W['attachurl'].'/images/'.$imgname;
				$is = pdo_insert('xfmarket_images',array('src'=>$imgurl));
				$id = pdo_insertid();
				if(empty($is)){
				 exit(json_encode(array(
					  'errCode'=>1,
					  'message'=>'上传出现错误',
					  'data'=>array('id'=>$_GPC['t'],'picId'=>$id)
				  )));
				}else{
				  exit(json_encode(array(
					  'errCode'=>0,
					  'message'=>'上传成功',
					  'data'=>array('id'=>$_GPC['id'],'picId'=>$id)
				  )));
				}
			}
			
		} 
}