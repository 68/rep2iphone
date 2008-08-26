<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

// p2 - �g�єŃ��X�t�B���^�����O

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';


$_login->authorize(); // ���[�U�F��

// {{{ �X���b�h���

$host = $_GET['host'];
$bbs  = $_GET['bbs'];
$key  = $_GET['key'];
$ttitle = base64_decode($_GET['ttitle_en']);
$ttitle_back = (isset($_SERVER['HTTP_REFERER']))
    ? '<a href="' . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES) . '" title="�߂�">' . $ttitle . '</a>'
    : $ttitle;

// }}}
// {{{ �O��t�B���^�l�ǂݍ���

require_once P2_LIB_DIR . '/filectl.class.php';

$cachefile = $_conf['pref_dir'] . '/p2_res_filter.txt';

if (file_exists($cachefile) and $res_filter_cont = file_get_contents($cachefile)) {
    $res_filter = unserialize($res_filter_cont);
}

$field = array('hole'=>'', 'msg'=>'', 'name'=>'', 'mail'=>'', 'date'=>'', 'id'=>'', 'beid'=>'', 'belv'=>'');
$match = array('on'=>'', 'off'=>'');
$method = array('and' => '', 'or' => '', 'just' => '', 'regex' => '', 'similar' => '');

$field[$res_filter['field']]   = ' selected';
$match[$res_filter['match']]   = ' selected';
$method[$res_filter['method']] = ' selected';

// }}}

/**
 * �����t�H�[���y�[�W HTML�\��
 * s1, s2�Ɠ���� submit name �����邯�ǈꏏ�ۂ��Bs1, s2 �͌����� word�Ŕ��肵�Ă���
 */
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOF
<html>
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <link rel="stylesheet" type="text/css" href="./iui/read.css">
    <title>�X��������</title>
</head>
<body{$k_color_settings}>
<div class="toolbar">
<h1>{$ttitle_back}</h1>
<a id="backButton" class="button" href="iphone.php">TOP</a>
</div>


<form class="panel" id="header" method="get" action="{$_conf['read_php']}" accept-charset="{$_conf['accept_charset']}">
<h2>�������[�h</h2>
<filedset>
<input type="hidden" name="detect_hint" value="����">
<input type="hidden" name="host" value="{$host}">
<input type="hidden" name="bbs" value="{$bbs}">
<input type="hidden" name="key" value="{$key}">
<input type="hidden" name="ls" value="all">
<input type="hidden" name="offline" value="1">
<input class="serch" id="word" name="word">
<input class="whitebutton" type="submit" name="s1" value="����">
</filedset>
<br>
<h2>�����I�v�V����</h2>
<filedset>
<select class="serch" id="field" name="field">
<option value="hole"{$field['hole']}>�S��</option>
<option value="msg"{$field['msg']}>���b�Z�[�W</option>
<option value="name"{$field['name']}>���O</option>
<option value="mail"{$field['mail']}>���[��</option>
<option value="date"{$field['date']}>���t</option>
<option value="id"{$field['id']}>ID</option>
<!-- <option value="belv"{$field['belv']}>�|�C���g</option> -->
</select>
��
<select class="serch" id="method" name="method">
<option value="or"{$method['or']}>�����ꂩ</option>
<option value="and"{$method['and']}>���ׂ�</option>
<option value="just"{$method['just']}>���̂܂�</option>
<option value="regex"{$method['regex']}>���K�\��</option>
</select>
��
<select class="serch" id="match" name="match">
<option value="on"{$match['on']}>�܂�</option>
<option value="off"{$match['off']}>�܂܂Ȃ�</option>
</select><br>
<input class="whitebutton" type="submit" name="s2" value="����">

{$_conf['k_input_ht']}
</form>

</filedset>
</div>
</body>
</html>
EOF;
