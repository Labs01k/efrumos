<?php
//Define language
$lang_default = CLang::GetDefaultLang();
$req_uri=explode("/", $_SERVER['REQUEST_URI']);
if (empty($_REQUEST['lang'])){
  define("LANG", $lang_default['lang']);
  define("LANGID", $lang_default['id']);
}else {
  if($_REQUEST['lang'] != "ru" && $_REQUEST['lang'] != "ro" && $_REQUEST['lang'] != "en"){
    define("LANG", $lang_default['lang']);
    define("LANGID", $lang_default['id']);
  }else{
  	define("LANG", $_REQUEST['lang']);
  	define("LANGID", CLang::GetLangIDByName($_REQUEST['lang']));
  }
}

function pre($obj){
  echo "<pre>";
  print_r($obj);
  echo "</pre>";
}

function undo_slashes($v){
  if(!is_array($v)){
  	if (get_magic_quotes_gpc()) $v = stripslashes($v);
  }else {
  	$k=array_keys($v);
  	for($i=0;$i<count($k);$i++)
    	$v[$k[$i]]=undo_slashes($v[$k[$i]]);
  }
  return $v;
}

function getmicrotimeS(){
  list($usec, $sec) = explode(" ",microtime());
  list ($usec, $s) = explode(".", $usec);
  $s .= time();
  return ($s);
}

function getmicrotimeSS(){
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
}

/**
 * Возвращает строку со списком страниц и ссылками на них...
 *
 * @param int $total
 * @param int $page
 * @return string
 */
function pages($total, $page){
  global $l;
  $res = "";
  $pp = ceil($total/PERPAGE);
  $href = "";
  $uri = explode("&", $_SERVER['QUERY_STRING']);
  foreach ($uri as $k=>$v){
    $suri = explode("=",$v);
    if ($suri[0] !="page"){
      if ($suri[0]){
        $href .= "&".$v;
      }
    }
  }
  if ($pp>0){
    $res .= ShowLabelById(1, LANGID)." ";
    for ($i=1;$i<=$pp;$i++){
      if (PAGE==$i){
        $res .= $i." ";
      }else{
        $res .= "<a href=\"?page=$i$href\">$i</a> ";
      }
    }
  }
  return $res;
}

function pages_front_old($total, $page){
  preg_match("/\/(.*)\//", $_SERVER['REQUEST_URI'], $uri_arr1);
  preg_match("/\/([^\/]*)\./", $_SERVER['REQUEST_URI'], $uri_arr2);
  if(preg_match("/(page-)/", $uri_arr2[1])){//Если уже на странице
    $href = $uri_arr1[0];
  }else{
    $href = $uri_arr1[0].$uri_arr2[1]."/";
  }

  $res = "";
  $pp = ceil($total/PERPAGE);

  $res .= '<div class="s1">';
  if ($pp>0){
    for ($i=1;$i<=$pp;$i++){
      if (PAGE==$i){
        $res .= '<span class="s1-a">'.$i.'</span>';
      }else{
        $res .= "<a href=\"{$href}page-{$i}.html\">{$i}</a>";
      }
    }
  }
  $res .= '<div class="clear"></div>
  </div>';
  return $res;
}

function pages_front($total, $page, $perpage=null, $qsa=null){
	if (is_null($perpage)){
		$perpage = PERPAGE;
	}
  preg_match("/\/(.*)\//", $_SERVER['REQUEST_URI'], $uri_arr1);
  preg_match("/\/([^\/]*)\./", $_SERVER['REQUEST_URI'], $uri_arr2);
  if(preg_match("/(page-)/", $uri_arr2[1])){//Если уже на странице
    $href = mb_substr($uri_arr1[0], 0, mb_strlen($uri_arr1[0])-1);
  }else{
    $href = $uri_arr1[0].$uri_arr2[1];
  }

  $res = "";
  $pp = ceil($total/$perpage);
  $pp_start = 1;

  if($pp>=10){
    $perpage_half = ceil(10/2);

    if($page<$perpage_half){
      $pp_end = $page+$perpage_half+($perpage_half-$page);
    }else{
      $pp_end = $page + $perpage_half;
    }
    if($pp_end>$pp){
        $pp_end = $pp;
    }

    $pp_start = $pp_end - (10-1);
    if($pp_start<1){
      $pp_start = 1;
    }
  }else {
  	$pp_end=$pp;
  }

  $res .= '<div class="s1">';
  if($page > 1){
    $page_prev = $page-1;
    if ($page_prev == 1){
      $res .= '<a href="'.$href.'.html'.$qsa.'">&laquo;</a>';
    }else {
      $res .= '<a href="'.$href.'/page-'.$page_prev.'.html'.$qsa.'">&laquo;</a>';
    }
  }
  if ($pp>0){
    for ($i=$pp_start;$i<=$pp_end;$i++){
      if (PAGE==$i){
        $res .= '<span class="s1-a">'.$i.'</span>';
      }else{
        if ($i == 1){
          $res .= '<a href="'.$href.'.html'.$qsa.'">'.$i.'</a>';
        }else {
          $res .= '<a href="'.$href.'/page-'.$i.'.html'.$qsa.'">'.$i.'</a>';
        }
      }
    }
  }
  if($page < $pp_end){
    $page_next = $page+1;
    $res .= '<a href="'.$href.'/page-'.$page_next.'.html'.$qsa.'">&raquo;</a>';
  }

  $res .= '<div class="clear"></div></div>';
  return $res;
}

function pages_for_module_search_new($total, $page, $perpage=null){
  if(is_null($perpage)){
    $perpage = PERPAGE;
  }
  global $l;
  $res_search = "";
  $pp = ceil($total/$perpage);
  $href = "";
  $pp_start = 1;
  if($pp>10){
    $perpage_half = ceil(10/2);

    if($page<$perpage_half){
      $pp_end = $page+$perpage_half+($perpage_half-$page);
    }else{
      $pp_end = $page + $perpage_half;
    }
    if($pp_end>$pp){
        $pp_end = $pp;
    }
    $pp_start = $pp_end - (10-1);
    if($pp_start<1){
      $pp_start = 1;
    }
  }else {
  	$pp_end=$pp;
  }
  foreach ($_GET as $k=>$v) {
  	if($k != "lang" AND $k != "page" AND $k!="module"){
  	  $href .= $k."=".urlencode($v)."&";
  	}
  }
  if ($pp>0){
    $res_search .= "<div class=\"pagesnew\">";
    if($page > 1){
      $page_prev = $page-1;
      $res_search .= "<a href=\"?{$href}page={$page_prev}\"><img src=\"/i/pg.gif\" alt=\"\" /></a>";
    }
    for ($i=$pp_start;$i<=$pp_end;$i++){
      if (PAGE==$i){
        $res_search .= "<span>".$i."</span>";
      }else{
        $res_search .= "<a href=\"?{$href}page={$i}\">$i</a> ";
      }
    }
    if($page < $pp_end){
      $page_next = $page+1;
      $res_search .= "<a href=\"?{$href}page={$page_next}\"><img src=\"/i/pg.gif\" alt=\"\" /></a>";
    }
    $res_search .= "</div>";
  }
  return $res_search;
}

function upload($dir, $name){
	$uploaddir = $_SERVER['DOCUMENT_ROOT'].$dir;
	if ($_FILES[$name]['size'] > 0){
    $punct_pos = mb_strrpos($_FILES[$name]['name'], ".");
    $extension = mb_substr($_FILES[$name]['name'], $punct_pos);
  	$time = getmicrotimeS();
  	$s2 = $time.$extension;
  	if (move_uploaded_file($_FILES[$name]['tmp_name'], $uploaddir .	$s2)) {
  		$a = 0;
  		chmod ($uploaddir.$s2,0775);
  		return $s2;
  	}
	}
}

function upload_blueimp($dir, $name){
	$uploaddir = $_SERVER['DOCUMENT_ROOT'].$dir;
	if ($_FILES[$name]['size'] > 0){
    $punct_pos = mb_strrpos($_FILES[$name]['name'][0], ".");
    $extension = mb_substr($_FILES[$name]['name'][0], $punct_pos);
  	$time = getmicrotimeS();
  	$s2 = $time.$extension;
  	if (move_uploaded_file($_FILES[$name]['tmp_name'][0], $uploaddir .	$s2)) {
  		$a = 0;
  		chmod ($uploaddir.$s2,0775);
  		return $s2;
  	}
	}
}

function uploadwithgivenname($dir, $name, $file_name){
	$uploaddir = $_SERVER['DOCUMENT_ROOT'].$dir;
	if ($_FILES[$name]['size'] > 0){
    $punct_pos = mb_strrpos($_FILES[$name]['name'], ".");
    $extension = mb_strtolower(mb_substr($_FILES[$name]['name'], $punct_pos));
  	$s2 = $file_name.$extension;
  	if (move_uploaded_file($_FILES[$name]['tmp_name'], $uploaddir .	$s2)) {
  		$a = 0;
  		chmod ($uploaddir.$s2,0775);
  		return $s2;
  	}
	}
}

function multipleupload($dir, $name, $key){
	$uploaddir = $_SERVER['DOCUMENT_ROOT'].$dir;
  	$time = getmicrotimeS();
  	$punct_pos = strrpos($_FILES[$name]['name'][$key], ".");
    $extension = substr($_FILES[$name]['name'][$key], $punct_pos);
    $extension_length=strlen($extension);
    $file_lentgh=strlen($_FILES[$name]['name'][$key]);
    $name_length=$file_lentgh-$extension_length;
    $file_name = substr($_FILES[$name]['name'][$key], 0, $name_length);
  	$s2 = $time.$extension;
  	if (move_uploaded_file($_FILES[$name]['tmp_name'][$key], $uploaddir.$s2)) {
  		chmod ($uploaddir.$s2,0775);
  		$res['name'] = $file_name;
  		$res['attach'] = $s2;
  		return $res;
  	}
}

function uploadwithname($dir, $name){
	$uploaddir = $_SERVER['DOCUMENT_ROOT'].$dir;
	if ($_FILES[$name]['size'] > 0){
  	if (move_uploaded_file($_FILES[$name]['tmp_name'], $uploaddir .	$_FILES[$name]['name'])) {
  		$a = 0;
  		chmod ($uploaddir.$_FILES[$name]['name'],0775);
  		return true;
  	}
	}
}

	function IfHasName($id, $lang_id, $table){
		global $mysqli;
		$id = intval($id);
		$lang_id = intval($lang_id);
		$table = $mysqli->smart_escape($table);
	  $table_id=$table."_id";
	  $query = "SELECT `$table`.`name`
	  FROM `$table`
	  WHERE `$table_id`='$id' AND `lang_id`='$lang_id'
	  LIMIT 1";
	  $row = $mysqli->getone($query);
		return $row['name'];
	}

	function ShowName($table, $id, $lang_id=null){
		global $mysqli;
		$id = intval($id);
		$table = $mysqli->smart_escape($table);
    if(!is_null($lang_id)){
      $lang_id=" AND lang_id='".intval($lang_id)."'";
      $table_id=$table."_id";
      $where="$table_id='$id'";
    }else{
      $lang_id="";
      $where="id='$id'";
    }
    $query="SELECT name
    FROM `$table`
    WHERE $where $lang_id
    LIMIT 1";
    $row = $mysqli->getone($query);
    return $row['name'];
  }

function CutLongWords($string, $word_counts){
  $string_ok="";
  $string=strip_tags(str_replace('<br />',' ',$string));
  $string=str_replace('\r\n',' ',$string);
  $string_array=explode(' ',$string);
  foreach ($string_array as $k=>$v){
     $string_count=mb_strlen($v);
    //echo $string_count."<br>";
    //mb_detect_encoding($v)=='UTF-8'?$word_counts=$word_counts*1.2:$word_counts=$word_counts;
    if($string_count>$word_counts){
      $v=mb_substr($v, 0, $word_counts)."... ";
    }else {
      $v=$v." ";
    }
    $string_ok.=$v;
  }
  $result=$string_ok;
  return $result;
}

  function GetPosition($id, $table, $and=null){
		global $mysqli;
  	$id = intval($id);
		$table = $mysqli->smart_escape($table);
    $query = "SELECT position
    FROM `$table`
    WHERE id='$id' $and
    LIMIT 1";
    $row = $mysqli->getone($query);
    return $row['position'];
  }

  function GetLevel($id, $table){
		global $mysqli;
  	$id = intval($id);
		$table = $mysqli->smart_escape($table);
    $query = "SELECT level
    FROM `$table`
    WHERE id='$id'
    LIMIT 1";
    $row = $mysqli->getone($query);
    return $row['level'];
  }

  function IfHasChild($id, $table, $active=null, $deleted=null){
		global $mysqli;
  	$id = intval($id);
		$table = $mysqli->smart_escape($table);
    if (!is_null($active)){
    	$active=" AND $table.active='".intval($active)."'";
    }
    if (!is_null($deleted)){
      $deleted = " AND $table.deleted='".intval($deleted)."'";
    }
    $query = "SELECT id
    FROM `$table`
    WHERE p_id='$id' $active $deleted";
    return $mysqli->query_and_return_true_false($query);
  }

  function GetPidId($id, $table){
		global $mysqli;
  	$id = intval($id);
		$table = $mysqli->smart_escape($table);
    $query = "SELECT p_id
    FROM `$table`
    WHERE id='$id'
    LIMIT 1";
    $row = $mysqli->getone($query);
    return $row['p_id'];
  }

  function GetMaxPosition($table, $and=null){
		global $mysqli;
		$table = $mysqli->smart_escape($table);
    $query = "SELECT position
    FROM `$table`
    WHERE 1=1 $and
    ORDER BY position DESC
    LIMIT 1";
    $row = $mysqli->getone($query);
    return $row['position'];
  }

  function GetMinPosition($table, $and=null){
		global $mysqli;
		$table = $mysqli->smart_escape($table);
    $query = "SELECT position
    FROM `$table`
    WHERE 1=1 $and
    ORDER BY position ASC
    LIMIT 1";
    $row = $mysqli->getone($query);
    return $row['position'];
  }

  function GetNav($id, $table){
		global $mysqli;
    $i=1;
    $id = intval($id);
		$table = $mysqli->smart_escape($table);
    while (true){
      $query = "SELECT $table.p_id, $table.id
      FROM `$table`
      WHERE $table.id='$id'";
      $result = $mysqli->query($query);
      if ($mysqli->affected_rows){
        $row = $result->fetch_assoc();
        $res[$i] = $row;
        $id = $row['p_id'];
        $i++;
      }else {
        break;
      }
    }
    return $res;
  }

  function CheckIfSubjectHasItems($table_begin, $id){
		global $mysqli;
		$id = intval($id);
		$table_begin = $mysqli->smart_escape($table_begin);
  	$table=$table_begin."_item_id";
  	$subject=$table_begin."_subject_id";
  	$query="SELECT id
  	FROM `$table`
  	WHERE $subject='$id'
  	LIMIT 1";
  	return $mysqli->query_and_return_true_false($query);
  }

  function ShowSettingBodyByAlias($alias, $lang_id){
		global $mysqli;
  	$alias = $mysqli->smart_escape($alias);
  	$lang_id = intval($lang_id);
		$query="SELECT settings.body
		FROM settings_id
		LEFT JOIN settings ON(settings.settings_id=settings_id.id)
		WHERE settings_id.alias='$alias' AND settings.lang_id='$lang_id'
		LIMIT 1";
		$row = $mysqli->getone($query);
		return $row['body'];
	}

	function ShowLabelById($label_id, $lang_id){
		global $mysqli;
		$label_id = intval($label_id);
		$lang_id = intval($lang_id);
		$query="SELECT name
		FROM labels
		WHERE labels_id='$label_id' AND lang_id='$lang_id'
		LIMIT 1";
    $row = $mysqli->getone($query);
    return $row['name'];;
	}

/**
 * Меняет позиции
 *
 * @param таблица_где_менять $table
 * @param текущий_id $curr_id
 * @param id_с_которым_надо_менять $change_id
 * @param просто_AND $and
 */
	function ChangePosition($table, $curr_id, $change_id, $and=null){
		global $mysqli;
		$curr_id = intval($curr_id);
		$change_id = intval($change_id);
		$table = $mysqli->smart_escape($table);
		$curr_position=GetPosition($curr_id, $table, $and);
		$change_position=GetPosition($change_id, $table, $and);
		//Апдейтим текущий id и ставим ему позицию от change_id
		$curr_id = intval($curr_id);
		$change_id = intval($change_id);
		$query="UPDATE `$table`
		SET position='$change_position'
		WHERE id='$curr_id'";
		$mysqli->query($query);

		//Апдейтим change_id и ставим ему позицию от curr_id
		$query="UPDATE `$table`
		SET position='$curr_position'
		WHERE id='$change_id'";
		return $mysqli->query($query);
	}

/**
 * Меняет активность
 *
 * @param таблица_где_менять $table
 */
	function ChangeActive($table, $id, $active){
		global $mysqli;
		$id = intval($id);
		$active = intval($active);
		$query="UPDATE `$table`
		SET active='$active'
		WHERE `$table`.id='{$id}'
		LIMIT 1";
		return $mysqli->query($query);
	}

/**
 * Смотрим если есть запись на данном языке
 *
 * @param unknown_type $table - название таблицы
 * @param unknown_type $id_name - название поля
 * @param unknown_type $id
 * @param unknown_type $lang_id
 * @return unknown
 */
  function CheckIfExistsLangInTable($table, $id_name, $id, $lang_id){
  	global $mysqli;
  	$subject_id = intval($subject_id);
  	if (!is_null($lang_id)){
  		$lang_id=" AND lang_id='".intval($lang_id)."'";
  	}
  	$query="SELECT id
  	FROM `$table`
  	WHERE `$id_name`='{$id}' $lang_id
  	LIMIT 1";
  	return $mysqli->query_and_return_true_false($query);
  }



	function DelToRec($table, $id){
  	global $mysqli;
		$id = intval($id);
		$query = "UPDATE `$table`
		SET deleted=1, active=0
		WHERE `$table`.id='{$id}'
		LIMIT 1";
		return $mysqli->query($query);
	}

	function RestoreFromRec($table, $id){
  	global $mysqli;
		$id = intval($id);
		$query = "UPDATE `$table`
		SET deleted=0, active=0
		WHERE `$table`.id='{$id}'
		LIMIT 1";
		return $mysqli->query($query);
	}

	function DelFromRec($table, $id){
  	global $mysqli;
		$id = intval($id);
		$query = "DELETE FROM `$table`
		WHERE `$table`.id='{$id}'
		LIMIT 1";
		return $mysqli->query($query);
	}

/**
 * Меняем position
 * на входе получаем строку с позициями и айдишками и меняем)
 *
 */
	function Reorder($table, $neworder, $other_name=""){
		global $mysqli;
		$table = $mysqli->smart_escape($table);
    $i=0;
    $neworder = array_reverse(explode("&", $neworder));
    foreach ($neworder as $k=>$v) {
    	if($v != "tablelistsorter{$other_name}[]="){
    	  $query="UPDATE `$table`
    	  SET position='$i'
    	  WHERE id='".str_replace("tablelistsorter{$other_name}[]=","", $v)."'";
    	  $mysqli->query($query);
    	  $i++;
    	}
    }
	}

	function CheckIFAliasIsUnique($table, $alias, $noid=null){
		global $mysqli;
		$table = $mysqli->smart_escape($table);
		$alias = $mysqli->smart_escape($alias);
		if (!is_null($noid)){
			$noid=" AND id<>'".intval($noid)."'";
		}else {
			$noid="";
		}
		$query="SELECT alias
		FROM `{$table}`
		WHERE alias='$alias' $noid
		LIMIT 1";
		return $mysqli->query_and_return_true_false($query);
	}

  function substrBySpace($str, $length){
    if (mb_strlen($str)>$length){
      $str = mb_substr($str, 0, $length);
      $str_space = mb_substr($str, 0 ,mb_strrpos($str, " "));
      return $str_space?$str_space:$str;
    }else {
    	return $str;
    }
  }

	/**
	 * Сортируем многомерный массив по какому-то значению
	 *
	 * @param unknown_type multisort($array[, $key, $order, $type]...)
	 * Пример вызова: multisort($a, "'name'", true, 0, "'id'", false, 2); - эквивалентен вызову:
	 * 'ORDER BY id DESC, name ASC'
	 * @return unknown
	 */

  function multisort($array)
  {
      for($i = 1; $i < func_num_args(); $i += 3)
      {
          $key = func_get_arg($i);

          $order = true;
          if($i + 1 < func_num_args())
              $order = func_get_arg($i + 1);

          $type = 0;
          if($i + 2 < func_num_args())
              $type = func_get_arg($i + 2);

          switch($type)
          {
              case 1: // Case insensitive natural.
                  $t = 'strcasenatcmp($a[' . $key . '], $b[' . $key . '])';
                  break;
              case 2: // Numeric.
                  $t = '$a[' . $key . '] - $b[' . $key . ']';
                  break;
              case 3: // Case sensitive string.
                  $t = 'strcmp($a[' . $key . '], $b[' . $key . '])';
                  break;
              case 4: // Case insensitive string.
                  $t = 'strcasecmp($a[' . $key . '], $b[' . $key . '])';
                  break;
              default: // Case sensitive natural.
                  $t = 'strnatcmp($a[' . $key . '], $b[' . $key . '])';
                  break;
          }

          uasort($array, create_function('$a, $b', 'return ' . ($order ? '' : '-') . '(' . $t . ');'));
      }

      return $array;
  }

/**
 * транслитерация - преобразует русские и рум символы в англ. все остальные верезает.
*/
function transliterat($str){

	$tbl= array(
		'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ж'=>'g', 'з'=>'z',
		'и'=>'i', 'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p',
		'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'ы'=>'i', 'э'=>'e', 'А'=>'A',
		'Б'=>'B', 'В'=>'V', 'Г'=>'G', 'Д'=>'D', 'Е'=>'E', 'Ж'=>'G', 'З'=>'Z', 'И'=>'I',
		'Й'=>'Y', 'К'=>'K', 'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O', 'П'=>'P', 'Р'=>'R',
		'С'=>'S', 'Т'=>'T', 'У'=>'U', 'Ф'=>'F', 'Ы'=>'I', 'Э'=>'E', 'ё'=>"yo", 'х'=>"h",
		'ц'=>"ts", 'ч'=>"ch", 'ш'=>"sh", 'щ'=>"shch", 'ъ'=>"", 'ь'=>"", 'ю'=>"yu", 'я'=>"ya",
		'Ё'=>"YO", 'Х'=>"H", 'Ц'=>"TS", 'Ч'=>"CH", 'Ш'=>"SH", 'Щ'=>"SHCH", 'Ъ'=>"", 'Ь'=>"",
		'Ю'=>"YU", 'Я'=>"YA", "ă"=>"a", "î"=>"i", "ş"=>"s", "ţ"=>"t", "Ă"=>"A", "Î"=>"I", "Ş"=>"S", "Ţ"=>"T"
	);
  $ret = strtr($str, $tbl);
  $ret = preg_replace("/\s/", "_", $ret);//меняем пробелы на _
  $ret = preg_replace("/[^a-zA-Z0-9_\.]/", "", $ret);//убираем все не англ символы
  return $ret;
}

/**
 * та же транслитерация, но более полный набор символов и пробелы меняет на -
 *
 * @param unknown_type $str
 * @return unknown
 */
function transliterate_with_spaces($str){
	$tbl= array(
		'Ą'=>'a', 'ą'=>'a', 'Ć'=>'c', 'ć'=>'c', 'Č'=>'c', 'č'=>'c', 'Ď'=>'d', 'ď'=>'d', 'Ē'=>'e', 'ē'=>'e', 'Ĕ'=>'e', 'ĕ'=>'e', 'Ę'=>'e', 'ę'=>'e', 'Ě'=>'e', 'ě'=>'e', 'Ģ'=>'g', 'ģ'=>'g', 'Ī'=>'i', 'ī'=>'i', 'Ķ'=>'k', 'ķ'=>'k', 'Ĺ'=>'l', 'ĺ'=>'l', 'Ļ'=>'l', 'ļ'=>'l', 'Ľ'=>'l', 'ľ'=>'l', 'Ł'=>'l', 'ł'=>'l', 'Ń'=>'n', 'ń'=>'n', 'Ņ'=>'n', 'ņ'=>'n', 'Ň'=>'n', 'ň'=>'n', 'Ŕ'=>'r', 'ŕ'=>'r', 'Ř'=>'r', 'ř'=>'r', 'Ś'=>'s', 'ś'=>'s', 'Š'=>'s', 'š'=>'s', 'Ť'=>'t', 'ť'=>'t', 'Ū'=>'u', 'ū'=>'u', 'Ů'=>'u', 'ů'=>'u', 'Ź'=>'z', 'ź'=>'z', 'Ż'=>'z', 'ż'=>'z', 'Ž'=>'z', 'ž'=>'z', 'Š'=>'s', 'Ž'=>'z', 'š'=>'s', 'ž'=>'z', 'Ÿ'=>'y', 'À'=>'a', 'Á'=>'a', 'Â'=>'a', 'Ã'=>'a', 'Ä'=>'a', 'Å'=>'a', 'Æ'=>'a', 'È'=>'e', 'É'=>'e', 'Ê'=>'e', 'Ë'=>'e', 'Ì'=>'i', 'Í'=>'i', 'Î'=>'i', 'Ï'=>'i', 'Ò'=>'o', 'Ó'=>'o', 'Ô'=>'o', 'Õ'=>'o', 'Ö'=>'o', 'Ő'=>'o', 'Ù'=>'u', 'Ú'=>'u', 'Û'=>'u', 'Ü'=>'u', 'Ű'=>'u', 'Ý'=>'y', 'ß'=>'ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'å'=>'a', 'ã'=>'a', 'ä'=>'a', 'æ'=>'a', 'Ç'=>'c', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ő'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ű'=>'u', 'ý'=>'y', 'ÿ'=>'y', 'Ā'=>'a', 'ā'=>'a', 'ß'=>'ss', 'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ё'=>'yo', 'ж'=>'zh', 'з'=>'z', 'и'=>'i', 'й'=>'j', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'х'=>'h', 'ц'=>'c', 'ч'=>'ch', 'ш'=>'sh', 'щ'=>'sch', 'ы'=>'y', 'э'=>'e', 'ю'=>'yu', 'я'=>'ya', 'А'=>'a', 'Б'=>'b', 'В'=>'v', 'Г'=>'g', 'Д'=>'d', 'Е'=>'e', 'Ё'=>'yo', 'Ж'=>'zh', 'З'=>'z', 'И'=>'i', 'Й'=>'j', 'К'=>'k', 'Л'=>'l', 'М'=>'m', 'Н'=>'n', 'О'=>'o', 'П'=>'p', 'Р'=>'r', 'С'=>'s', 'Т'=>'t', 'У'=>'u', 'Ф'=>'f', 'Х'=>'h', 'Ц'=>'c', 'Ч'=>'ch', 'Ш'=>'sh', 'Щ'=>'sch', 'Ы'=>'y', 'Э'=>'e', 'Ю'=>'yu', 'Я'=>'ya', '¢'=>'cent', '£'=>'pound', '¥'=>'yen', '°'=>'degree', '₤'=>'lira', 'ϋ'=>'ou', 'ΰ'=>'ou', 'α'=>'a', 'β'=>'b', 'γ'=>'g', 'δ'=>'d', 'ε'=>'e', 'ζ'=>'z', 'η'=>'i', 'θ'=>'th', 'ι'=>'i', 'κ'=>'k', 'λ'=>'l', 'μ'=>'m', 'ν'=>'n', 'ξ'=>'ks', 'ο'=>'o', 'π'=>'p', 'ρ'=>'r', 'σ'=>'s', 'τ'=>'t', 'υ'=>'i', 'φ'=>'f', 'χ'=>'x', 'ψ'=>'ps', 'ω'=>'o', 'ά'=>'a', 'έ'=>'e', 'ή'=>'i', 'ί'=>'i', 'ό'=>'o', 'ύ'=>'i', 'ώ'=>'o', 'Ϊ'=>'i', 'Ϋ'=>'i', 'Ου'=>'ou', 'Ού'=>'ou', 'Α'=>'a', 'Β'=>'b', 'Γ'=>'g', 'Δ'=>'d', 'Ε'=>'e', 'Ζ'=>'z', 'Η'=>'i', 'Θ'=>'th', 'Ι'=>'i', 'Κ'=>'k', 'Λ'=>'l', 'Μ'=>'m', 'Ν'=>'n', 'Ξ'=>'ks', 'Ο'=>'o', 'Π'=>'p', 'Ρ'=>'r', 'Σ'=>'s', 'Τ'=>'t', 'Υ'=>'i', 'Φ'=>'f', 'Χ'=>'x', 'Ψ'=>'ps', 'Ω'=>'o', 'Ά'=>'a', 'Έ'=>'e', 'Ή'=>'i', 'Ί'=>'i', 'Ό'=>'o', 'Ύ'=>'i', 'Ώ'=>'o', 'ς'=>'s', 'ϊ'=>'i', 'ΐ'=>'i'
	);
  $ret = strtr($str, $tbl);
  $ret = preg_replace("/\s/", "-", $ret);//меняем пробелы на -
  $ret = preg_replace("/[^A-Za-z0-9-_\s]+/", "", $ret);//убираем все не англ символы
  return $ret;
}

function check_file_existence($link_to_file){
	$headers = @get_headers($link_to_file);
	if(strpos('200', $headers[0])) {
		return true;
	}
}

function my_ucfirst($string, $e ='utf-8') {
  if (function_exists('mb_strtoupper') && function_exists('mb_substr') && !empty($string)) {
  	$string = mb_strtolower($string, $e);
    $upper = mb_strtoupper($string, $e);
    preg_match('#(.)#us', $upper, $matches);
    $string = $matches[1] . mb_substr($string, 1, mb_strlen($string, $e), $e);
  }else {
     $string = ucfirst($string);
  }
  return $string;
}

function get_user_ip(){
  if ( getenv('REMOTE_ADDR') ) $user_ip = getenv('REMOTE_ADDR');
  elseif ( getenv('HTTP_FORWARDED_FOR') ) $user_ip = getenv('HTTP_FORWARDED_FOR');
  elseif ( getenv('HTTP_X_FORWARDED_FOR') ) $user_ip = getenv('HTTP_X_FORWARDED_FOR');
  elseif ( getenv('HTTP_X_COMING_FROM') ) $user_ip = getenv('HTTP_X_COMING_FROM');
  elseif ( getenv('HTTP_VIA') ) $user_ip = getenv('HTTP_VIA');
  elseif ( getenv('HTTP_XROXY_CONNECTION') ) $user_ip = getenv('HTTP_XROXY_CONNECTION');
  elseif ( getenv('HTTP_CLIENT_IP') ) $user_ip = getenv('HTTP_CLIENT_IP');
  $user_ip = trim($user_ip);
  if ( empty($user_ip) ) return false;
  if ( !preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $user_ip) ) return false;
  return $user_ip;
}

function checkurl($url){
  if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url)) return true;
}

function in_multiarray($elem, $array){
  foreach ($array as $k => $v){
    if ($v==$elem){
        return true;
    }
    elseif(is_array($v)){
      if(in_multiarray($elem, $v))
          return true;
    }
  }

  return false;
}

function reCaptcha($captcha)
{
    $secretKey = '6LdGxz0UAAAAAAx1bojrQ6xquzKYqWmSqTLVddqI';

    $ip = $_REQUEST['ip'];
    $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secretKey."&response=".$captcha."&remoteip=".$ip);
    $responseKeys = json_decode($response,true);

    if(intval($responseKeys["success"]) !== 1)
        return false;
    else
        return true;
}

?>