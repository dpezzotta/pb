<?php

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;

if (!function_exists('pb_mailgun_smtp_config')) {
    function pb_mailgun_smtp_config() {
        $source = 'missing';
        $local_config = dirname(__FILE__) . '/MailgunSmtp.local.php';
        if (file_exists($local_config)) {
            require($local_config);
        }

        $user = getenv('PB_MAILGUN_SMTP_USER');
        $password = getenv('PB_MAILGUN_SMTP_PASSWORD');
        $host = getenv('PB_MAILGUN_SMTP_HOST');
        $port = getenv('PB_MAILGUN_SMTP_PORT');
        if ($user && $password) {
            $source = 'environment';
        }

        if ((!$user || !$password) && isset($pb_mailgun_smtp) && is_array($pb_mailgun_smtp)) {
            $user = !empty($pb_mailgun_smtp['user']) ? $pb_mailgun_smtp['user'] : $user;
            $password = !empty($pb_mailgun_smtp['password']) ? $pb_mailgun_smtp['password'] : $password;
            $host = !empty($pb_mailgun_smtp['host']) ? $pb_mailgun_smtp['host'] : $host;
            $port = !empty($pb_mailgun_smtp['port']) ? $pb_mailgun_smtp['port'] : $port;
            if ($user && $password) {
                $source = 'local config';
            }
        }

        if (!$user || !$password) {
            $legacy_config = pb_mailgun_smtp_legacy_config();
            $user = !empty($legacy_config['user']) ? $legacy_config['user'] : $user;
            $password = !empty($legacy_config['password']) ? $legacy_config['password'] : $password;
            if ($user && $password) {
                $source = 'legacy fallback';
            }
        }

        return array(
            'user' => $user,
            'password' => $password,
            'host' => $host ? $host : 'smtp.mailgun.org',
            'port' => $port ? (int)$port : 587,
            'source' => $source
        );
    }
}

if (!function_exists('pb_mailgun_smtp_legacy_config')) {
    function pb_mailgun_smtp_legacy_config() {
        $legacy_file = dirname(__FILE__) . '/../admin/wednesday_mailers.php';
        if (!is_readable($legacy_file)) return array();

        $contents = file_get_contents($legacy_file);
        if ($contents === false) return array();

        $user = '';
        $password = '';
        if (preg_match('/\$mail_from\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/', $contents, $match)) {
            $user = $match[1];
        }
        if (preg_match('/\$pass\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/', $contents, $match)) {
            $password = $match[1];
        }

        return array('user' => $user, 'password' => $password);
    }
}

if (!function_exists('pb_mailgun_smtp_mailer')) {
    function pb_mailgun_smtp_mailer(&$error_message = '') {
        if (!class_exists('Symfony\Component\Mailer\Transport')) {
            $error_message = 'The Mailgun email library is not available on this server.';
            return null;
        }

        $config = pb_mailgun_smtp_config();
        if (empty($config['user']) || empty($config['password'])) {
            $error_message = 'Mailgun SMTP is not configured. Add PB_MAILGUN_SMTP_USER and PB_MAILGUN_SMTP_PASSWORD, or create db/inc/MailgunSmtp.local.php.';
            return null;
        }

        $dsn = 'smtp://' . rawurlencode($config['user']) . ':' . rawurlencode($config['password']) . '@' . $config['host'] . ':' . (int)$config['port'];
        return new Mailer(Transport::fromDsn($dsn));
    }
}

?>
