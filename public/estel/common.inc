<?php
  error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
  session_start();
  define("SHOWMYSQLERROR", 1);
  define("PERPAGE", 10);
  define("PAGE", 1);
?>