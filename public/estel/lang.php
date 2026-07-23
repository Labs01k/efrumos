<?php

class CLang{

  function GetLangsList($active=null){
  	global $mysqli;
  	if (!is_null($active)){
  		$active=" WHERE active='".intval($active)."'";
  	}
    $query = "SELECT lang.*, lang.id AS lang_id
    FROM lang
    $active
    ORDER BY position DESC";
    $res = $mysqli->getlist($query);
    return $res;
  }

 	function GetLangByID($lang_id){
  	global $mysqli;
	 	$lang_id = intval($lang_id);
	 	$query="SELECT lang.*, lang.id AS lang_id
	 	FROM lang
	 	WHERE id='{$lang_id}'
	 	LIMIT 1";
	 	$res = $mysqli->getone($query);
	 	return $res;
 	}

	function GetDefaultLang(){
  	global $mysqli;
		$query="SELECT lang.*, lang.id AS lang_id
	 	FROM lang
	 	WHERE default_lang='1'
	 	LIMIT 1";
	 	$row = $mysqli->getone($query);
	 	return $row;
	}

	function GetLangIDByName($lang){
  	global $mysqli;
  	$lang = $mysqli->smart_escape($lang);
	  $query = "SELECT id
	  FROM lang
	  WHERE lang='{$lang}'
		LIMIT 1";
	  $row = $mysqli->getone($query);
	  return $row['id'];
	}

	function IfActiveLang($lang){
  	global $mysqli;
  	$lang = $mysqli->smart_escape($lang);
	  $query = "SELECT active
	  FROM lang
	  WHERE lang='{$lang}'";
	  $row = $mysqli->getone($query);
	  return $row['active'];
	}

	function OtherlangList($lang_id){
  	global $mysqli;
		$lang_id = intval($lang_id);
	  $query = "SELECT lang.*, lang.id AS lang_id
	  FROM lang
	  WHERE lang.id<>'{$lang_id}' AND active='1'";
	  $res = $mysqli->getlist($query);
	  return $res;
	}

	function GetLangDescrByID($lang_id){
  	global $mysqli;
		$lang_id = intval($lang_id);
	  $query = "SELECT descr
	  FROM lang
	  WHERE id='{$lang_id}'
		LIMIT 1";
	  $row = $mysqli->getone($query);
	  return $row['descr'];
	}

	function GetLangNameByID($lang_id){
  	global $mysqli;
		$lang_id = intval($lang_id);
		$query="SELECT lang
		FROM lang
		WHERE id='{$lang_id}'
		LIMIT 1";
	  $row = $mysqli->getone($query);
		return $row['lang'];
	}

	function CheckIfExistsLangID($lang_id){
  	global $mysqli;
		$lang_id = intval($lang_id);
		$query="SELECT id
		FROM lang
		WHERE id='{$lang_id}'
		LIMIT 1";
		return $mysqli->query_and_return_true_false($query);
	}

 	function AddLang($param){
  	global $mysqli;
	 	$param = $mysqli->smart_escape($param);
	 	$active = $param['active']=='on'?1:0;
	 	$position = GetMinPosition("lang")-1;
	 	$query="INSERT INTO lang
	 	SET lang='{$param['lang']}', descr='{$param['descr']}', active='{$active}', position='{$position}'";
	 	return $mysqli->insert_and_return_id($query);
 	}

  function EditLang($param){
  	global $mysqli;
	 	$param = $mysqli->smart_escape($param);
	 	$query="UPDATE lang
	 	SET lang='{$param['lang']}', descr='{$param['descr']}'
	 	WHERE id='{$param['lang_id']}'
	 	LIMIT 1";
	 	return $mysqli->query($query);
  }

  function MakeDefaultLang($lang_id){
  	global $mysqli;
	 	$lang_id = intval($lang_id);
	 	$query="UPDATE lang
	 	SET default_lang=1
	 	WHERE id='{$lang_id}'";
	 	$mysqli->query($query);
	 	$query="UPDATE lang
	 	SET default_lang=0
	 	WHERE id<>'{$lang_id}'";
	 	return $mysqli->query($query);
  }

  function ChangeActiveLang($lang_id, $active){
  	global $mysqli;
	 	$lang_id = intval($lang_id);
	 	$active = intval($active);
	 	$query="UPDATE lang
	 	SET active='{$active}'
	 	WHERE id='{$lang_id}'
	 	LIMIT 1";
	 	return $mysqli->query($query);
  }

  function CheckIfExists($lang, $noid=null){
  	global $mysqli;
	 	$lang = $mysqli->smart_escape($lang);
	 	if (!is_null($noid)){
	 		$noid=" AND id<>'".intval($noid)."'";
	 	}
	 	$query="SELECT id
	 	FROM lang
	 	WHERE lang='$lang' $noid
	 	LIMIT 1";
	 	return $mysqli->query_and_return_true_false($query);
  }

}
?>