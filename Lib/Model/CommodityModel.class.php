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
class GoodsModel extends RelationModel{
	protected $_validate = array(
		array('title','require','标题有误！'),
		array('number','require','编号有误！'),
		array('zimg','require','主图有误！'),
		array('fid','number','类目有误！'),
		array('sort','number','排序有误！'),
		array('details','require','内容有误！'),
		array('specifications','require','内容有误！'),
		array('img','require','细节图有误！'),
		
	);
}
?>