<?php
//////////////////////////////////////////////SETTINGS//////////////////////////////////////////////
define('MAIL_TO', '');                          //To 接收人邮件地址，接收从本页面发送邮件的邮箱
define('MAIL_TO_NAME', 'Myself');               //To Name 友好的收件人称呼
define('MAIL_FROM', '');                        //From 发件人邮件地址
define('MAIL_FROM_NAME', 'Mail me page');       //From name 友好的发件人称呼
define('MAIL_SMTP_HOST', '');                   //SMTP Server 发信服务器，如smtp.qq.com
define('MAIL_SMTP_USER', '');                   //SMTP User smtp用户名，如253719360@qq.com
define('MAIL_SMTP_PASS', '');                   //SMTP Pass smtp密码，如qq邮箱密码

define('FETION_PHONE_NUM', '');                 //Phone  飞信账号电话号码，这里只能是数字手机号码
define('FETION_PASSWORD', '');                  //Password 飞信密码

////////////////////////////////////////////////////////////////////////////////////////////////////
session_start();
//CSRF CHECK
if ($_SESSION['token'] != $_POST['_csrf']){
    echo 'Cross-site request forgery found! You have been logged.';
    exit;
}
if (!isset($_POST['subject']) || $_POST['subject'] == '' || !isset($_POST['message']) || $_POST['message'] == '' ) {
    echo 'You must fill the field, both subject and content are required!';
    exit;
}


error_reporting(0);
send_mail('', MAIL_TO, $_POST['subject'], $_POST['message']);

//Fetion SMS Notification
if (isset($_POST['fetion']) && $_POST['fetion'] == 'on') {
    fetion_me('mailme页面通知，邮件主题：'.$_POST['subject']);
}

echo 'The message has been sent, please be patient waiting for a reply.';

////////////////////////////////////////////////////////////////////////////////////////////////////
function fetion_me($message) {
    require_once('PHPFetionEx.php');

    $fetion = new PHPFetionEx();
    $fetion->setUser(FETION_PHONE_NUM, FETION_PASSWORD);
    $fetion->login();
    $fetion->sendToMyself($message);
    $fetion->logout();
}
function send_mail($from, $to, $subject, $message) {
    require_once("class.phpmailer.php");

    $mail = new PHPMailer(); //建立邮件发送类

    $mail->IsSMTP(); 						// 使用SMTP方式发送
    $mail->Host = MAIL_SMTP_HOST; 	// 您的企业邮局域名
    $mail->SMTPAuth = true; 				// 启用SMTP验证功能
    $mail->Username = MAIL_SMTP_USER; 	// 邮局用户名(请填写完整的email地址)
    $mail->Password = MAIL_SMTP_PASS; 			// 邮局密码
    $mail->From = MAIL_FROM; //邮件发送者email地址
    $mail->FromName = MAIL_FROM_NAME;
    $mail->AddAddress("$to", MAIL_TO_NAME);//收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
    //$mail->AddReplyTo("", "");
    //$mail->AddAttachment("/var/tmp/file.tar.gz"); // 添加附件

    //Add Attachment
    if (count($_FILES)>0) {
        foreach($_FILES as $f) {
            $mail->AddAttachment($f['tmp_name'], $f['name']);
        }
    }
    
    $mail->IsHTML(true); 					// set email format to HTML //是否使用HTML格式
    $mail->Subject = $subject; //邮件标题
    $mail->Body = $message; //邮件内容
    //$mail->AltBody = "This is the body in plain text for non-HTML mail clients"; //附加信息，可以省略
    $mail->Send();

    if($mail->IsError()) {
        die($mail->ErrorInfo);
    }
}