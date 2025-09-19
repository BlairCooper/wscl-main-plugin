<?php
declare(strict_types = 1);
namespace WSCL\Main;

use Psr\Log\LoggerInterface;
use RCS\Traits\SingletonTrait;
use RCS\WP\PluginInfo;

final class WpMailSmtpLogger
{
    private LoggerInterface $logger;

    use SingletonTrait;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function initializeInstance(): void
    {
        if (PluginInfo::isPluginActive('wp-mail-smtp/wp_mail_smtp.php')) {
            add_action('wp_mail_smtp_mailcatcher_smtp_send_after', [$this, 'smtpSendAfter'], 20, 7);
            add_action('wp_mail_smtp_mailcatcher_send_after', [$this, 'sendAfter'], 20, 2);
            add_action('wp_mail_smtp_mailcatcher_send_failed', [$this, 'sendFailed'], 20, 3);
        }
    }

    public function smtpSendAfter(
        bool $isSent,
        string $to,
        string $cc,
        string $bcc,
        string $subject,
        mixed $body,
        string $from
        ): void
    {
        if ($isSent) {
            $this->logger->info(
                'Mail sent via SMTP to {to}: {subject}',
                [
                    'to' => $to,
                    'subject' => $subject
                ]
                );
        } else {
            $this->logger->warning(
                'Mail failed via SMTP to send: {to}: {subject}',
                [
                    'to' => $to,
                    'subject' => $subject
                ]
                );
        }
    }

    /**
     * Log successful send
     *
     * mailCatcher is likely a MailCatcherV6/PHPMailer.
     *
     * @param mixed $mailer
     * @param mixed $mailCatcher
     */
    public function sendAfter(mixed $mailer, mixed $mailCatcher): void
    {
        list($to, $subject, $body) = $this->getDetailsFromCatcher($mailCatcher);

        $this->logger->info(
            'Mail sent via API to {to} : {subject} : {body}',
            [
                'to' => $to,
                'subject' => $subject,
                'body' => $body
            ]
            );
    }

    /**
     * Log failed send
     *
     * mailCatcher is likely a MailCatcherV6/PHPMailer.
     *
     * @param string $errorMsg
     * @param mixed $mailCatcher
     * @param string $mailerSlug
     */
    public function sendFailed(string $errorMsg, mixed $mailCatcher, string $mailerSlug): void
    {
        list($to, $subject) = $this->getDetailsFromCatcher($mailCatcher);

        $this->logger->warning(
            'Sending mail to {to} : {subject} failed: {msg}',
            [
                'to' => $to,
                'subject' => $subject,
                'msg' => $errorMsg
            ]
            );
    }

    /**
     *
     * @param mixed $mailCatcher
     *
     * @return string[]
     */
    private function getDetailsFromCatcher(mixed $mailCatcher): array
    {
        if (method_exists($mailCatcher, 'get_state')) {
            $this->logger->info('Using MailCatcher State');

            $state = $mailCatcher->get_state();

            $to      = $state['to'] ?? 'unknown';
            $subject = $state['Subject'] ?? 'unknown';
            $body    = $state['Body'] ?? 'unknown';

            $this->logger->info('To field is of type: ' . gettype($to));

            if (is_array($to)) {
                $tmpTo = '';

                foreach ($to as $addr) {
                    if (is_array($addr)) {
                        $tmpTo .= implode(' ', $addr);
                    } else {
                        $tmpTo .= $addr;
                    }

                    $tmpTo .= ', ';
                }

                $to = $tmpTo;
            }
        } else {
            if ($mailCatcher instanceof \PHPMailer\PHPMailer\PHPMailer) {
                $to = implode(', ', $mailCatcher->getToAddresses());
                $subject = $mailCatcher->Subject;
                $body = $mailCatcher->Body;
            } else {
                $to = $subject = $body = 'unknown mailCatcher';
            }
        }

        return [$to, $subject, $body];
    }
}
