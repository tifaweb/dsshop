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
class IndentModel extends RelationModel{
	protected $_link=array(
		'erector'=> array(  
			'mapping_type'=>BELONGS_TO,
			'class_name'=>'erector',
            'foreign_key'=>'eid',
            'mapping_name'=>'erector'
		),
	);
}
?>