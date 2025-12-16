<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';

class MailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        // Genel SMTP ayarları
        $this->mailer->isSMTP();
        $this->mailer->Host       = 'mail.metafonics.com';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->CharSet    = "UTF-8";
        $this->mailer->Username   = "info@metafonics.com";
        $this->mailer->Password   = "Metafonics1010";
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mailer->Port       = 465;
    }

    public function sendMail(array $options)
    {
        try {
            $fromName  = $options['fromName'] ?? 'Gönderen';
            $fromEmail = $options['fromEmail'] ?? null;
            $to        = $options['to'] ?? 'info@metafonics.com';
            $subject   = $options['subject'] ?? 'Yeni Mail';
            $body      = $options['body'] ?? '';

            $this->mailer->setFrom("info@metafonics.com", $fromName);
            $this->mailer->addAddress($to, "Metafonics Yapay Zeka Teknolojileri A.Ş.");
            if ($fromEmail) $this->mailer->addReplyTo($fromEmail, $fromName);

            // CC & BCC
            if (!empty($options['cc'])) {
                foreach ((array)$options['cc'] as $cc) {
                    $this->mailer->addCC($cc);
                }
            }
            if (!empty($options['bcc'])) {
                foreach ((array)$options['bcc'] as $bcc) {
                    $this->mailer->addBCC($bcc);
                }
            }

            // Attachments
            if (!empty($options['attachments'])) {
                foreach ((array)$options['attachments'] as $file) {
                    $this->mailer->addAttachment($file);
                }
            }

            // Reply-To override
            if (!empty($options['replyTo'])) {
                foreach ((array)$options['replyTo'] as $reply) {
                    $this->mailer->addReplyTo($reply['email'], $reply['name'] ?? '');
                }
            }

            // Priority
            if (!empty($options['priority'])) {
                $this->mailer->Priority = $options['priority'];
            }

            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;

            $this->mailer->send();
            return ['status' => 'success', 'message' => 'Mail başarıyla gönderildi.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Mail gönderilemedi: ' . $this->mailer->ErrorInfo];
        }
    }
}
