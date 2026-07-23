<?php
define("DOC_ROOT", $_SERVER['DOCUMENT_ROOT'].'/public');
include(DOC_ROOT."/estel/common.inc");
include(DOC_ROOT."/estel/kbmysqli.php");
include(DOC_ROOT."/estel/db.inc");
include(DOC_ROOT."/estel/lang.php");
include(DOC_ROOT."/estel/common.php");
include(DOC_ROOT."/estel/photo_resize.php");

function GetProductFoto($item_id){
	global $mysqli;
	$item_id = intval($item_id);
	$query = "SELECT goods_foto.*
	FROM goods_foto
	WHERE goods_item_id='{$item_id}'
	ORDER BY position ASC";
	return $mysqli->getlist($query);
}

function GetSubjectAliasByID($subject_id){
	global $mysqli;
	$item_id = intval($goods_item_id);
	$query = "SELECT alias
	FROM goods_subject_id
	WHERE id='{$subject_id}'
	LIMIT 1";
	$row = $mysqli->getone($query);
	return $row['alias'];
}

function GetItem($item_id, $lang_id){
	global $mysqli;
	$item_id = intval($item_id);
	$lang_id = intval($lang_id);
	$query = "SELECT goods_item.name, goods_item.body, goods_item.subject_nav_name, goods_item.brand_nav_name
	FROM goods_item
	WHERE goods_item_id='{$item_id}' AND lang_id='{$lang_id}'
	LIMIT 1";
	return $mysqli->getone($query);
}

$lang_id_ro = 2;
$lang_id_ru =3;

$site_link = 'https://www.estel.md';

$query = "SELECT goods_item_id.*, goods_item_id.id AS goods_item_id
FROM goods_item_id
WHERE active=1 AND deleted=0
ORDER BY position ASC";
$items_list = $mysqli->getlist($query);
$img_names[] = 'Link Image produs - principala';
for($i = 1; $i <= 5; $i++){
	$img_names[] = 'Link image produs_secundar_'.$i;
}
if(!empty($items_list)){
	$csv = array();
	header('Content-Type: application/csv; charset=UTF-8');
	header('Content-Disposition: attachment; filename="estel_'.date('d.m.Y H:i:s').'.csv";');
	$out = fopen('php://output', 'w');
	foreach ($items_list as $key=>$one_item) {
		$item_ro = GetItem($one_item['goods_item_id'], $lang_id_ro);
		$item_ru = GetItem($one_item['goods_item_id'], $lang_id_ru);
		$fotos_list = GetProductFoto($one_item['goods_item_id']);
		if(!empty($fotos_list)){
			$img_array = array();
			foreach($fotos_list as $k => $one_foto){
				if($key == 0)
					$img_array['Link Image produs - principala'] = $site_link.'/upfiles/gallery/'.$one_foto['img'];
				else
					$img_array['Link image produs_secundar_'.$k] = $site_link.'/upfiles/gallery/'.$one_foto['img'];
			}
		}
		$brand_array = explode('/', $item_ro['brand_nav_name']);
		$subject_array = explode('/', $item_ro['subject_nav_name']);
		$item_array = array('Cod 1C'=>$one_item['one_c_code'], 'Articol_produs'=>$one_item['articol'], 'Titlu_produs ro'=>$item_ro['name'], 'Descriere_produs ro'=>$str = str_replace(array("\r","\n"),"",$item_ro['body']), 'Titlu_produs ru'=>$item_ru['name'], 'Descriere_produs ru'=>$str = str_replace(array("\r","\n"),"",$item_ru['body']), 'Brand_produs'=>$brand_array[0], 'Sub_brand_produs'=>$brand_array[1], 'Politica_vanzare_b2b_sau_b2c'=>$one_item['b2b_type'], 'Tip_produs'=>ShowName('goods_type', $one_item['goods_type_id'], LANGID), 'Categorie 1'=>$subject_array[0], 'Categorie 2'=>$subject_array[1], 'Categorie 3'=>$subject_array[2], 'Pret_normal_B2C'=>$one_item['price'], 'Pret_la_oferta_B2C'=>$one_item['price_promo'], 'Stoc'=>$one_item['products_count'], 'Link_product_page'=>$site_link.'/ro/item/'.$one_item['alias'], 'Barcode'=>$one_item['barcode'], 'Gramaj_in_ml'=>$one_item['gramaj']);
		if($key == 0) fputcsv($out, array_merge(array_keys($item_array), $img_names));
		$csv[$key] = array_merge($item_array, $img_array);
		fputcsv($out, $csv[$key]);
	}
}
// pre($csv);
// fclose($out);