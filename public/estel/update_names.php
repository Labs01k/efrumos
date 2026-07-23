<?php
define("DOC_ROOT", stripos($_SERVER['DOCUMENT_ROOT'], 'estelmd') !== false?$_SERVER['DOCUMENT_ROOT'].'/public':$_SERVER['DOCUMENT_ROOT']);
include(DOC_ROOT."/estel/common.inc");
include(DOC_ROOT."/estel/kbmysqli.php");
include(DOC_ROOT."/estel/db.inc");
include(DOC_ROOT."/estel/lang.php");
include(DOC_ROOT."/estel/common.php");
include(DOC_ROOT."/estel/photo_resize.php");

function ShowBrandName($brand_id, $lang){
	global $mysqli;
	$brand_id = intval($brand_id);
	$query = "SELECT name_{$lang}
	FROM goods_brand
	WHERE id='{$brand_id}'
	LIMIT 1";
	$row = $mysqli->getone($query);
	return $row['name_'.$lang];
}

$langs_list = array(2,3);
$langnames_list = array('ro','ru');

$query = "SELECT goods_item_id.*
FROM goods_item_id";
$items_list = $mysqli->getlist($query);
if(!empty($items_list)){
	foreach ($items_list as $one_item) {
		$subject_nav = GetNav($one_item['goods_subject_id'], 'goods_subject_id');
		if (!empty($langs_list)) {
			$param = array();
			foreach ($langs_list as $lang_key=>$one_lang) {
				$param['subject_name'] = ShowName('goods_subject', $one_item['goods_subject_id'], $one_lang);
				$param['subject_nav_name'] = '';
				$param['brand_nav_name'] = '';
				if(!empty($subject_nav)){
					krsort($subject_nav);
					$param['goods_subject_pid'] = $subject_nav[count($subject_nav) - 1]['id'];
					foreach ($subject_nav as $one_snav) {
						if($one_snav['p_id'] != 0) $param['subject_nav_name'] .= '/'.ShowName('goods_subject', $one_snav['id'], $one_lang);;
					}
					$param['subject_nav_name'] = mb_substr($param['subject_nav_name'], 1);
				}
				$brand_nav = GetNav($one_item['brand_id'], 'goods_brand');
				if(!empty($brand_nav)){
					krsort($brand_nav);
					foreach ($brand_nav as $one_bnav) {
						$param['brand_nav_name'] .= '/'.ShowBrandName($one_bnav['id'], $langnames_list[$lang_key]);
					}
					$param['brand_nav_name'] = mb_substr($param['brand_nav_name'], 1);
				}
				$param = $mysqli->smart_escape($param);
				$query = "UPDATE goods_item_id
				SET goods_subject_pid = '{$param['goods_subject_pid']}'
				WHERE id='{$one_item['id']}'
				LIMIT 1";
				$mysqli->query($query);
				$query = "UPDATE goods_item
				SET subject_name = '{$param['subject_name']}', subject_nav_name='{$param['subject_nav_name']}', brand_nav_name='{$param['brand_nav_name']}'
				WHERE goods_item_id='{$one_item['id']}' AND lang_id='{$one_lang}'
				LIMIT 1";
				$mysqli->query($query);
			}
		}
	}
}

?>