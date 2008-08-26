<?php
/*
    p2 -  ���j���[ �g�їp
*/

//080825iphone�p���C�u�����ǉ�
require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';
require_once P2_LIB_DIR . '/brdctl.class.php';
require_once P2_IPHONE_LIB_DIR . '/showbrdmenuk.class.php';

$_login->authorize(); // ���[�U�F��

//==============================================================
// �ϐ��ݒ�
//==============================================================
$_conf['ktai'] = 1;
$brd_menus = array();
$GLOBALS['menu_show_ita_num'] = 0;

BrdCtl::parseWord(); // set $GLOBALS['word']

//============================================================
// ����ȑO����
//============================================================
// ���C�ɔ̒ǉ��E�폜
if (isset($_GET['setfavita'])) {
    require_once P2_LIB_DIR . '/setfavita.inc.php';
    setFavIta();
}

//================================================================
// ���C��
//================================================================
$aShowBrdMenuK =& new ShowBrdMenuK;

//============================================================
// �w�b�_HTML��\��
//============================================================

$get['view'] = isset($_GET['view']) ? $_GET['view'] : null;

if ($get['view'] == "favita") {
    $ptitle = "���C�ɔ�";
} elseif ($get['view'] == "cate"){
    $ptitle = "���X�g";
} elseif (isset($_GET['cateid'])) {
    $ptitle = "���X�g";
} else {
    $ptitle = "��޷��p2";
}

echo $_conf['doctype'];
/*
echo <<<EOP
<html>
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">

    <title>{$ptitle}</title>\n
EOP;
*/

echo <<<EEE
<html>
<head>
{$_conf['meta_charset_ht']}
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
<style type="text/css" media="screen">@import "./iui/iui.css";</style>
<script type="text/javascript"> 
<!-- 
window.onload = function() { 
setTimeout(scrollTo, 100, 0, 1); 
} 
// --> 
</script> 
<title>{$ptitle}</title>
</head><body>\n
<div class="toolbar"><h1 id="pageTitle">{$ptitle}</h1></div>
EEE;
P2Util::printInfoHtml();

// ���C�ɔ�HTML�\������
if ($get['view'] == 'favita') {
    $aShowBrdMenuK->printFavItaHtml();
    echo '<p><a id="backButton"class="button" href="iphone.php">TOP</a></p>';

// ����ȊO�Ȃ�brd�ǂݍ���
} else {
    $brd_menus = BrdCtl::readBrdMenus();
}

// �����t�H�[����HTML�\��
if ($get['view'] != 'favita' && $get['view'] != 'rss' && empty($_GET['cateid'])) {
    echo  '<ul><li class="group">����</li></ul>';
    echo '<div id="usage" class="panel"><filedset>';
    echo BrdCtl::getMenuKSearchFormHtml();
    echo '</filedset></div>';
}

//===========================================================
// �������ʂ�HTML�\��
//===========================================================
// {{{ �������[�h�������

if (strlen($GLOBALS['word']) > 0) {

    $word_hs = htmlspecialchars($word, ENT_QUOTES);

    if ($GLOBALS['ita_mikke']['num']) {
$hit_ht = "\"{$word_hs}\" {$GLOBALS['ita_mikke']['num']}hit!";
    }
    echo '<div id="usage" class="panel">';
    echo "<h2>���X�g��������{$hit_ht}</h2>";
    echo '</div>';

    // �����������ĕ\������
    if ($brd_menus) {
        foreach ($brd_menus as $a_brd_menu) {
            $aShowBrdMenuK->printItaSearch($a_brd_menu->categories);
        }
    }

    if (!$GLOBALS['ita_mikke']['num']) {
        P2Util::pushInfoHtml("<p>\"{$word_hs}\"���܂ޔ͌�����܂���ł����B</p>");
    }
    $modori_url_ht = <<<EOP

EOP;
}

// }}}

// �J�e�S����HTML�\��
if ($get['view'] == 'cate' or isset($_REQUEST['word']) && strlen($GLOBALS['word']) == 0) {
    if ($brd_menus) {
        foreach ($brd_menus as $a_brd_menu) {
            $aShowBrdMenuK->printCate($a_brd_menu->categories);
        }
    }
    echo '<p><a id="backButton"class="button" href="iphone.php">TOP</a></p>';
}


// �J�e�S���̔�HTML�\��
if (isset($_GET['cateid'])) {
    if ($brd_menus) {
        foreach ($brd_menus as $a_brd_menu) {
            $aShowBrdMenuK->printIta($a_brd_menu->categories);
        }
    }
    $modori_url_ht = <<<EOP
<div><a id="backButton"class="button" href="menu_i.php?view=cate&amp;nr=1{$_conf['k_at_a']}">���X�g </a></div>
EOP;
}

P2Util::printInfoHtml();

!isset($GLOBALS['list_navi_ht']) and $GLOBALS['list_navi_ht'] = null;
!isset($modori_url_ht) and $modori_url_ht = null;

// �t�b�^��HTML�\��
echo $list_navi_ht;
echo $modori_url_ht;
//echo $_conf['k_to_index_ht'];
//echo '<p><a id="backButton"class="button" href="index.php?b=k">TOP</a></p>';
echo '</body></html>';

