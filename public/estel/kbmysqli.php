<?php
//Узнать у Влада -  стоит ли делать free_mem
//http://md.php.net/manual/en/mysqli-result.free.php
//http://php.net/manual/en/mysqli.query.php - насчет директивы MYSQLI_USE_RESULT при больших объемах - на 5.1 не проканало - выдало ошибку

class CKBMysqli extends mysqli {
	function getlist($query, $free_mem=1){
		if ($result = $this->query($query)){
			while ($row = $result->fetch_assoc()){
				$res[] = $row;
			}
			if ($free_mem == 1) $result->close();
		}elseif (SHOWMYSQLERROR == 1){
			printf("Errormessage: %s\n", $this->error);
		}
		return $res;
	}
	
	function getone($query, $free_mem=1){
		if ($result = $this->query($query)){
			$row = $result->fetch_assoc();
			if ($free_mem == 1) $result->close();
		}elseif (SHOWMYSQLERROR == 1){
			printf("Errormessage: %s\n", $this->error);
		}
		return $row;
	}
	
	function insert_and_return_id($query){
		if ($result = $this->query($query)){			
			return $this->insert_id;
		}
	}
	
	function query_and_return_true_false($query){
		$result = $this->query($query);
		if ($this->affected_rows){
			return true;
		}
	}
	
	function smart_escape($value, $clean_template=0, $key=null){
		if(!is_array($value)){
	  	if (get_magic_quotes_gpc()) $value = stripslashes($value); //Если включен magic_quotes - уберем слэши
	  	$substr_key = substr($key, -3);
	  	/*if (!is_null($key) && $substr_key == "_id"){//Если передаваемый параметр является id - жестко ставим intval
	     	$value = intval($value);
	  	}else {*///Если любой другой параметр
	    	if (!is_numeric($value)) {//Если переменная - не число, то экранируем, если число - остается как есть
	    		if ($clean_template == 1) $value = $this->clean_template($value);//Если параметр передается с фронта - то для начала почистим его от всякой фигни
	     		$value = $this->real_escape_string($value);//Экранируем
	     	}
	  	//}
	  }else {
	  	$k=array_keys($value);
	  	for($i=0;$i<count($k);$i++)
	    	$value[$k[$i]]=$this->smart_escape($value[$k[$i]], $clean_template, $k[$i]);
	  }
	  return $value;
	}

	

	function clean_template ($t="", $htmlspecialchars=0, $strip_tags=0, $strip_tags_tags='') {
	  if(!is_array($t)){
  		if($strip_tags) $t = strip_tags($t, $strip_tags_tags);
  	  while( preg_match( "#script(.+?)/script#ies", $t ) ) {
  	  	$t = preg_replace( "#script(.+?)/script#ies", "" , $t);
  	  }
  	  $t = preg_replace( "/javascript/i" , "j&#097;v&#097;script", $t );
  	  $t = preg_replace( "/alert/i"      , "&#097;lert", $t );
  	  $t = preg_replace( "/about:/i"     , "&#097;bout:", $t );
  	  $t = preg_replace( "/onmouseover/i", "&#111;nmouseover", $t );
  	  $t = preg_replace( "/onmouseenter/i", "&#111;nmouseenter", $t );
  	  $t = preg_replace( "/onclick/i"    , "&#111;nclick", $t );
  	  $t = preg_replace( "/onload/i"     , "&#111;nload", $t );
  	  $t = preg_replace( "/onsubmit/i"   , "&#111;nsubmit", $t );
  	  $t = preg_replace( "/onmouseout/i" , "&#111;nmouseout", $t );
  	  $t = preg_replace( "/onunload/i"   , "&#111;nunload", $t );
  	  $t = preg_replace( "/onabort/i"    , "&#111;nabort", $t );
  	  $t = preg_replace( "/onerror/i"    , "&#111;nerror", $t );
  	  $t = preg_replace( "/onblur/i"     , "&#111;nblur", $t );
  	  $t = preg_replace( "/onchange/i"   , "&#111;nchange", $t );
  	  $t = preg_replace( "/onfocus/i"    , "&#111;nfocus", $t );
  	  $t = preg_replace( "/onreset/i"    , "&#111;nreset", $t );
  	  $t = preg_replace( "/ondblclick/i" , "&#111;ndblclick", $t );
  	  $t = preg_replace( "/onkeydown/i"  , "&#111;nkeydown", $t );
  	  $t = preg_replace( "/onkeypress/i" , "&#111;nkeypress", $t );
  	  $t = preg_replace( "/onkeyup/i"    , "&#111;nkeyup", $t );
  	  $t = preg_replace( "/onmousedown/i", "&#111;nmousedown", $t );
  	  $t = preg_replace( "/onmouseup/i"  , "&#111;nmouseup", $t );
  	  $t = preg_replace( "/onselect/i"   , "&#111;nselect", $t );
  		$t = preg_replace( "/ecmascript/i"	, "", $t );
  	 	$t = preg_replace( "/about:/si"	, "", $t );
  		$t = preg_replace( "/data:/si"	, "", $t );
  		$t = preg_replace( "/onfocus/i"	, "", $t );
  		$t = preg_replace( "/onblur/i"	, "", $t );
  		$t = preg_replace( "/ondblclick/i"	, "", $t );
  		$t = preg_replace( "/onmousedown/i"	, "", $t );
  		$t = preg_replace( "/onmouseup/i"	, "", $t );
  		$t = preg_replace( "/onmousemove/i"	, "", $t );
  		$t = preg_replace( "/onmouseout/i"	, "", $t );
  		$t = preg_replace( "/onkeypress/i"	, "", $t );
  		$t = preg_replace( "/onkeydown/i"	, "", $t );
  		$t = preg_replace( "/onkeyup/i"	, "", $t );
  		$t = preg_replace( "/onunload/i"	, "", $t );
  	  $t = preg_replace( "/onabort/i"	, "", $t );
  	  $t = preg_replace( "/onerror/i"	, "", $t );
  		$t = preg_replace( "/onchange/i"	, "", $t );
  		$t = preg_replace( "/onreset/i"	, "", $t );
  		$t = preg_replace( "/onselect/i"	, "", $t );	
  		$t = preg_replace( "/document\./i"	, "", $t );
  		$t = preg_replace( "/window\./i"	, "", $t );
  	  if($htmlspecialchars) $t = htmlspecialchars($t);
	  }else {
	  	$k=array_keys($t);
	  	for($i=0;$i<count($k);$i++)
	    	$t[$k[$i]]=$this->clean_template($t[$k[$i]], $htmlspecialchars, $strip_tags, $strip_tags_tags);
	  }
	  return $t;
	}
}
?>