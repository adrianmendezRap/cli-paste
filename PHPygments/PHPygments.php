<?php
class PHPygments {
  static function render($code, $language, $style = "default", $linenumbers = FALSE) {
    $linenumbers = ($linenumbers !== FALSE) ? $linenumbers : "False";
    $pygments_bind_app = "python " . dirname(__FILE__) . "/bind.py";
    $temp_name = tempnam("/tmp", "pygmentize_");
    $file_handle = fopen($temp_name, "w");
    fwrite($file_handle, $code);
    fclose($file_handle);
    chmod($temp_name, 0777);
    $pygments_bind_params = array(
      "--sourcefile" => $temp_name,
      "--style" => $style,
      "--lang" => $language,
      "--linenumbers" => $linenumbers
    );
    $params = " ";
    foreach ($pygments_bind_params as $k => $v) {
      $params .= $k . "=" . $v . " ";
    }
    $command = $pygments_bind_app . " " . rtrim($params);
    $output = array();
    $retval = -1;
    exec($command, $output, $retval);
    unlink($temp_name);
    return array(
      "code" => utf8_decode(implode("\n", $output)),
      "styles" => "styles/" . $style . ".css"
    );
  }
}
?>