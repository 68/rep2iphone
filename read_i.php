<?php
/*
    p2 - �X���b�h�\���X�N���v�g
    �t���[��������ʁA�E������
*/

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';

require_once P2_LIB_DIR . '/thread.class.php';
require_once P2_LIB_DIR . '/threadread.class.php';
require_once P2_LIB_DIR . '/filectl.class.php';
require_once P2_LIB_DIR . '/ngabornctl.class.php';
require_once P2_IPHONE_LIB_DIR . '/showthread.class.php';

$_login->authorize(); // ���[�U�F��

//================================================================
// �ϐ�
//================================================================
$newtime = date('gis');  // ���������N���N���b�N���Ă��ēǍ����Ȃ��d�l�ɑ΍R����_�~�[�N�G���[

// {{{ ���ꃊ�N�G�X�g

if ($_conf['ktai'] && isset($_GET['ktool_name']) && isset($_GET['ktool_value'])) {
    $ktv = (int) $_GET['ktool_value'];
    switch ($_GET['ktool_name']) {
        case 'goto':
            $_REQUEST['ls'] = $_GET['ls'] = $ktv . '-' . ($ktv + $_conf['k_rnum_range']);
            break;
        case 'res_quote':
            $_GET['resnum'] = $ktv;
            $_GET['inyou'] = 1;
            require './post_form_i.php';
            exit;
        case 'copy_quote':
            $_GET['inyou'] = 1;
        case 'copy':
            $GLOBALS['_read_copy_resnum'] = $ktv;
            require './read_copy_k.php';
            exit;
        /* 2006/03/06 aki �m�[�}��p2�ł͖��Ή�
        case 'aas_rotate':
            $_GET['rotate'] = 1;
        case 'aas':
            $_GET['resnum'] = $ktv;
            require './aas.php';
            exit;
        */
    }
}

// }}}

// �X���̎w��
list($host, $bbs, $key, $ls) = detectThread();

// {{{ ���X�t�B���^

$res_filter = array();
if (isset($_POST['word']))   { $GLOBALS['word'] = $_POST['word']; }
if (isset($_GET['word']))    { $GLOBALS['word'] = $_GET['word']; }
if (isset($_POST['field']))  { $res_filter['field'] = $_POST['field']; }
if (isset($_GET['field']))   { $res_filter['field'] = $_GET['field']; }
if (isset($_POST['match']))  { $res_filter['match'] = $_POST['match']; }
if (isset($_GET['match']))   { $res_filter['match'] = $_GET['match']; }
if (isset($_POST['method'])) { $res_filter['method'] = $_POST['method']; }
if (isset($_GET['method']))  { $res_filter['method'] = $_GET['method']; }

// $GLOBALS['word'] �Ȃǂ̌�����̎�舵�����ǂ������Ȃ̂́A�ł���ΐ����������Ƃ���...

$GLOBALS['word_fm'] = null;

if (isset($GLOBALS['word']) && strlen($GLOBALS['word']) > 0) {

    // �f�t�H���g�I�v�V����
    if (empty($res_filter['field']))  { $res_filter['field']  = "hole"; }
    if (empty($res_filter['match']))  { $res_filter['match']  = "on"; }
    if (empty($res_filter['method'])) { $res_filter['method'] = "or"; }

    if (!($res_filter['method'] == 'regex' && preg_match('/^\.+$/', $GLOBALS['word']))) {
        require_once P2_LIB_DIR . '/strctl.class.php';
        $GLOBALS['word_fm'] = StrCtl::wordForMatch($word, $res_filter['method']);
        if ($res_filter['method'] != 'just') {
            if (P2_MBREGEX_AVAILABLE == 1) {
                $GLOBALS['words_fm'] = mb_split('\s+', $GLOBALS['word_fm']);
                $GLOBALS['word_fm'] = mb_ereg_replace('\s+', '|', $GLOBALS['word_fm']);
            } else {
                $GLOBALS['words_fm'] = preg_split('/\s+/', $GLOBALS['word_fm']);
                $GLOBALS['word_fm'] = preg_replace('/\s+/', '|', $GLOBALS['word_fm']);
            }
        }
    }
    
    if ($_conf['ktai']) {
        $filter_page = isset($_REQUEST['filter_page']) ? max(1, intval($_REQUEST['filter_page'])) : 1;
        $filter_range = array();
        $filter_range['start'] = ($filter_page - 1) * $_conf['k_rnum_range'] + 1;
        $filter_range['to'] = $filter_range['start'] + $_conf['k_rnum_range'] - 1;
    }

} else {
    $GLOBALS['word'] = null;
}

// }}}
// {{{ �t�B���^�l�ۑ�

$cachefile = $_conf['pref_dir'] . '/p2_res_filter.txt';

// �t�B���^�w�肪�Ȃ���ΑO��ۑ���ǂݍ��ށi�t�H�[���̃f�t�H���g�l�ŗ��p�j
if (!isset($GLOBALS['word'])) {

    if (file_exists($cachefile) and $res_filter_cont = file_get_contents($cachefile)) {
        $res_filter = unserialize($res_filter_cont);
    }
    
// �t�B���^�w�肪�����
} else {

    // �{�^����������Ă����Ȃ�A�t�@�C���ɐݒ��ۑ�
    if (isset($_REQUEST['submit_filter'])) { // !isset($_REQUEST['idpopup'])
        FileCtl::make_datafile($cachefile, $_conf['p2_perm']);
        if ($res_filter) {
            $res_filter_cont = serialize($res_filter);
        }
        if ($res_filter_cont && empty($GLOBALS['popup_filter'])) {
            if (FileCtl::file_write_contents($cachefile, $res_filter_cont) === false) {
                die("Error: cannot write file.");
            }
        }
    }
}

// }}}


//==================================================================
// ���C��
//==================================================================

if (!isset($aThread)) {
    $aThread =& new ThreadRead();
}

// ls�̃Z�b�g
if (!empty($ls)) {
    $aThread->ls = strip_tags(mb_convert_kana($ls, 'a'));
}

// {{{ idx�̓ǂݍ���

// host�𕪉�����idx�t�@�C���̃p�X�����߂�
if (!isset($aThread->keyidx)) {
    $aThread->setThreadPathInfo($host, $bbs, $key);
}

// �f�B���N�g����������΍��
// FileCtl::mkdirFor($aThread->keyidx);

$aThread->itaj = P2Util::getItaName($host, $bbs);
if (!$aThread->itaj) { $aThread->itaj = $aThread->bbs; }

// idx�t�@�C��������Γǂݍ���
if (file_exists($aThread->keyidx)) {
    $lines = file($aThread->keyidx);
    $idx_data = explode('<>', rtrim($lines[0]));
} else {
    $idx_data = array_fill(0, 12, null);
}

$aThread->getThreadInfoFromIdx();

// }}}
// {{{ preview >>1

if (!empty($_GET['onlyone'])) {
    
    $aThread->ls = '1';
    
    // �K���������m�ł͂Ȃ����֋X�I��
    if (!isset($aThread->rescount) and !empty($_GET['rc'])) {
        $aThread->rescount = $_GET['rc'];
    }
    
    $body = $aThread->previewOne();
    $ptitle_ht = htmlspecialchars($aThread->itaj, ENT_QUOTES) . " / " . $aThread->ttitle_hd;

    
    // PC
    if (empty($GLOBALS['_conf']['ktai'])) {
        $read_header_inc_php = P2_LIB_DIR . '/read_header.inc.php';
        $read_footer_inc_php = P2_LIB_DIR . '/read_footer.inc.php';
    // �g��
    } else {
        $read_header_inc_php = P2_IPHONE_LIB_DIR . '/read_header_k.inc.php';
        $read_footer_inc_php = P2_IPHONE_LIB_DIR . '/read_footer_k.inc.php';
    }
    require_once $read_header_inc_php;

    echo $body;
    
    require_once $read_footer_inc_php;
    
    return;
}

// }}}

// DAT�̃_�E�����[�h
if (empty($_GET['offline'])) {
    $aThread->downloadDat();
}

// DAT��ǂݍ���
$aThread->readDat();

// �I�t���C���w��ł����O���Ȃ���΁A���߂ċ����ǂݍ���
if (empty($aThread->datlines) && !empty($_GET['offline'])) {
    $aThread->downloadDat();
    $aThread->readDat();
}


$aThread->setTitleFromLocal(); // �^�C�g�����擾���Đݒ�

// {{{ �\�����X�Ԃ͈̔͂�ݒ肷��

if ($_conf['ktai']) {
    $before_respointer = $_conf['before_respointer_k'];
} else {
    $before_respointer = $_conf['before_respointer'];
}

// �擾�ς݂Ȃ�
if ($aThread->isKitoku()) {
    
    //�u�V�����X�̕\���v�̎��͓��ʂɂ�����ƑO�̃��X����\��
    if (!empty($_GET['nt'])) {
        if (substr($aThread->ls, -1) == "-") {
            $n = $aThread->ls - $before_respointer;
            if ($n < 1) { $n = 1; }
            $aThread->ls = "$n-";
        }
        
    } elseif (!$aThread->ls) {
        $from_num = $aThread->readnum +1 - $_conf['respointer'] - $before_respointer;
        if ($from_num < 1) {
            $from_num = 1;
        } elseif ($from_num > $aThread->rescount) {
            $from_num = $aThread->rescount - $_conf['respointer'] - $before_respointer;
        }
        $aThread->ls = "$from_num-";
    }
    
    if ($_conf['ktai'] && (!strstr($aThread->ls, "n"))) {
        $aThread->ls = $aThread->ls . "n";
    }
    
// ���擾�Ȃ�
} else {
    if (!$aThread->ls) {
        $aThread->ls = $_conf['get_new_res_l'];
    }
}

// �t�B���^�����O�̎��́Aall�Œ�Ƃ���
if (isset($GLOBALS['word'])) {
    $aThread->ls = 'all';
}

$aThread->lsToPoint();

// }}}

//===============================================================
// HTML�v�����g
//===============================================================
$ptitle_ht = htmlspecialchars($aThread->itaj, ENT_QUOTES) . " / " . $aThread->ttitle_hd;

if ($_conf['ktai']) {

    if (isset($GLOBALS['word']) && strlen($GLOBALS['word']) > 0) {
        $GLOBALS['filter_hits'] = 0;
    } else {
        $GLOBALS['filter_hits'] = NULL;
    }
    
    // �w�b�_�v�����g
    require_once P2_IPHONE_LIB_DIR . '/read_header_k.inc.php';
    
    if ($aThread->rescount) {
        require_once P2_IPHONE_LIB_DIR . '/showthreadk.class.php';
        $aShowThread =& new ShowThreadK($aThread);
        $aShowThread->datToHtml();
    }
    
    // �t�b�^�v�����g
    if ($filter_hits !== NULL) {
        resetReadNaviFooterK();
    }
    require_once P2_IPHONE_LIB_DIR . '/read_footer_k.inc.php';
    
} else {

    // �w�b�_ �\��
    require_once P2_IPHONE_LIB_DIR . '/read_header.inc.php';
    flush();
    
    //===========================================================
    // ���[�J��Dat��ϊ�����HTML�\��
    //===========================================================
    // ���X������A�����w�肪�����
    if (isset($GLOBALS['word']) && $aThread->rescount) {
    
        $all = $aThread->rescount;
        
        $GLOBALS['filter_hits'] = 0;
        
        $hits_line = "<p><b id=\"filerstart\">{$all}���X�� <span id=\"searching\">{$GLOBALS['filter_hits']}</span>���X���q�b�g</b></p>";
        echo <<<EOP
<script type="text/javascript">
<!--
document.writeln('{$hits_line}');
var searching = document.getElementById('searching');

function filterCount(n){
    if (searching) {
        searching.innerHTML = n;
    }
}
-->
</script>
EOP;
    }
    
    $debug && $profiler->enterSection("datToHtml");
    
    if ($aThread->rescount) {

        require_once P2_IPHONE_LIB_DIR . '/showthreadpc.class.php';
        $aShowThread =& new ShowThreadPc($aThread);
        
        $res1 = $aShowThread->quoteOne(); // >>1�|�b�v�A�b�v�p
        echo $res1['q'];

        $aShowThread->datToHtml();
    } else {
        $res1 = array();
    }
    
    $debug && $profiler->leaveSection("datToHtml");
    
    // �t�B���^���ʂ�\��
    if ($word && $aThread->rescount) {
        echo <<<EOP
<script type="text/javascript">
<!--
var filerstart = document.getElementById('filerstart');
if (filerstart) {
    filerstart.style.backgroundColor = 'yellow';
    filerstart.style.fontWeight = 'bold';
}
-->
</script>\n
EOP;
        if ($GLOBALS['filter_hits'] > 5) {
            echo "<p><b class=\"filtering\">{$all}���X�� {$GLOBALS['filter_hits']}���X���q�b�g</b></p>\n";
        }
    }
    
    // �t�b�^HTML �\��
    require_once P2_IPHONE_LIB_DIR . '/read_footer.inc.php';

}

// {{{ idx�̒l��ݒ�A�L�^

if ($aThread->rescount) {
    // �����̎��́A���ǐ����X�V���Ȃ�
    if (isset($GLOBALS['word']) and strlen($GLOBALS['word']) > 0) {
        $aThread->readnum = $idx_data[5];
    } else {
        $aThread->readnum = min($aThread->rescount, max(0, $idx_data[5], $aThread->resrange_readnum)); 
    }
    $newline = $aThread->readnum + 1; // $newline�͔p�~�\�肾���A���݊��p�ɔO�̂���

    $sar = array($aThread->ttitle, $aThread->key, $idx_data[2], $aThread->rescount, '',
                $aThread->readnum, $idx_data[6], $idx_data[7], $idx_data[8], $newline,
                $idx_data[10], $idx_data[11], $aThread->datochiok);
    P2Util::recKeyIdx($aThread->keyidx, $sar); // key.idx�ɋL�^
}

// }}}

// �������L�^
if ($aThread->rescount) {
    $newdata = "{$aThread->ttitle}<>{$aThread->key}<>$idx_data[2]<><><>{$aThread->readnum}<>$idx_data[6]<>$idx_data[7]<>$idx_data[8]<>{$newline}<>{$aThread->host}<>{$aThread->bbs}";
    recRecent($newdata);
}

// NG���ځ[����L�^
NgAbornCtl::saveNgAborns();


exit;



//===============================================================================
// �֐� �i���̃t�@�C�����ł̂ݗ��p�j
//===============================================================================
/**
 * �X���b�h���w�肷��
 *
 * @return  array|false
 */
function detectThread()
{
    global $_conf;
    
    $ls = null;
    
    // �X��URL�̒��ڎw��
    if ((isset($_GET['nama_url']) and $url = $_GET['nama_url']) || (isset($_GET['url']) and $url = $_GET['url'])) { 
            
        $url = trim($url);
        
        // 2ch or pink - http://choco.2ch.net/test/read.cgi/event/1027770702/
        if (preg_match('{http://([^/]+\.(2ch\.net|bbspink\.com))/test/read\.cgi/([^/]+)/([0-9]+)/?([^/]+)?}', $url, $matches)) {
            $host = $matches[1];
            $bbs = $matches[3];
            $key = $matches[4];
            $ls = $matches[5];
        
        // c-docomo c-au c-other http://c-au.2ch.net/test/--3!mail=sage/operate/1159594301/519-n
        } elseif (preg_match('{http://((c-docomo|c-au|c-other)\.2ch\.net)/test/([^/]+)/([^/]+)/([0-9]+)/?([^/]+)?}', $url, $m)) {
            require_once P2_LIB_DIR . '/BbsMap.class.php';
            if ($mapped_host = BbsMap::get2chHostByBbs($m[4])) {
                $host = $mapped_host;
                $bbs = $m[4];
                $key = $m[5];
                $ls = $m[6];
            }
        
        // 2ch or pink �ߋ����Ohtml - http://pc.2ch.net/mac/kako/1015/10153/1015358199.html
        } elseif ( preg_match("/(http:\/\/([^\/]+\.(2ch\.net|bbspink\.com))(\/[^\/]+)?\/([^\/]+)\/kako\/\d+(\/\d+)?\/(\d+)).html/", $url, $matches) ){ //2ch pink �ߋ����Ohtml
            $host = $matches[2];
            $bbs = $matches[5];
            $key = $matches[7];
            $kakolog_uri = $matches[1];
            $_GET['kakolog'] = urlencode($kakolog_uri);
            
        // �܂����������JBBS - http://kanto.machibbs.com/bbs/read.pl?BBS=kana&KEY=1034515019
        } elseif ( preg_match("/http:\/\/([^\/]+\.machibbs\.com|[^\/]+\.machi\.to)\/bbs\/read\.(pl|cgi)\?BBS=([^&]+)&KEY=([0-9]+)(&START=([0-9]+))?(&END=([0-9]+))?[^\"]*/", $url, $matches) ){
            $host = $matches[1];
            $bbs = $matches[3];
            $key = $matches[4];
            $ls = $matches[6] ."-". $matches[8];
        } elseif (preg_match("{http://((jbbs\.livedoor\.jp|jbbs\.livedoor.com|jbbs\.shitaraba\.com)(/[^/]+)?)/bbs/read\.(pl|cgi)\?BBS=([^&]+)&KEY=([0-9]+)(&START=([0-9]+))?(&END=([0-9]+))?[^\"]*}", $url, $matches)) {
            $host = $matches[1];
            $bbs = $matches[5];
            $key = $matches[6];
            $ls = $matches[8] ."-". $matches[10];
            
        // �������JBBS http://jbbs.livedoor.com/bbs/read.cgi/computer/2999/1081177036/-100 
        } elseif ( preg_match("{http://(jbbs\.livedoor\.jp|jbbs\.livedoor.com|jbbs\.shitaraba\.com)/bbs/read\.cgi/(\w+)/(\d+)/(\d+)/((\d+)?-(\d+)?)?[^\"]*}", $url, $matches) ){
            $host = $matches[1] . "/" . $matches[2];
            $bbs = $matches[3];
            $key = $matches[4];
            $ls = $matches[5];
        }
    
    } else {
        !empty($_GET['host'])   and $host = $_GET['host'];  // "pc.2ch.net"
        !empty($_POST['host'])  and $host = $_POST['host'];
        isset($_GET['bbs'])     and $bbs  = $_GET['bbs'];   // "php"
        isset($_POST['bbs'])    and $bbs  = $_POST['bbs'];
        isset($_GET['key'])     and $key  = $_GET['key'];   // "1022999539"
        isset($_POST['key'])    and $key  = $_POST['key'];
        !empty($_GET['ls'])     and $ls   = $_GET['ls'];    // "all"
        !empty($_POST['ls'])    and $ls   = $_POST['ls'];
    }
    
    if (empty($host) || !isset($bbs) || !isset($key)) {
        $htm['url'] = htmlspecialchars($url, ENT_QUOTES);
        $msg = "p2 - {$_conf['read_php']}: �X���b�h�̎w�肪�ςł��B<br>"
            . "<a href=\"{$htm['url']}\">" . $htm['url'] . "</a>";
        P2Util::printSimpleHtml($msg);
        die;
        return false;
    }
    
    return array($host, $bbs, $key, $ls);
}

/**
 * �������L�^����
 *
 * @return  boolean
 */
function recRecent($data)
{
    global $_conf;
    
    if (false === FileCtl::make_datafile($_conf['rct_file'], $_conf['rct_perm'])) {
        return false;
    }
    
    $lines = file($_conf['rct_file']);
    $neolines = array();

    // �ŏ��ɏd���v�f���폜���Ă���
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = rtrim($line);
            $lar = explode('<>', $line);
            $data_ar = explode('<>', $data);
            if ($lar[1] == $data_ar[1]) { continue; } // key�ŏd�����
            if (!$lar[1]) { continue; } // key�̂Ȃ����͕̂s���f�[�^
            $neolines[] = $line;
        }
    }
    
    // �V�K�f�[�^�ǉ�
    array_unshift($neolines, $data);

    while (sizeof($neolines) > $_conf['rct_rec_num']) {
        array_pop($neolines);
    }
    
    // ��������
    if ($neolines) {
        $cont = '';
        foreach ($neolines as $l) {
            $cont .= $l . "\n";
        }

        if (false === FileCtl::filePutRename($_conf['rct_file'], $cont)) {
            $errmsg = sprintf('p2 error: %s(), FileCtl::filePutRename() failed.', __FUNCTION__);
            trigger_error($errmsg, E_USER_WARNING);
            return false;
        }
    }
    
    return true;
}