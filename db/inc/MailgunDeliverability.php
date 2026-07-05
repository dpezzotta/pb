<?php

if (!function_exists('pb_mailgun_apply_deliverability_headers')) {
    function pb_mailgun_apply_deliverability_headers($email, $tag = 'planetbravo-mail') {
        if (!is_object($email) || !method_exists($email, 'getHeaders')) {
            return $email;
        }
        $headers = $email->getHeaders();
        $deliverability_headers = array(
            'X-Mailgun-Track' => 'no',
            'X-Mailgun-Track-Clicks' => 'no',
            'X-Mailgun-Track-Opens' => 'no'
        );
        foreach ($deliverability_headers as $name => $value) {
            if (!method_exists($headers, 'has') || !$headers->has($name)) {
                $headers->addTextHeader($name, $value);
            }
        }
        $tag = preg_replace('/[^a-zA-Z0-9_.-]/', '-', (string)$tag);
        $tag = trim($tag, '-');
        if ($tag != '' && (!method_exists($headers, 'has') || !$headers->has('X-Mailgun-Tag'))) {
            $headers->addTextHeader('X-Mailgun-Tag', substr($tag, 0, 128));
        }
        return $email;
    }
}

?>
