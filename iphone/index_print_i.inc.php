<?php

 /**
 * ���C�ɔ�HTML�\������ for �g��
 *
 * @access  public
 * @return  void
 */
/* iPhone ��Top�p�ɕ\�������� */
function printFavItaHtml()
{
    global $_conf;
    
    $show_flag = false;
        
if (file_exists($_conf['favita_path']) and $lines = file($_conf['favita_path'])) {
        
    echo '<li class="group">���C�ɔꗗ</li>';
        $i = 0;
        for($i=0; $i < 7;$i++){
        //foreach ($lines as $l) {
            $l= $lines[$i];
            $l = rtrim($l);
            if (preg_match("/^\t?(.+)\t(.+)\t(.+)$/", $l, $matches)) {
                $itaj = rtrim($matches[3]);
                $itaj_hs = htmlspecialchars($itaj, ENT_QUOTES);
                $itaj_en = rawurlencode(base64_encode($itaj));
                echo <<<EOP
    <li><a href="{$_conf['subject_php']}?host={$matches[1]}&amp;bbs={$matches[2]}&amp;itaj_en={$itaj_en}{$_conf['k_at_a']}">{$itaj_hs}</a></li>
EOP;
        
                $show_flag = true;
            }
        }
    //���C�ɓ��肪�\��������Ȃ�������ʃy�[�W��
    if($lines[7]){
    echo <<<EOP
    <li><a href="menu_k.php?view=favita{$_conf['k_at_a']}{$user_at_a}">���C�ɔ̑S��</a></li>
EOP;
    }
    }
        
    if (empty($show_flag)) {
        echo "<p>���C�ɔ͂܂��Ȃ��悤��</p>";
    }
}
/**
 * p2 - �g�їp�C���f�b�N�X��HTML�v�����g����֐�
 */
function index_print_k()
{
    global $_conf, $_login;

    $newtime = date('gis');
    
    $body = "";
    $ptitle = "rep2phone";
    
    // ���O�C�����[�U���
    $htm['auth_user'] = "<p>���[�U�[: {$_login->user_u} - " . date("Y/m/d (D) G:i:s") . "</p>\n";
    
    // p2���O�C���pURL
    $login_url = rtrim(dirname(P2Util::getMyUrl()), '/') . '/';
    $login_url_pc = $login_url . '?b=pc';
    $login_url_pc_hs = hs($login_url_pc);
    $login_url_k = $login_url . '?b=k&user=' . $_login->user_u;
    $login_url_k_hs = hs($login_url_k);
    
    // �O��̃��O�C�����
    if ($_conf['login_log_rec'] && $_conf['last_login_log_show']) {
        if (($log = P2Util::getLastAccessLog($_conf['login_log_file'])) !== false) {
            $log_hd = array_map('htmlspecialchars', $log);
            $htm['last_login'] = <<<EOP
<font color="#888888">
�O���۸޲ݏ�� - {$log_hd['date']}<br>
հ��:   {$log_hd['user']}<br>
IP:     {$log_hd['ip']}<br>
HOST:   {$log_hd['host']}<br>
UA:     {$log_hd['ua']}<br>
REFERER: {$log_hd['referer']}
</font>
EOP;
        }
    }
    
    // �Â��Z�b�V����ID���L���b�V������Ă��邱�Ƃ��l�����āA���[�U����t�����Ă���
    // �i���t�@�����l�����āA���Ȃ��ق��������ꍇ������̂Œ��Ӂj
    $user_at_a = '&amp;user=' . $_login->user_u;
    $user_at_q = '?user=' . $_login->user_u;
    
    require_once P2_LIB_DIR . '/brdctl.class.php';
    $search_form_htm = BrdCtl::getMenuKSearchFormHtml('menu_k.php');

    //=========================================================
    // �g�їp HTML �v�����g
    //=========================================================
    P2Util::header_nocache();
    echo $_conf['doctype'];
    echo <<<EOP
<html>
<head>
    {$_conf['meta_charset_ht']}
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
<script type="text/javascript"> 
<!-- 
window.onload = function() { 
setTimeout(scrollTo, 100, 0, 1); 
} 
// --> 
</script> 
<style type="text/css" media="screen">@import "./iui/iui.css";@import "./iui/index.css";</style>
    <title>{$ptitle}</title>
</head>
<body>
    <div class="toolbar">
<h1 id="pageTitle">{$ptitle}</h1>
<a class="button" href="editpref_i.php?dummy=1{$user_at_a}{$_conf['k_at_a']}">�ݒ�Ǘ� </a>
</div>
EOP;
    P2Util::printInfoHtml();
echo <<<EOP
<ul id="other" class="hidden">
    <li class="group">���̑�</li>
    <li><a href="subject.php?spmode=res_hist{$_conf['k_at_a']}{$user_at_a}">��������</a> </li>
    <li><a href="read_res_hist.php?nt={$newtime}{$_conf['k_at_a']}">���O</a></li>
    <li><a href="subject.php?spmode=palace&amp;norefresh=1{$_conf['k_at_a']}{$user_at_a}">�X���̓a��</a></li>
    <li><a href="editfavita_i.php?k=1">���C�ɓ���ҏW</a></li>
    <li><a href="setting.php?dummy=1{$user_at_a}{$_conf['k_at_a']}">���O�C���Ǘ�</a></li>
<li class="group">����</li>
{$search_form_htm} 
</ul>
<ul id="fav" class="hidden">
EOP;

printFavItaHtml();

echo <<<EOP

</ul>
    
<ul id="home">
    <li class="group">���j���[</li>
    <li><a href="menu_k.php?view=cate{$_conf['k_at_a']}{$user_at_a}">���X�g</a></li>
     <li><a href="subject.php?spmode=fav&amp;norefresh=1{$_conf['k_at_a']}{$user_at_a}">���C�ɃX���̑S��</a></li>
    <li><a href="subject.php?spmode=fav&amp;sb_view=shinchaku{$_conf['k_at_a']}{$user_at_a}">���C�ɃX���̐V��</a></li>
    <li><a href="subject.php?spmode=recent&amp;sb_view=shinchaku{$_conf['k_at_a']}{$user_at_a}">�ŋߓǂ񂾃X���̐V��</a></li>
    <li><a href="subject.php?spmode=recent&amp;norefresh=1{$_conf['k_at_a']}{$user_at_a}">�ŋߓǂ񂾃X���̑S��</a></li>
</ul>
<div id="foot">
 <div class="foot_index">
<span class="top"><a onclick="all.item('home').style.visibility='visible';all.item('other').style.visibility='hidden';all.item('fav').style.visibility='hidden'">Top</a></span>
<span class="fav"><a onclick="all.item('fav').style.visibility='visible';all.item('home').style.visibility='hidden';all.item('other').style.visibility='hidden'">Top</a></span>
<span class="other"><a onclick="all.item('other').style.visibility='visible';all.item('home').style.visibility='hidden';all.item('fav').style.visibility='hidden'">Top</a></span>
 </div>
</div>
</body>
</html>
EOP;

}

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
