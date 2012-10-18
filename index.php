<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Horsley
 * Date: 12-5-20
 * Time: 下午7:44
 * To change this template use File | Settings | File Templates.
 */
session_start();
$token = md5(uniqid(rand(),true));
$_SESSION['token'] = $token;
?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8">
    <title>给我发邮件</title>
    <script type="text/javascript" src="ckeditor/ckeditor.js"></script>
    <style type="text/css">
        html, body, div, ul, li, form, label{
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
        }
        body {
            background-color: #f8f8f8;
            font-family: '微软雅黑',sans-serif;
        }
        ul {
            list-style: none;
        }
        input {
            vertical-align: middle;
        }
        #footer a {
            text-decoration: none;
            color: #222;
        }
        #footer a:hover {
            text-decoration: underline;
        }
        #main {
            width: 800px;
            margin: 0 auto;
        }
        #header {
            width: 800px;
            margin: 2em auto 1em;
            text-align: center;
        }
        #footer {
            width: 800px;
            margin: 1em auto;
            text-align: center;
            color: #999;
            font-size: 14px;
        }
        h1 {
            font-weight: normal;
            color: #123;
        }
        #subject{
            width: 98%;
            -moz-border-radius: 5px;
            -webkit-border-radius: 5px;
            border-radius: 5px;
            border: 1px solid #d3d3d3;
            padding: 1%;
        }
        label {
            font-size: 16px;
            color: #222;
            padding-right: 5px;
        }
        .tdr {
            width: 720px;
            padding: 5px 0;
        }
        .panel {

            margin-left: 8px;
            padding: 26px 24px 26px;
            font-weight: normal;
            -moz-border-radius: 3px;
            -webkit-border-radius: 3px;
            border-radius: 3px;
            background: white;
            border: 1px solid #E5E5E5;
            -moz-box-shadow: rgba(200,200,200,0.7) 0 4px 10px -1px;
            -webkit-box-shadow: rgba(200,200,200,0.7) 0 4px 10px -1px;
            box-shadow: rgba(200,200,200,0.7) 0 4px 10px -1px;
            clear:both;
        }
        .button {
            width: 90px;
            height: 30px;
            line-height: 30px;
            display: inline-block;
            background: #66f;
            border: 1px solid #d3d3d3;
            font-weight: bold;
            font-size: 16px;
            font-family: '微软雅黑',sans-serif;
            color: #fff;
        }
        .options {
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
<div id="header">
    <h1>给我发邮件吧！</h1>
</div>
<div id="main" class="panel">
    <form action="recv.php" method="post" enctype="multipart/form-data">
        <table style="width: 100%">
            <tr><td>收件人: </td><td colspan="2">i@xinjian.li</td></tr>
            <tr><td><label for="subject">主题: </label></td>
                <td class="tdr" colspan="2"><input name="subject" type="text" id="subject"/></td>
            </tr>
            <tr><td><label for="mail_content">正文: </label></td>
                <td class="tdr" colspan="2">
                <textarea name="message" id="mail_content">
                    <span style="font-size:18px;">写清楚你是谁，要做啥，回信地址等等</span>
                </textarea></td>
            </tr>
            <tr><td>&nbsp;</td> <!-- rev 20120521, mail attachment added -->
                <td><label for="attachment">附件: </label><input type="file" name="attachment" id="attachment"></td>
                <td style="text-align: right">
                    <input type="checkbox" name="fetion" id="fetion" style="vertical-align: middle; margin-top:-2px; margin-bottom:1px;"/><label for="fetion" class="options"> 通过飞信通知本人</label>
                    <input type="submit" class="button" value="发送"/></td>
            </tr>
        </table>
        <input type="hidden" name="_csrf" value="<?php echo $token; ?>">
    </form>
</div>
<div id="footer">
    <p>如果这里功能不好用，你还是在你自己邮箱编辑邮件发送给我吧。<br /> <!-- rev 20120522, -->
        珍惜时间，谢谢合作。</p>
    <p><a href="http://weibo.com/horsley" target="_blank">@Horsley阿黎</a></p>
</div>
<script type="text/javascript">
    CKEDITOR.replace( 'mail_content' );
</script>

</body>
</html>