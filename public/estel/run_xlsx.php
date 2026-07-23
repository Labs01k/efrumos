<?php
define("DOC_ROOT", stripos($_SERVER['DOCUMENT_ROOT'], 'estelmd') !== false?$_SERVER['DOCUMENT_ROOT'].'/public':$_SERVER['DOCUMENT_ROOT']);
include(DOC_ROOT."/estel/common.inc");
include(DOC_ROOT."/estel/kbmysqli.php");
include(DOC_ROOT."/estel/db.inc");
include(DOC_ROOT."/estel/lang.php");
include(DOC_ROOT."/estel/common.php");
include(DOC_ROOT."/estel/photo_resize.php");

$die = 0;//останавливаем или идем дальше

function GetYoutubeIdByLink($code){
	if ($youtube_pos = strpos($code, "youtube.com/v/")){
  	$youtube_id=substr($code, $youtube_pos + 14, 11);
  	return $youtube_id;
  }elseif ($youtube_pos = strpos($code, "youtube.com/watch?v=")){
  	$youtube_id=substr($code, $youtube_pos + 20, 11);
  	return $youtube_id;
  }elseif ($youtube_pos = strpos($code, "youtube.com/embed/")){
    $youtube_id=substr($code, $youtube_pos + 18, 11);
  	return $youtube_id;
  }elseif ($youtube_pos = strpos($code, "youtube-nocookie.com/embed/")){
    $youtube_id=substr($code, $youtube_pos + 27, 11);
  	return $youtube_id;
  }elseif ($youtube_pos = strpos($code, "youtu.be/")){
    $youtube_id=substr($code, $youtube_pos + 9, 11);
  	return $youtube_id;
  }else {
  	return false;
  }
}

//13.05.2020 убрали AND active='1' AND deleted='0' в запросе
function GetSubjectByName($subject_name, $p_id){
  global $mysqli;
  $subject_name = $mysqli->smart_escape($subject_name);
  $p_id = intval($p_id);
  $query = "SELECT goods_subject_id
  FROM goods_subject
  LEFT JOIN goods_subject_id ON(goods_subject_id.id=goods_subject.goods_subject_id)
  WHERE name='{$subject_name}' AND p_id='{$p_id}'
  LIMIT 1";
  $row = $mysqli->getone($query);
  return $row['goods_subject_id'];
}

function GetSubjectByAlias($alias, $p_id){
  global $mysqli;
  $alias = $mysqli->smart_escape($alias);
  $p_id = intval($p_id);
  $query = "SELECT id
  FROM goods_subject_id
  WHERE alias='{$alias}' AND p_id='{$p_id}'
  LIMIT 1";;
  $row = $mysqli->getone($query);
  return $row['id'];
}

function GetSubjectAliasByID($id){
  global $mysqli;
  $id = intval($id);
  $query = "SELECT alias
  FROM goods_subject_id
  WHERE id='{$id}'
  LIMIT 1";
  $row = $mysqli->getone($query);
  return $row['alias'];
}

function GetTypeByName($type_name){
  global $mysqli;
  $type_name = $mysqli->smart_escape($type_name);
  $query = "SELECT goods_type_id
  FROM goods_type
  LEFT JOIN goods_type_id ON(goods_type_id.id=goods_type.goods_type_id)
  WHERE name='{$type_name}'
  LIMIT 1";
  $row = $mysqli->getone($query);
//  pre($query);
  return $row['goods_type_id'];
}

//13.05.2020 убрали AND active='1' в запросе
function GetBrandByName($brand_name, $p_id){
  global $mysqli;
  $brand_name = $mysqli->smart_escape($brand_name);
  $p_id = intval($p_id);
  $query = "SELECT id AS goods_brand_id
  FROM goods_brand
  WHERE name_ro='{$brand_name}' AND p_id='{$p_id}'
  LIMIT 1";
  $row = $mysqli->getone($query);
  return $row['goods_brand_id'];
}

function GetItemIDByArticol($articol){
  global $mysqli;
  $articol = $mysqli->smart_escape($articol);
  $query = "SELECT id
  FROM goods_item_id
  WHERE articol='{$articol}'
  LIMIT 1";
  $row = $mysqli->getone($query);
  return $row['id'];
}

function GetItemIDByAlias($alias){
  global $mysqli;
  $alias = $mysqli->smart_escape($alias);
  $query = "SELECT id
  FROM goods_item_id
  WHERE alias='{$alias}'
  LIMIT 1";
  $row = $mysqli->getone($query);
  return $row['id'];
}

function GetParametrByName($parametr_name, $subject_id){
  global $mysqli;
  $parametr_name = $mysqli->smart_escape($parametr_name);
  $subject_id = intval($subject_id);
  $query = "SELECT goods_parametr_id
  FROM goods_parametr
  LEFT JOIN goods_parametr_id ON(goods_parametr_id.id=goods_parametr.goods_parametr_id)
  WHERE name='{$parametr_name}' AND goods_subject_id='{$subject_id}'
  LIMIT 1";
  $row = $mysqli->getone($query);
  return $row['goods_parametr_id'];
}

function GetParametrValueByName($parametr_value, $parametr_id){
  global $mysqli;
  $parametr_value = $mysqli->smart_escape($parametr_value);
  $parametr_id = intval($parametr_id);
  $query = "SELECT goods_parametr_value_id
  FROM goods_parametr_value
  LEFT JOIN goods_parametr_value_id ON(goods_parametr_value_id.id=goods_parametr_value.goods_parametr_value_id)
  WHERE name='{$parametr_value}' AND goods_parametr_id='{$parametr_id}'
  LIMIT 1";
  $row = $mysqli->getone($query);
  return $row['goods_parametr_value_id'];
}

function AddSubject($param){
  	global $mysqli;
		$param = $mysqli->smart_escape($param);
		if ($param['alias'] != ""){
    	$alias = preg_replace("/[^A-Za-z0-9-_\s]+/", "", $param['alias']);
  		$alias = preg_replace("/\s/", "-", $alias);
  	}else {
			$alias = preg_replace("/[^A-Za-z0-9-_\s]+/", "", $param['name']);
  		$alias = preg_replace("/\s/", "-", $alias);
  	}
  	if (CheckIFAliasIsUnique('goods_subject_id', $alias)) $alias = $alias.'-'.GetSubjectAliasByID($param['p_id']);
		$level = GetLevel($param['p_id'], "goods_subject_id")+1;
		$position = GetMinPosition("goods_subject_id", " AND p_id='{$param['p_id']}'")-1;
		$query="INSERT INTO goods_subject_id
		SET p_id='{$param['p_id']}', alias='{$alias}', active='1', deleted='0', level='{$level}', position='{$position}', created_at=NOW(), updated_at=NOW()";
		$ins_id = $mysqli->insert_and_return_id($query);
		$query = "INSERT INTO goods_subject
		SET goods_subject_id='{$ins_id}', lang_id='{$param['lang_id']}', name='{$param['name']}', created_at=NOW(), updated_at=NOW()";
		$mysqli->query($query);
		$otherlangs = CLang::OtherlangList($param['lang_id']);
		if (is_array($otherlangs) && count($otherlangs) > 0){
			foreach ($otherlangs as $k=>$v){
				$query = "INSERT INTO goods_subject
				SET goods_subject_id='{$ins_id}', lang_id='{$v['lang_id']}', name='', created_at=NOW(), updated_at=NOW()";
				$mysqli->query($query);
			}
		}
		return $ins_id;
	}

function AddType($param){
  	global $mysqli;
		$param = $mysqli->smart_escape($param);
		$position = GetMinPosition("goods_type_id")-1;
		$query="INSERT INTO goods_type_id
		SET position='{$position}'";
		$ins_id = $mysqli->insert_and_return_id($query);
		$query = "INSERT INTO goods_type
		SET goods_type_id='{$ins_id}', lang_id='{$param['lang_id']}', name='{$param['name']}', created_at=NOW(), updated_at=NOW()";
		$mysqli->query($query);
		$otherlangs = CLang::OtherlangList($param['lang_id']);
		if (is_array($otherlangs) && count($otherlangs) > 0){
			foreach ($otherlangs as $k=>$v){
				$query = "INSERT INTO goods_type
				SET goods_type_id='{$ins_id}', lang_id='{$v['lang_id']}', name='', created_at=NOW(), updated_at=NOW()";
				$mysqli->query($query);
			}
		}
		return $ins_id;
	}

function AddBrand($param){
  	global $mysqli;
		$param = $mysqli->smart_escape($param);
		if ($param['alias'] != ""){
    	$alias = preg_replace("/[^A-Za-z0-9-_\s]+/", "", $param['alias']);
  		$alias = preg_replace("/\s/", "-", $alias);
  	}else {
			$alias = preg_replace("/[^A-Za-z0-9-_\s]+/", "", $param['name']);
  		$alias = preg_replace("/\s/", "-", $alias);
  	}
		$position = GetMinPosition("goods_brand", " AND p_id='{$param['p_id']}'")-1;
		$query="INSERT INTO goods_brand
		SET p_id='{$param['p_id']}', active='1', deleted='0', position='{$position}', name_ro='{$param['name']}', name_ru='{$param['name']}', created_at=NOW(), updated_at=NOW()";
		return $mysqli->insert_and_return_id($query);
	}

	function CheckIfExistsItemLang($item_id, $lang_id=null){
		global $mysqli;
		$item_id = intval($item_id);
		if (!is_null($lang_id)){
			$lang_id=" AND lang_id='".intval($lang_id)."'";
		}
		$query="SELECT id
		FROM goods_item
		WHERE goods_item_id='{$item_id}' $lang_id
		LIMIT 1";
		return $mysqli->query_and_return_true_false($query);
	}

	function AddItem($param){
		global $mysqli;
		$param = $mysqli->smart_escape($param);
		if ($param['alias'] != ""){
    	$alias = preg_replace("/[^A-Za-z0-9-_\s]+/", "", $param['alias']);
  		$alias = preg_replace("/\s/", "-", $alias);
  	}else {
			$alias = preg_replace("/[^A-Za-z0-9-_\s]+/", "", $param['name']);
  		$alias = preg_replace("/\s/", "-", $alias);
  	}
  	if (CheckIFAliasIsUnique('goods_item_id', $alias)) $alias = $alias.'-'.time();
		$position = GetMinPosition("goods_item_id", "AND goods_item_id.goods_subject_id='{$param['goods_subject_id']}'")-1;
		$query = "INSERT INTO goods_item_id
		SET goods_subject_id='{$param['goods_subject_id']}', brand_id='{$param['brand_id']}', goods_type_id='{$param['goods_type_id']}', articol='{$param['articol']}', one_c_code='{$param['one_c_code']}', barcode='{$param['barcode']}', alias='{$alias}', active='{$param['active']}', popular_element='{$param['popular_element']}', new_element='{$param['new_element']}', prof_element='{$param['prof_element']}', deleted='0', price='{$param['price']}', price_promo='{$param['price_promo']}', price_b2b='{$param['price_b2b']}', price_b2b_promo='{$param['price_b2b_promo']}', b2b_type='{$param['b2b_type']}', stock_type='{$param['stock_type']}', puncte_bonus='{$param['puncte_bonus']}', produse_compatibile='{$param['produse_compatibile']}', produse_similare='{$param['produse_similare']}', gramaj='{$param['gramaj']}', products_count='{$param['products_count']}', youtube_link='{$param['youtube_link']}', youtube_id='{$param['youtube_id']}', in_stoc='{$param['in_stoc']}', position='{$position}', created_at=NOW(), updated_at=NOW()";
		$ins_id = $mysqli->insert_and_return_id($query);
		$query = "INSERT INTO goods_item
		SET goods_item_id='{$ins_id}', lang_id='{$param['lang_id']}', name='{$param['name']}', short_descr='{$param['short_descr']}', body='{$param['body']}', destinatie_produs='{$param['destinatie_produs']}', tags='{$param['tags']}', img_alt='{$param['img_alt']}', country_from='{$param['country_from']}', measure_name='{$param['measure_name']}', page_title='{$param['page_title']}', h1_title='{$param['h1_title']}', meta_title='{$param['meta_title']}', meta_keywords='{$param['meta_keywords']}', meta_description='{$param['meta_description']}', created_at=NOW(), updated_at=NOW()";
		$mysqli->query($query);
		$query = "INSERT INTO goods_item
		SET goods_item_id='{$ins_id}', lang_id='{$param['other_lang_id']}', name='{$param['name_ru']}', short_descr='{$param['short_descr_ru']}', body='{$param['body_ru']}', destinatie_produs='{$param['destinatie_produs']}', tags='{$param['tags']}', img_alt='{$param['img_alt']}', country_from='{$param['country_from']}', measure_name='{$param['measure_name']}', page_title='{$param['page_title']}', h1_title='{$param['h1_title']}', meta_title='{$param['meta_title']}', meta_keywords='{$param['meta_keywords']}', meta_description='{$param['meta_description']}', created_at=NOW(), updated_at=NOW()";
		$mysqli->query($query);
		return $ins_id;
	}

	function EditItem($param){
		global $mysqli;
		$param = $mysqli->smart_escape($param);
		$goods_subject_id = $param['goods_subject_id']?", goods_subject_id='{$param['goods_subject_id']}'":'';
		$brand_id = $param['brand_id']?", brand_id='{$param['brand_id']}'":'';
		$goods_type_id = $param['goods_type_id']?", goods_type_id='{$param['goods_type_id']}'":'';
		$one_c_code = $param['one_c_code']?", one_c_code='{$param['one_c_code']}'":'';
		$barcode = $param['barcode']?", barcode='{$param['barcode']}'":'';
		$active = $param['active']?", active='{$param['active']}'":'';
		$popular_element = $param['popular_element']?", popular_element='{$param['popular_element']}'":'';
		$new_element = $param['new_element']?", new_element='{$param['new_element']}'":'';
		$prof_element = $param['prof_element']?", prof_element='{$param['prof_element']}'":'';
		$price = $param['price']?", price='{$param['price']}'":'';
		//$price_promo = $param['price_promo']?", price_promo='{$param['price_promo']}'":'';
		//промо цену всегда обновляем
		$price_promo = ", price_promo='{$param['price_promo']}'";
		$price_b2b = $param['price_b2b']?", price_b2b='{$param['price_b2b']}'":'';
		$price_b2b_promo = $param['price_b2b_promo']?", price_b2b_promo='{$param['price_b2b_promo']}'":'';
		$b2b_type = $param['b2b_type']?", b2b_type='{$param['b2b_type']}'":'';
		$stock_type = $param['stock_type']?", stock_type='{$param['stock_type']}'":'';
		$puncte_bonus = $param['puncte_bonus']?", puncte_bonus='{$param['puncte_bonus']}'":'';
		$produse_compatibile = $param['produse_compatibile']?", produse_compatibile='{$param['produse_compatibile']}'":'';
		$produse_similare = $param['produse_similare']?", produse_similare='{$param['produse_similare']}'":'';
		$gramaj = $param['gramaj']?", gramaj='{$param['gramaj']}'":'';
		$products_count = $param['products_count']?", products_count='{$param['products_count']}'":'';
		$in_stoc = $param['in_stoc']?", in_stoc='{$param['in_stoc']}'":'';
		$youtube_link = $param['youtube_link']?", youtube_link='{$param['youtube_link']}'":'';
		$youtube_id = $param['youtube_id']?", youtube_id='{$param['youtube_id']}'":'';
		$query = "UPDATE goods_item_id
		SET id=id, updated_at=NOW() $goods_subject_id $brand_id $goods_type_id $one_c_code $barcode $active $popular_element $new_element $prof_element $price $price_promo $price_b2b $price_b2b_promo $b2b_type $stock_type $puncte_bonus $produse_compatibile $produse_similare $gramaj $products_count $in_stoc $youtube_link $youtube_id
		WHERE id='{$param['goods_item_id']}'
		LIMIT 1";
		$mysqli->query($query);
		if (CheckIfExistsItemLang($param['goods_item_id'], $param['lang_id'])){
		  $name = $param['name']?", name='{$param['name']}'":'';
		  $short_descr = $param['short_descr']?", short_descr='{$param['short_descr']}'":'';
		  $body = $param['body']?", body='{$param['body']}'":'';
		  $destinatie_produs = $param['destinatie_produs']?", destinatie_produs='{$param['destinatie_produs']}'":'';
		  $tags = $param['tags']?", tags='{$param['tags']}'":'';
		  $img_alt = $param['img_alt']?", img_alt='{$param['img_alt']}'":'';
		  $country_from = $param['country_from']?", country_from='{$param['country_from']}'":'';
		  $measure_name = $param['measure_name']?", measure_name='{$param['measure_name']}'":'';
		  $meta_title = $param['meta_title']?", meta_title='{$param['meta_title']}'":'';
		  $meta_description = $param['meta_description']?", meta_description='{$param['meta_description']}'":'';
			$query = "UPDATE goods_item
			SET id=id, updated_at=NOW() $name $short_descr $body $destinatie_produs $tags $img_alt $country_from $measure_name $meta_title $meta_description
			WHERE goods_item_id='{$param['goods_item_id']}' AND lang_id='{$param['lang_id']}'
			LIMIT 1";
			$mysqli->query($query);
		}else {
			$query = "INSERT INTO goods_item
			SET goods_item_id='{$param['goods_item_id']}', lang_id='{$param['lang_id']}', name='{$param['name']}', short_descr='{$param['short_descr']}', body='{$param['body']}', destinatie_produs='{$param['destinatie_produs']}', tags='{$param['tags']}', img_alt='{$param['img_alt']}', country_from='{$param['country_from']}', measure_name='{$param['measure_name']}', page_title='{$param['page_title']}', h1_title='{$param['h1_title']}', meta_title='{$param['meta_title']}', meta_keywords='{$param['meta_keywords']}', meta_description='{$param['meta_description']}', created_at=NOW(), updated_at=NOW()";
			$mysqli->query($query);
		}
		if (CheckIfExistsItemLang($param['goods_item_id'], $param['other_lang_id'])){
		  $name = $param['name_ru']?", name='{$param['name_ru']}'":'';
		  $short_descr = $param['short_descr_ru']?", short_descr='{$param['short_descr_ru']}'":'';
		  $body = $param['body_ru']?", body='{$param['body_ru']}'":'';
		  $destinatie_produs = $param['destinatie_produs']?", destinatie_produs='{$param['destinatie_produs']}'":'';
		  $tags = $param['tags']?", tags='{$param['tags']}'":'';
		  $img_alt = $param['img_alt']?", img_alt='{$param['img_alt']}'":'';
		  $country_from = $param['country_from']?", country_from='{$param['country_from']}'":'';
		  $measure_name = $param['measure_name']?", measure_name='{$param['measure_name']}'":'';
		  $meta_title = $param['meta_title']?", meta_title='{$param['meta_title']}'":'';
		  $meta_description = $param['meta_description']?", meta_description='{$param['meta_description']}'":'';
			$query = "UPDATE goods_item
			SET id=id, updated_at=NOW() $name $short_descr $body $destinatie_produs $tags $img_alt $country_from $measure_name $meta_title $meta_description
			WHERE goods_item_id='{$param['goods_item_id']}' AND lang_id='{$param['other_lang_id']}'
			LIMIT 1";
			$mysqli->query($query);
		}else {
			$query = "INSERT INTO goods_item
			SET goods_item_id='{$param['goods_item_id']}', lang_id='{$param['other_lang_id']}', name='{$param['name_ru']}', short_descr='{$param['short_descr_ru']}', body='{$param['body_ru']}', destinatie_produs='{$param['destinatie_produs']}', tags='{$param['tags']}', img_alt='{$param['img_alt']}', country_from='{$param['country_from']}', measure_name='{$param['measure_name']}', page_title='{$param['page_title']}', h1_title='{$param['h1_title']}', meta_title='{$param['meta_title']}', meta_keywords='{$param['meta_keywords']}', meta_description='{$param['meta_description']}', created_at=NOW(), updated_at=NOW()";
			$mysqli->query($query);
		}
		return true;
	}

	function AddParametr($param){
  	global $mysqli;
  	$param = $mysqli->smart_escape($param);
		$position = GetMinPosition('goods_parametr_id', " AND goods_subject_id='{$param['goods_subject_id']}'")-1;
  	$active = 1;
  	if ($param['parametr_type'] == 'input'){
    	switch ($param['measure_type']) {
    		case "no_measure":
    			$goods_measure_id = 0;
    			break;

    		case "with_measure":
    			$goods_measure_id = $param['goods_measure_id'];
    			break;

    		case "measure_list":
    			$goods_measure_id = 0;
    			break;

    		default:
    			break;
    	}
    	$measure_type = $param['measure_type'];
  	}else {
  	  $goods_measure_id = 0;
  	  $measure_type = '';
  	}
  	$query = "INSERT INTO goods_parametr_id
  	SET goods_subject_id='{$param['goods_subject_id']}', parametr_type='{$param['parametr_type']}', active='{$active}', position='{$position}', measure_type='{$measure_type}', goods_measure_id='{$goods_measure_id}', created_at=NOW(), updated_at=NOW()";
  	if ($ins_id = $mysqli->insert_and_return_id($query)){
  		$query = "INSERT INTO goods_parametr
  		SET goods_parametr_id='{$ins_id}', lang_id='{$param['lang_id']}', name='{$param['name']}', created_at=NOW(), updated_at=NOW()";
  		$mysqli->query($query);
  		$other_langs = CLang::OtherlangList($param['lang_id']);
  		if ($other_langs){
  			foreach ($other_langs as $k=>$v){
  				$query = "INSERT INTO goods_parametr
  				SET goods_parametr_id='{$ins_id}', lang_id='{$v['lang_id']}', name='', created_at=NOW(), updated_at=NOW()";
		  		$mysqli->query($query);
  			}
  		}
  	}

  	/*if($param['measure_type'] == 'measure_list' && $param['parametr_type'] == 'input'){
   		foreach ($param['goods_measure_list'] as $k=>$v){
  		  $query = "INSERT INTO goods_measure_list
  		  SET goods_parametr_id='{$ins_id}', goods_measure_id='{$v}', position='{$k}'";
  		  $mysqli->query($query);
  		}
  	}

  	//Значения параметра
  	if($param['parametr_type'] == 'select' || $param['parametr_type'] == 'radio' || $param['parametr_type'] == 'checkbox'){
  	  if(is_array($param['parametr_type_value']) && count($param['parametr_type_value']) > 0){
  	    foreach ($param['parametr_type_value'] as $k=>$v){
  	    	$v = trim($v);
  	    	if ($v){
	  	      $query = "INSERT INTO goods_parametr_value_id SET goods_parametr_id='{$ins_id}', cont_alias='{$param['cont_alias_value'][$k]}', position='{$k}', active='1'";
	  		    if ($goods_parametr_value_id = $mysqli->insert_and_return_id($query)){
	  		      $query = "INSERT INTO goods_parametr_value SET goods_parametr_value_id='{$goods_parametr_value_id}', lang_id='{$param['lang_id']}', name='{$v}'";
	  		      $mysqli->query($query);
	  		    }
  	    	}
  	    }
  	  }
  	}*/
  	return $ins_id;
  }

  function AddParametrValue($param){
  	global $mysqli;
  	$param = $mysqli->smart_escape($param);
		$position = GetMinPosition('goods_parametr_value_id', " AND goods_parametr_id='{$param['goods_parametr_id']}'")-1;
  	$active = 1;
  	$query = "INSERT INTO goods_parametr_value_id
  	SET goods_parametr_id='{$param['goods_parametr_id']}', active='{$active}', position='{$position}', created_at=NOW(), updated_at=NOW()";
  	if ($ins_id = $mysqli->insert_and_return_id($query)){
  		$query = "INSERT INTO goods_parametr_value
  		SET goods_parametr_value_id='{$ins_id}', lang_id='{$param['lang_id']}', name='{$param['name']}', created_at=NOW(), updated_at=NOW()";
  		$mysqli->query($query);
  		$other_langs = CLang::OtherlangList($param['lang_id']);
  		if ($other_langs){
  			foreach ($other_langs as $k=>$v){
  				$query = "INSERT INTO goods_parametr_value
  				SET goods_parametr_value_id='{$ins_id}', lang_id='{$v['lang_id']}', name='', created_at=NOW(), updated_at=NOW()";
		  		$mysqli->query($query);
  			}
  		}
  	}
  	return $ins_id;
  }

  function AddParametrAndValueInItem($item_id, $parametr_id, $value_id){
    global $mysqli;
    $item_id = intval($item_id);
    $parametr_id = intval($parametr_id);
    $value_id = intval($value_id);
    $query = "INSERT INTO goods_parametr_item_id
    SET goods_item_id='{$item_id}', goods_parametr_id='{$parametr_id}', created_at=NOW(), updated_at=NOW()";
  	$ins_item_id = $mysqli->insert_and_return_id($query);
  	$query = "INSERT INTO goods_parametr_item_rsc
    SET goods_parametr_item_id='{$ins_item_id}', goods_parametr_value_id='{$value_id}', created_at=NOW(), updated_at=NOW()";
  	$mysqli->query($query);
  }

  function DeleteParametrsFromItem($item_id){
    global $mysqli;
    $item_id = intval($item_id);
    $query = "DELETE FROM goods_parametr_item_id
    WHERE goods_item_id='{$item_id}'";
  	$mysqli->query($query);
  }

  function AddParametrAndValueFromArray($data, $parametr_key, $value_key){
    global $mysqli;
    $parametr = trim($data[$parametr_key]);
    $value = trim($data[$value_key]);
    if (!empty($parametr) && !empty($value)){
      if (!$parametr_id = GetParametrByName($parametr, 1)){
        $param = array();
        $param['name'] = $parametr;
        $param['parametr_type'] = 'select';
        $param['goods_subject_id'] = 1;
        $param['lang_id'] = 2;
        $parametr_id = AddParametr($param);
      }
      if ($parametr_id > 0){
        if (!$value_id = GetParametrValueByName($value, $parametr_id)){
          $param = array();
          $param['name'] = $value;
          $param['goods_parametr_id'] = $parametr_id;
          $param['lang_id'] = 2;
          $value_id = AddParametrValue($param);
        }
      }
      return array('goods_parametr_id'=>$parametr_id, 'goods_parametr_value_id'=>$value_id);
    }
  }

  function AddFoto($item_id, $file_name){
  	global $mysqli;
		$item_id = intval($item_id);
		if ($file_name){
		  $punct_pos = mb_strrpos($file_name, ".");
      $extension = mb_substr($file_name, $punct_pos);
    	$time = getmicrotimeS();
    	$img = $time.$extension;
		  copy($file_name, DOC_ROOT.'/upfiles/gallery/'.$img);
		}
		if ($img){
		  CreateImageManipulator("/upfiles/gallery/", "/upfiles/gallery/s/", $img, 100, 100, true);
		  CreateImageManipulator("/upfiles/gallery/", "/upfiles/gallery/m/", $img, 230, 230, true);
		  CreateImageManipulator("/upfiles/gallery/", "/upfiles/gallery/", $img, 800, 800, true);
  		$position = GetMinPosition("goods_foto", "AND goods_item_id='{$item_id}'")-1;
  		$query = "INSERT INTO goods_foto
  		SET goods_item_id='{$item_id}', img='{$img}', position='{$position}', active='1'";
  		return $mysqli->insert_and_return_id($query);
    }
	}

	function DeleteFotosFromItem($item_id){
  	global $mysqli;
		$item_id = intval($item_id);
		$query = "SELECT goods_foto.*, id AS goods_foto_id
		FROM goods_foto
		WHERE goods_item_id='{$item_id}'";
		$fotos_list = $mysqli->getlist($query);
		if (!empty($fotos_list)){
			foreach ($fotos_list as $k=>$v){
				@unlink(DOC_ROOT."/upfiles/gallery/".$v['img']);
    		@unlink(DOC_ROOT."/upfiles/gallery/m/".$v['img']);
    		@unlink(DOC_ROOT."/upfiles/gallery/s/".$v['img']);
    		$query="DELETE FROM goods_foto
    		WHERE id='{$v['goods_foto_id']}'
    		LIMIT 1";
    		$mysqli->query($query);
			}
		}
	}

$file = 'estel.xlsx';
require_once DOC_ROOT.'/estel/SimpleXLSX.php';
$xlsx = SimpleXLSX::parse($file);
//pre($xlsx->sheetNames());
if($die == 1){
  pre($xlsx->rows());
  die();
}
if (!empty($xlsx->rows())){//change to $xlsx
  foreach ($xlsx->rows() as $k=>$v){
    if ($k > 0 && !empty($v[3])){
      $item_articol = trim($v[3]);
//Рубрики
      $subject_0 = trim($v[9]);
      $subject_1 = trim($v[10]);
      $subject_2 = trim($v[11]);
      if (!$subject_0_id = GetSubjectByName($subject_0, 1)){
        $param = array();
        $param['name'] = $subject_0;
        $param['alias'] = mb_strtolower(transliterate_with_spaces($subject_0));
        $param['p_id'] = 1;
        $param['lang_id'] = 2;
        $subject_0_id = AddSubject($param);
      }
      if ($subject_0_id > 0){
        if (!$subject_1_id = GetSubjectByName($subject_1, $subject_0_id)){
          $param = array();
          $param['name'] = $subject_1;
          $param['alias'] = mb_strtolower(transliterate_with_spaces($subject_1));
          $param['p_id'] = $subject_0_id;
          $param['lang_id'] = 2;
          $subject_1_id = AddSubject($param);
        }
        if ($subject_1_id > 0){
          if (!empty($subject_2)){
            if (!$subject_2_id = GetSubjectByName($subject_2, $subject_1_id)){
              $param = array();
              $param['name'] = $subject_2;
              $param['alias'] = mb_strtolower(transliterate_with_spaces($subject_2));
              $param['p_id'] = $subject_1_id;
              $param['lang_id'] = 2;
              $subject_2_id = AddSubject($param);
            }
            $subject_item_id = $subject_2_id;
          }else $subject_item_id = $subject_1_id;
        }
      }
//      pre($subject_item_id);
      //pre($k.'-'.$subject_2.'-'.$subject_item_id);
      //убрали проверку на рубрику для возможности загрузки товаров только с ценами
      //if ($subject_item_id > 0){//должна быть конечная рубрика
        //Брэнды
        $brand_0 = trim($v[7]);
        $brand_1 = trim($v[8]);
        if (!empty($brand_0)){
          if (!$brand_0_id = GetBrandByName($brand_0, 0)){
            $param = array();
            $param['name'] = $brand_0;
            $param['alias'] = mb_strtolower(transliterate_with_spaces($brand_0));
            $param['p_id'] = 0;
            $param['lang_id'] = 2;
            $brand_0_id = AddBrand($param);
          }
          if ($brand_0_id > 0){
            if (!empty($brand_1)){
              if (!$brand_1_id = GetBrandByName($brand_1, $brand_0_id)){
                $param = array();
                $param['name'] = $brand_1;
                $param['alias'] = mb_strtolower(transliterate_with_spaces($brand_1));
                $param['p_id'] = $brand_0_id;
                $param['lang_id'] = 2;
                $brand_1_id = AddBrand($param);
              }
              $brand_item_id = $brand_1_id;
            }else $brand_item_id = $brand_0_id;
          }
        }
        //Типы
        $type = trim($v[14]);
        if (!$type_id = GetTypeByName($type, $item_articol)){
          $param = array();
          $param['name'] = $type;
          $param['lang_id'] = 2;
          $type_id = AddType($param);
        }
        //Параметры
        $parametr_array = array();
        for ($i = 22; $i <= 41; $i = $i+2){//идем на один меньше чем в реальности
          $parametr_array[] = AddParametrAndValueFromArray($v, $i, $i+1);
        }
        $item_articol = trim($v[3]);
        $item_name = trim($v[2]);
        $item_id = GetItemIDByArticol($item_articol);
        $param = array();
        $param['goods_subject_id'] = $subject_item_id;
        $param['one_c_code'] = intval(trim($v[1]));
        $param['brand_id'] = $brand_item_id;
        $param['goods_type_id'] = $type_id;
        $param['articol'] = $item_articol;
        $param['barcode'] = trim($v[0]);
        $param['alias'] = mb_strtolower(transliterate_with_spaces($item_name.'-'.transliterate_with_spaces($item_articol)));
        $param['active'] = $v[16]?1:0;
        $param['popular_element'] = $v[19]?1:0;
        $param['new_element'] = $v[18]?1:0;
        $param['prof_element'] = $v[20]?1:0;
        $param['tags'] = trim($v[21]);
        $param['price'] = trim($v[45]);
        $param['price_promo'] = trim($v[46]);
        $param['b2b_type'] = 'all';
        $param['puncte_bonus'] = trim($v[13]);
        $param['youtube_link'] = GetYoutubeIdByLink(trim($v[54]));
        $param['youtube_id'] = GetYoutubeIdByLink(trim($v[55]));
        $param['produse_compatibile'] = trim($v[56]);
        $param['produse_compatibile'] = trim($v[57]);
        $param['produse_similare'] = trim($v[58]);
        $param['gramaj'] = trim($v[42]);
        $param['products_count'] = trim($v[44]);
        $param['in_stoc'] = $param['products_count'] > 0?1:0;
        $param['stock_type'] = trim($v[50]);
        //lang
        $param['name'] = $item_name;
        $param['name_ru'] = trim($v[4]);
        $param['body'] = $v[5];
        $param['body_ru'] = $v[6];
        $param['destinatie_produs'] = trim($v[15]);
        $param['meta_title'] = trim($v[52]);
        $param['meta_description'] = trim($v[53]);
        $param['img_alt'] = trim($v[49]);
        $param['country_from'] = trim($v[51]);
        $param['measure_name'] = trim($v[43]);
        $param['lang_id'] = 2;
        $param['other_lang_id'] = 3;
        $add_foto = 1;
        if(!$item_id && $param['alias']){//внос товара; добавили проверку на алиас
          $add_foto = 1;
          $item_id = AddItem($param);
        }else {//редактирование товара
          $param['goods_item_id'] = $item_id;
          EditItem($param);
          //удаляем все параметры
          DeleteParametrsFromItem($item_id);
        }
        if ($item_id){
          //внос параметров
          if (!empty($parametr_array)){
            foreach ($parametr_array as $pv){
              AddParametrAndValueInItem($item_id, $pv['goods_parametr_id'], $pv['goods_parametr_value_id']);
            }
          }
          //Фото
          if ($add_foto == 1){//пока фото не трогаем вообще
            $img_main = trim($v[48]);
            $fotos_deleted = 0;
            if ($img_main){
              $fotos_deleted = 1 ;
              //DeleteFotosFromItem($item_id);//удаляем фото только в случае если есть фото для загрузки
              AddFoto($item_id, $img_main);
            }
            /*$img_sec = trim($v[58]);
            if (file_exists(DOC_ROOT.'/estel/img/'.$img_sec.'.jpg') || file_exists(DOC_ROOT.'/estel/img/'.$img_sec.'.png')){
              //if ($fotos_deleted == 0) DeleteFotosFromItem($item_id);//удаляем фото только в случае если есть фото для загрузки
              //AddFoto($item_id, $img_sec);
            }*/
          }
        }
      //}
    }
  }
}
$location = "Location: ".$_SERVER[HTTP_REFERER];
@header($location);
?>