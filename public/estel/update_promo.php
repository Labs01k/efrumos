<?php
define("DOC_ROOT", "/home/estel/sites/estel.md/public_html/public");
include(DOC_ROOT."/estel/common.inc");
include(DOC_ROOT."/estel/kbmysqli.php");
include(DOC_ROOT."/estel/db.inc");
include(DOC_ROOT."/estel/lang.php");
include(DOC_ROOT."/estel/common.php");
include(DOC_ROOT."/estel/photo_resize.php");

$die = 0;//останавливаем или идем дальше

$query = "SELECT goods_promo.*, goods_promo.id AS goods_promo_id, DATE_FORMAT(data_end, '%Y-%m-%e') AS data_end_format
	FROM goods_promo
	WHERE promo_type='1'
	ORDER BY data_end ASC";
$promo_list = $mysqli->getlist($query);
if (!empty($promo_list)) {
	foreach ($promo_list as $one_promo) {
		$data_start = strtotime($one_promo['data_start']);
		$data_end = strtotime($one_promo['data_end']);
		$data_curr = time();
		$price_promo = $one_promo['discount_summa'] > 0?'(price-'.$one_promo['discount_summa'].')':'(price*(100-'.$one_promo['discount_procent'].')/100)';
		if($data_end < $data_curr){//старые акции
			$query = "UPDATE goods_item_id
			SET price_promo=0, updated_at=NOW()
			WHERE id IN(SELECT goods_item_id FROM goods_promo_items WHERE goods_promo_id={$one_promo['goods_promo_id']}) AND manual_promo=0";
		}elseif($data_curr >= $data_start){//акции, только когда текущая дата больше даты начала
			$query = "UPDATE goods_item_id
			SET price_promo=$price_promo, updated_at=NOW()
			WHERE id IN(SELECT goods_item_id FROM goods_promo_items WHERE goods_promo_id={$one_promo['goods_promo_id']})";
		}
		$mysqli->query($query);

	}
}

?>