<?php
/*
    p2 - スレッド表示スクリプト - 新着まとめ読み（携帯）
    フレーム分割画面、右下部分
*/

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';

require_once P2_LIB_DIR . '/threadlist.class.php';
require_once P2_LIB_DIR . '/thread.class.php';
require_once P2_LIB_DIR . '/threadread.class.php';
require_once P2_LIB_DIR . '/ngabornctl.class.php';
require_once P2_LIB_DIR . '/read_new.inc.php';

$_login->authorize(); // ユーザ認証

// まとめよみのキャッシュ読み
if (!empty($_GET['cview'])) {
    $cnum = isset($_GET['cnum']) ? intval($_GET['cnum']) : NULL;
    if ($cont = getMatomeCache($cnum)) {
        echo $cont;
        exit;
    } else {
        p2die('新着まとめ読みのキャッシュがないよ');
    }
}

//==================================================================
// 変数
//==================================================================
$GLOBALS['rnum_all_range'] = $_conf['k_rnum_range'];

$sb_view = "shinchaku";
$newtime = date("gis");

isset($_GET['host'])    and $host   = $_GET['host'];
isset($_POST['host'])   and $host   = $_POST['host'];
isset($_GET['bbs'])     and $bbs    = $_GET['bbs'];
isset($_POST['bbs'])    and $bbs    = $_POST['bbs'];
isset($_GET['spmode'])  and $spmode = $_GET['spmode'];
isset($_POST['spmode']) and $spmode = $_POST['spmode'];

if ((empty($host) || !isset($bbs)) && !isset($spmode)) {
    p2die('必要な引数が指定されていません');
}


//====================================================================
// メイン
//====================================================================

register_shutdown_function('saveMatomeCache');

$GLOBALS['read_new_html'] = '';
ob_start();

$aThreadList =& new ThreadList();

// 板とモードのセット
if ($spmode) {
    if ($spmode == "taborn" or $spmode == "soko") {
        $aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));
    }
    $aThreadList->setSpMode($spmode);
} else {
    $aThreadList->setIta($host, $bbs, P2Util::getItaName($host, $bbs));

    // スレッドあぼーんリスト読込
    $idx_host_dir = P2Util::idxDirOfHost($host);
    $taborn_file = $idx_host_dir.'/'.$bbs.'/p2_threads_aborn.idx';
    
    if ($tabornlines = @file($taborn_file)) {
        $ta_num = sizeOf($tabornlines);
        foreach ($tabornlines as $l) {
            $tarray = explode('<>', rtrim($l));
            $ta_keys[ $tarray[1] ] = true;
        }
    }
}

// ソースリスト読込
$lines = $aThreadList->readList();

// ページヘッダ表示 ===================================
$ptitle_hs = htmlspecialchars($aThreadList->ptitle, ENT_QUOTES);
$ptitle_ht = "{$ptitle_hs} の 新着まとめ読み";

// &amp;sb_view={$sb_view}
if ($aThreadList->spmode) {
    $sb_ht = <<<EOP
        <a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}{$_conf['k_at_a']}">{$ptitle_hs}</a>
EOP;
    $sb_ht_btm = <<<EOP
        <a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}{$_conf['k_at_a']}">{$ptitle_hs}</a>
EOP;
} else {
    $sb_ht = <<<EOP
        <a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}{$_conf['k_at_a']}">{$ptitle_hs}</a>
EOP;
    $sb_ht_btm = <<<EOP
        <a href="{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}{$_conf['k_at_a']}">{$ptitle_hs}</a>
EOP;
}

$body_at = '';
if (!empty($STYLE['read_k_bgcolor'])) {
    $body_at .= " bgcolor=\"{$STYLE['read_k_bgcolor']}\"";
}
if (!empty($STYLE['read_k_color'])) {
    $body_at .= " text=\"{$STYLE['read_k_color']}\"";
}

// ========================================================
// require_once P2_LIB_DIR . '/read_header.inc.php';

echo $_conf['doctype'];
echo <<<EOHEADER
<html>
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$ptitle_ht}</title>\n
EOHEADER;

/*
    <script type="text/javascript" src="js/basic.js?v=20061209"></script>
    <script type="text/javascript" src="js/respopup.js?v=20061206"></script>
    <script type="text/javascript" src="js/setfavjs.js?v=20061206"></script>
    <script type="text/javascript" src="js/delelog.js?v=20061206"></script>
*/

echo <<<EOHEADER
    <script type="text/javascript" src="js/basic.js?v=20061209"></script>
	<script type="text/javascript" src="iphone/js/respopup.iPhone.js?v=20061206"></script>
	<script type="text/javascript" src="iphone/js/setfavjs.js?v=20061206"></script>
	<script type="text/javascript" src="js/post_form.js?v=20061209"></script>
<script type="text/javascript" src="js/smartpopup.js?v=20070308"></script>
    <script type="text/javascript"> 
	<!-- 
		// iPhoneのURL編集部分を表示しないようスクロールする
		window.onload = function() { 
		setTimeout(scrollTo, 100, 0, 1); 
		}
		
		// ページ読み込み完了時コールバック関数
		gIsPageLoaded = false;
	    addLoadEvent(function() {			// basic.jsのメソッド
	        gIsPageLoaded = true;			// ページロード完了フラグ(trueじゃないとお気に入り変更javascriptが動かない)
	        {$onload_script}				// ページ読み込み完了時に実行するスクリプト群
	        
	    });
	    
	    // レス範囲のフォームの内容をリセットしてからページ移行するメソッド
	    var onArreyt = 2;
	    function formReset() {
		    var uriValue = "{$_conf['read_php']}?"
		    			 + "offline=1&"
		    			 + "b=k&"
		    			 + "host=" + document.frmresrange.host.value + "&"
		    			 + "bbs=" + document.frmresrange.bbs.value + "&"
		    			 + "key=" + document.frmresrange.key.value + "&"
		    			 + "rescount=" + document.frmresrange.rescount.value + "&"
		    			 + "ttitle_en=" + document.frmresrange.ttitle_en.value + "&"
		    			 + "ls=" + document.frmresrange.ls.value + "&";
		    document.frmresrange.reset();
		    window.location.assign(uriValue);
		}
		// フッターのレスフィルター表示フォームのポップアップを表示するメソッド
		// Edit 080727 by 240
		function footbarFormPopUp(arrayNum, resetFlag) {
			var formStyles = new Array(2);
			var liElement = new Array(2);
			formStyles[0] = document.getElementById('searchForm').style;
			formStyles[1] = document.getElementById('writeForm').style;
			liElement[0]  = document.getElementById('serchId');
			liElement[1]  = document.getElementById('writeId');

			for (var i = 0; i < 2; i++) {
				if (i != arrayNum)
					liElement[i].setAttribute('title', 'off');
				liElement[i].style.backgroundPositionY = '0';
				formStyles[i].display = 'none';
			}
			if (liElement[arrayNum].getAttribute('title') == 'on' || resetFlag) {
				liElement[arrayNum].setAttribute('title', 'off');
				return;
			}

			liElement[arrayNum].setAttribute('title', 'on');
			liElement[arrayNum].style.backgroundPositionY = '-50px';
//			formStyles[arrayNum].top = (document.height - 480).toString(); + "px"
			formStyles[arrayNum].display = 'block';
		}
		

	// --> 
	</script> 
<link rel="stylesheet" type="text/css" href="./iui/read.css"> 
EOHEADER;

echo <<<EOP
</head>
<body{$body_at}>\n
EOP;

echo <<<EOP
<div class="toolbar">
    <h1>{$sb_ht}の新まとめ</h1>
</div>
EOP;
P2Util::printInfoHtml();

//==============================================================
// それぞれの行解析
//==============================================================

$linesize = sizeof($lines);

for ($x = 0; $x < $linesize; $x++) {

    if (isset($GLOBALS['rnum_all_range']) and $GLOBALS['rnum_all_range'] <= 0) {
        break;
    }

    $l = $lines[$x];
    $aThread =& new ThreadRead();
    
    $aThread->torder = $x + 1;

    // データ読み込み
    if ($aThreadList->spmode) {
        switch ($aThreadList->spmode) {
        case "recent":    // 履歴
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case "res_hist":    // 書き込み履歴
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case "fav":    // お気に
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        case "taborn":    // スレッドあぼーん
            $aThread->getThreadInfoFromExtIdxLine($l);
            $aThread->host = $aThreadList->host;
            $aThread->bbs = $aThreadList->bbs;
            break;
        case "palace":    // 殿堂入り
            $aThread->getThreadInfoFromExtIdxLine($l);
            break;
        }
    // subject (not spmode)
    } else {
        $aThread->getThreadInfoFromSubjectTxtLine($l);
        $aThread->host = $aThreadList->host;
        $aThread->bbs = $aThreadList->bbs;
    }
    
    // hostもbbsも不明ならスキップ
    if (!($aThread->host && $aThread->bbs)) {
        unset($aThread);
        continue;
    }
    
    $aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);
    $aThread->getThreadInfoFromIdx(); // 既得スレッドデータをidxから取得

    // 新着のみ(for subject) =========================================
    if (!$aThreadList->spmode and $sb_view == "shinchaku" and !$_GET['word']) { 
        if ($aThread->unum < 1) {
            unset($aThread);
            continue;
        }
    }

    // スレッドあぼーんチェック =====================================
    if ($aThreadList->spmode != "taborn" and $ta_keys[$aThread->key]) { 
        unset($ta_keys[$aThread->key]);
        continue; // あぼーんスレはスキップ
    }

    // spmode(殿堂入りを除く)なら ====================================
    if ($aThreadList->spmode && $sb_view != "edit") { 
        
        // subject.txtが未DLなら落としてデータを配列に格納
        if (!$subject_txts["$aThread->host/$aThread->bbs"]) {
        
            require_once P2_LIB_DIR . '/SubjectTxt.class.php';
            $aSubjectTxt =& new SubjectTxt($aThread->host, $aThread->bbs);

            $subject_txts["$aThread->host/$aThread->bbs"] = $aSubjectTxt->subject_lines;
        }
        
        // スレ情報取得 =============================
        if ($subject_txts["$aThread->host/$aThread->bbs"]) {
            foreach ($subject_txts["$aThread->host/$aThread->bbs"] as $l) {
                if (@preg_match("/^{$aThread->key}/", $l)) {
                    $aThread->getThreadInfoFromSubjectTxtLine($l); // subject.txt からスレ情報取得
                    break;
                }
            }
        }
        
        // 新着のみ(for spmode) ===============================
        if ($sb_view == "shinchaku" and !$_GET['word']) {
            if ($aThread->unum < 1) {
                unset($aThread);
                continue;
            }
        }
    }
    
    if ($aThread->isonline) { $online_num++; } // 生存数set
    
    P2Util::printInfoHtml();
    
    $GLOBALS['read_new_html'] .= ob_get_flush();
    ob_start();
        
    if (($aThread->readnum < 1) || $aThread->unum) {
        readNew($aThread);
    } elseif ($aThread->diedat) {
        echo $aThread->getdat_error_msg_ht;
        //echo "<hr>\n";
    }
    
    $GLOBALS['read_new_html'] .= ob_get_flush();
    ob_start();
    
    // リストに追加
    // $aThreadList->addThread($aThread);
    $aThreadList->num++;
    unset($aThread);
}

//$aThread =& new ThreadRead();

/**
 * スレッドの新着部分を読み込んで表示する
 */
function readNew(&$aThread)
{
    global $_conf, $_newthre_num, $STYLE;
    global $spmode;

    $_newthre_num++;
    
    //==========================================================
    // idxの読み込み
    //==========================================================
    
    //hostを分解してidxファイルのパスを求める
    $aThread->setThreadPathInfo($aThread->host, $aThread->bbs, $aThread->key);
    
    //FileCtl::mkdirFor($aThread->keyidx); //板ディレクトリが無ければ作る //この操作はおそらく不要

    $aThread->itaj = P2Util::getItaName($aThread->host, $aThread->bbs);
    if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }

    // idxファイルがあれば読み込む
    if (is_readable($aThread->keyidx)) {
        $lines = file($aThread->keyidx);
        $data = explode('<>', rtrim($lines[0]));
    }
    $aThread->getThreadInfoFromIdx();
    

    // DATのダウンロード
    if (!($word and file_exists($aThread->keydat))) {
        $aThread->downloadDat();
    }
    
    // DATを読み込み
    $aThread->readDat();
    $aThread->setTitleFromLocal(); // ローカルからタイトルを取得して設定
    
    //===========================================================
    // 表示レス番の範囲を設定
    //===========================================================
    // 取得済みなら
    if ($aThread->isKitoku()) {
        $from_num = $aThread->readnum +1 - $_conf['respointer'] - $_conf['before_respointer_new'];
        if ($from_num > $aThread->rescount) {
            $from_num = $aThread->rescount - $_conf['respointer'] - $_conf['before_respointer_new'];
        }
        if ($from_num < 1) {
            $from_num = 1;
        }

        //if (!$aThread->ls) {
            $aThread->ls = "$from_num-";
        //}
    }
    
    $aThread->lsToPoint();
    
    //==================================================================
    // ヘッダ 表示
    //==================================================================
    $motothre_url = $aThread->getMotoThread();
    
    $ttitle_en = base64_encode($aThread->ttitle);
    $ttitle_en_q = "&amp;ttitle_en=".$ttitle_en;
    $bbs_q = "&amp;bbs=".$aThread->bbs;
    $key_q = "&amp;key=".$aThread->key;
    $popup_q = "&amp;popup=1";
    
    // require_once P2_LIB_DIR . '/read_header.inc.php';
    
    $prev_thre_num = $_newthre_num - 1;
    $next_thre_num = $_newthre_num + 1;
    if ($prev_thre_num != 0) {
        $prev_thre_ht = "<a href=\"#ntt{$prev_thre_num}\">▲</a>";
    }
    //$next_thre_ht = "<a href=\"#ntt{$next_thre_num}\">▼</a> ";
    $next_thre_ht = "<a class=\"button\" href=\"#ntt_bt{$_newthre_num}\">▼</a> ";
    
    $itaj_hs = htmlspecialchars($aThread->itaj, ENT_QUOTES);
    
    if ($spmode) {
        $read_header_itaj_ht = " ({$itaj_hs})";
    }
    
    P2Util::printInfoHtml();
    
    $read_header_ht = <<<EOP
        <p id="ntt{$_newthre_num}" name="ntt{$_newthre_num}"><font color="{$STYLE['read_k_thread_title_color']}"><b>{$aThread->ttitle_hd}</b></font>{$read_header_itaj_ht} {$next_thre_ht}</p>\n
EOP;

    //==================================================================
    // ローカルDatを読み込んでHTML表示
    //==================================================================
    $aThread->resrange['nofirst'] = true;
    $GLOBALS['newres_to_show_flag'] = false;
    if ($aThread->rescount) {
        //$aThread->datToHtml(); // dat を html に変換表示
        require_once P2_LIB_DIR . '/showthread.class.php';
        require_once P2_IPHONE_LIB_DIR . '/showthreadk.class.php';
        $aShowThread =& new ShowThreadK($aThread);
        
        $read_cont_ht .= $aShowThread->getDatToHtml();
        
        unset($aShowThread);
    }
    
    //==================================================================
    // フッタ 表示
    //==================================================================
    //include $read_footer_inc;
    
    //----------------------------------------------
    // $read_footer_navi_new  続きを読む 新着レスの表示
    $newtime = date("gis");  // リンクをクリックしても再読込しない仕様に対抗するダミークエリー
    
    $info_st = "情";
    $delete_st = "削";
    $prev_st = "前";
    $next_st = "次";

    // 表示範囲
    if ($aThread->resrange['start'] == $aThread->resrange['to']) {
        $read_range_on = $aThread->resrange['start'];
    } else {
        $read_range_on = "{$aThread->resrange['start']}-{$aThread->resrange['to']}";
    }
    $read_range_ht = "{$read_range_on}/{$aThread->rescount}<br>";

    $read_footer_navi_new = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->rescount}-&amp;nt={$newtime}{$_conf['k_at_a']}#r{$aThread->rescount}\">新着ﾚｽの表示</a>";
    
    if (!empty($_conf['disable_res'])) {
        $dores_ht = <<<EOP
          <a href="{$motothre_url}" target="_blank">ﾚｽ</a>
EOP;
    } else {
        $dores_ht = <<<EOP
        <a href="post_form.php_i?host={$aThread->host}{$bbs_q}{$key_q}&amp;rescount={$aThread->rescount}{$ttitle_en_q}{$_conf['k_at_a']}">ﾚｽ</a>
EOP;
    }
    
    // ツールバー部分HTML =======
    if ($spmode) {
        $toolbar_itaj_ht = <<<EOP
(<a href="{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$_conf['k_at_a']}">{$itaj_hs}</a>)
EOP;
    }
    $toolbar_right_ht .= <<<EOTOOLBAR
            <a href="info.php_i?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$_conf['k_at_a']}">{$info_st}</a> 
            <a href="info.php_i?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}&amp;dele=true{$_conf['k_at_a']}">{$delete_st}</a> 
            <a href="{$motothre_url}">元ｽﾚ</a>\n
EOTOOLBAR;

    $read_footer_ht = <<<EOP
            <a class="button" id="backbutton"href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;offline=1&amp;rescount={$aThread->rescount}{$_conf['k_at_a']}#r{$aThread->rescount}">{$aThread->ttitle_hd}</a>
EOP;

    // 透明あぼーんや表示数制限で新しいレス表示がない場合はスキップ
    if ($GLOBALS['newres_to_show_flag']) {
        echo $read_header_ht;
        echo $read_cont_ht;
        echo $read_footer_ht;
    }

    //==================================================================
    // key.idxの値設定
    //==================================================================
    if ($aThread->rescount) {
    
        $aThread->readnum = min($aThread->rescount, max(0, $data[5], $aThread->resrange['to']));
        
        $newline = $aThread->readnum + 1; // $newlineは廃止予定だが、旧互換用に念のため
        
        $sar = array($aThread->ttitle, $aThread->key, $data[2], $aThread->rescount, $aThread->modified,
                    $aThread->readnum, $data[6], $data[7], $data[8], $newline,
                    $data[10], $data[11], $aThread->datochiok);
        P2Util::recKeyIdx($aThread->keyidx, $sar); // key.idxに記録
    }

    unset($aThread);
}

//==================================================================
// ページフッタ表示
//==================================================================
$_newthre_num++;

if (!$aThreadList->num) {
    $GLOBALS['matome_naipo'] = TRUE;
    echo "新着ﾚｽはないぽ";
}
echo <<<EOP
<div id="footbar01">
<div class="footbar">
<ul>
<li class="home"><a name="ntt_bt1" href="iphone.php">TOP</a></li>
<li class="other"><a onclick="all.item('footbar02').style.visibility='visible';">その他</a></li>
EOP;
if (!isset($GLOBALS['rnum_all_range']) or $GLOBALS['rnum_all_range'] > 0 or !empty($GLOBALS['limit_to_eq_to'])) {
    if (!empty($GLOBALS['limit_to_eq_to'])) {
        $str = '新着まとめの更新or続き';
    } else {
        $str = '新まとめを更新';
    }
    echo <<<EOP
    <li class="new">
        <a href="{$_conf['read_new_k_php']}?host={$aThreadList->host}&bbs={$aThreadList->bbs}&spmode={$aThreadList->spmode}&nt={$newtime}{$_conf['k_at_a']}">{$str}</a>
</li>\n
<li id="blank" class="next"></li> 
EOP;
} else {
    echo <<<EOP
    <li id="blank" class="new"></li> 
    <li class="next">
        <a href="{$_conf['read_new_k_php']}?host={$aThreadList->host}&bbs={$aThreadList->bbs}&spmode={$aThreadList->spmode}&nt={$newtime}&amp;norefresh=1{$_conf['k_at_a']}">新まとめの続き</a>
    </li>\n
EOP;
}
//{$sb_ht_btm}の
//echo '<hr>' . $_conf['k_to_index_ht'] . "\n";
//iphone 080801
echo <<<EOP
 </ul>
</div></div>
<div id="footbar02" class="dialog_other">
<filedset>
 <ul>
 <li class="whiteButton">{$sb_ht_btm}</li> 
 <li class="grayButton" onclick="all.item('footbar02').style.visibility='hidden'">キャンセル</li>
 </ul>
 </filedset>
</div>
</body></html>
EOP;
$GLOBALS['read_new_html'] .= ob_get_flush();

// 後処理

// NGあぼーんを記録
NgAbornCtl::saveNgAborns();
