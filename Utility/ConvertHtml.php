<?php
/**
 * Convert_Html Utility
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji Masukawa
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Convert_Html Utility
 *
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @package NetCommons\Mails\Utility
 */
class ConvertHtml {

/**
 * HtmlからText変換処理
 *
 * @param string $str Html文字列
 * @return string Plain Text文字列
 **/
	public function convertHtmlToText($str) {
		$patterns = array();
		$replacements = array();
		//\nを削除
		$patterns[] = "/\\n/su";
		$replacements[] = "";

		//brを\n
		$patterns[] = "/<br(.|\s)*?>/u";
		$replacements[] = "\n";

		//divを\n
		$patterns[] = "/<\/div>/u";
		$replacements[] = "</div>\n";

		//pを\n
		$patterns[] = "/<\/p>/u";
		$replacements[] = "</p>\n";

		//blockquoteを\n
		$patterns[] = "/<\/blockquote>/u";
		$replacements[] = "</blockquote>\n";

		//liを\n
		$patterns[] = "/[ ]*<li>/u";
		$replacements[] = "    <li>";

		$patterns[] = "/<\/li>/u";
		$replacements[] = "</li>\n";

		//&npspを空白
		$patterns[] = "/\&nbsp;/u";
		$replacements[] = " ";

		//&quot;を"
		$patterns[] = "/\&quot;/u";
		$replacements[] = "\"";

		//&acute;を´
		$patterns[] = "/\&acute;/u";
		$replacements[] = "´";

		//&cedil;を¸
		$patterns[] = "/\&cedil;/u";
		$replacements[] = "¸";

		//&circ;を?
		$patterns[] = "/\&circ;/u";
		$replacements[] = "?";

		//&lsquo;を‘
		$patterns[] = "/\&lsquo;/u";
		$replacements[] = "‘";

		//&rsquo;を’
		$patterns[] = "/\&rsquo;/u";
		$replacements[] = "’";

		//&ldquo;を“
		$patterns[] = "/\&ldquo;/u";
		$replacements[] = "“";

		//&rdquo;を”
		$patterns[] = "/\&rdquo;/u";
		$replacements[] = "”";

		//&apos;を'
		$patterns[] = "/\&apos;/u";
		$replacements[] = "'";

		//&#039;を'
		$patterns[] = "/\&#039;/u";
		$replacements[] = "'";

		//&amp;を&
		$patterns[] = "/\&amp;/u";
		$replacements[] = "&";

		$str = preg_replace($patterns, $replacements, $str);
		$quoteArr = explode("<blockquote class=\"quote\">", $str);
		$quoteCnt = count($quoteArr);
		if ($quoteCnt > 1) {
			$resultStr = "";
			$indentCnt = 0;
			$count = 0;
			foreach ($quoteArr as $quoteStr) {
				if ($count == 0 || $quoteCnt == $count) {
					$resultStr .= $quoteStr;
					$count++;
					continue;
				}
				$indentCnt++;
				$quoteCloseArr = explode("</blockquote>", $quoteStr);
				$quoteCloseCnt = count($quoteCloseArr);
				if ($quoteCloseCnt > 1) {
					$closeCount = 0;
					foreach ($quoteCloseArr as $quoteCloseStr) {
						//if($closeCount == 0 || $quoteCloseCnt == $closeCount) {
						//						if($quoteCloseCnt == $closeCount+1) {
						//							$resultStr .= $quoteCloseStr;
						//							$closeCount++;
						//							continue;
						//						}
						$indentStr = $this->getIndentStr($indentCnt);
						if ($indentStr != "") {
							$quotePattern = "/\n/u";
							$quoteReplacement = "\n" . $indentStr;
							$resultStr = preg_replace("/(> )+$/u", "", $resultStr);
							if ($quoteCloseCnt != $closeCount + 1) {
								if (!preg_match("/\n$/u", $resultStr)) {
									$resultStr .= "\n";
								}
								$resultStr .= preg_replace("/^(> )+\n/u", "", $indentStr . preg_replace($quotePattern, $quoteReplacement, $quoteCloseStr));
								$indentCnt--;
							} else {
								$resultStr .= preg_replace($quotePattern, $quoteReplacement, $quoteCloseStr);
							}
						} else {
							$resultStr .= $quoteCloseStr;
						}
						$closeCount++;
					}

				} else {
					$indentStr = $this->getIndentStr($indentCnt);
					$quotePattern = "/\n/u";
					$quoteReplacement = "\n" . $indentStr;
					$resultStr .= $indentStr . preg_replace($quotePattern, $quoteReplacement, $quoteStr);
				}
				$count++;
			}
			$str = $resultStr;
		}
		$str = strip_tags($str);

		// strip_tagsで「<」、「>」があるとそれ以降の文字が消えるため、strip_tags後に変換
		$patterns = array();
		$replacements = array();

		//&lt;を<
		$patterns[] = "/\&lt;/u";
		$replacements[] = "<";

		//&gt;を>
		$patterns[] = "/\&gt;/u";
		$replacements[] = ">";

		return preg_replace($patterns, $replacements, $str);
	}

/**
 * getIndentStr
 *
 * @param int $indentCnt インデント
 * @return string
 */
	public function getIndentStr($indentCnt = 0) {
		$indentStr = "";
		$tabStr = "";
		for ($i = 0; $i < $indentCnt; $i++) {
			$indentStr .= "> ";
			$tabStr .= "";
		}
		return $tabStr . $indentStr;
	}

	///**
	// * HtmlからText変換処理
	// *
	// * @param string $str Html文字列
	// * @param bool $convert 変換フラグ
	// * @return string	Plain Text文字列
	// */
	//	public function convertMobileHtml($str, $convert = false) {
	//		$container =& DIContainerFactory::getContainer();
	//		$session =& $container->getComponent("Session");
	//		$mobile_flag = $session->getParameter("_mobile_flag");
	//		if (!isset($mobile_flag)) {
	//			$mobileCheck =& MobileCheck::getInstance();
	//			$mobile_flag = $mobileCheck->isMobile();
	//			$session->setParameter("_mobile_flag", $mobile_flag);
	//		}
	//		if ($mobile_flag == _ON) {
	//			$patterns = array();
	//			$replacements = array();
	//
	//			if ($session->getParameter("_reader_flag") == _OFF) {
	//				// 画像にsession_idを付与
	//				$matches = array();
	//
	//				$pattern = "/(href=|src=)([\"'])?(\\.?\\/?)(\\?)/";
	//				$str = preg_replace_callback($pattern, array($this, "_replaceRelative2Absolute"), $str);
	//
	//				$pattern_url = preg_replace("/\//", "\\\/", preg_quote(BASE_URL));
	//				$pattern = "/(href=|src=)([\"'])?(".$pattern_url.")([^\\/]*?)?([^ \"'>]*)?([ \"'>])?/";
	//				$str = preg_replace_callback($pattern, array($this, "_replaceSesion"), $str);
	//			}
	//
	//			//「 />」「/>」を「>」
	//			$patterns[] = "/( )?\/>/ui";
	//			$replacements[] = ">";
	//
	//			$str = preg_replace($patterns, $replacements, $str);
	//			if ($convert) {
	//				//mb_stringがロードされているかどうか
	//		    	if (!extension_loaded('mbstring') && !function_exists("mb_convert_encoding")) {
	//	    			include_once MAPLE_DIR  . '/includes/mbstring.php';
	//		    	} else if(function_exists("mb_detect_order")){
	//		    		mb_detect_order(_MB_DETECT_ORDER_VALUE);
	//		    	}
	//		    	$str = mb_convert_encoding($str, "shift_jis", _CHARSET);
	//			}
	//		}
	//		return $str;
	//	}

	///**
	// * HtmlからText変換処理
	// *
	// * @param string $matches Html文字列
	// * @return string	Plain Text文字列
	// **/
	//	public function replaceRelative2Absolute($matches) {
	//		return $matches[1] . $matches[2] . BASE_URL . INDEX_FILE_NAME . $matches[4];
	//	}

/**
 * HtmlからText変換処理
 *
 * @param string $matches Html文字列
 * @return string	Plain Text文字列
 **/
	public function replaceSesion($matches) {
		$sessionValue = session_name() . "=" . session_id();
		if (preg_match("/" . $sessionValue . "/", $matches[5])) {
			return $matches[0];
		}

		$pos = strpos($matches[5], "?");
		$pause = $pos !== false ? "&" : "?";

		$pos = strpos($matches[5], "#");
		$matches[5] = $pos !== false ? substr($matches[5], 0, $pos) . $pause . $sessionValue . substr($matches[5], $pos) : $matches[5] . $pause . $sessionValue;

		return $matches[1] . $matches[2] . $matches[3] . $matches[4] . $matches[5] . $matches[6];
	}
}
