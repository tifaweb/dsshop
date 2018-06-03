<?php
// +----------------------------------------------------------------------
// | dswjcms
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.tifaweb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
// +----------------------------------------------------------------------
// | Author: 宁波市鄞州区天发网络科技有限公司 <dianshiweijin@126.com>
// +----------------------------------------------------------------------
// | Released under the GNU General Public License
// +----------------------------------------------------------------------
class AuctionModel extends RelationModel{
	protected $_validate = array(
		array('category','require','分类必须！'),
		array('title','require','标题必须！'),
		array('i_img','require','主图必须！'),
		array('money','require','起拍金额必须！'),
		array('assessment','require','评估价必须！'),
		array('premium','number','加价幅度必须！'),
		array('checktime','require','开始时间必须！'),
		array('endtime','require','结束时间必须！'),
	);
	protected $_link=array(
		'user'=> array(  
			'mapping_type'=>BELONGS_TO,
			'class_name'=>'user',
            'foreign_key'=>'uid',
            'mapping_name'=>'user',
			'mapping_fields'=>'username',
			'as_fields'=>'username:username',
		),
	);
}
?>