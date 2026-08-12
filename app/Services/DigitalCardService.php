<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DigitalBusinessCard;

final class DigitalCardService
{
    public function __construct(private readonly DigitalBusinessCard $cards)
    {
    }

    public function createForStaff(int $staffId, string $displayName): int
    {
        $slug = $this->uniqueSlug($displayName);
        return $this->cards->create(['user_id' => $staffId, 'public_slug' => $slug, 'qr_generated_at' => date('Y-m-d H:i:s')]);
    }

    public function uniqueSlug(string $name, ?int $ignoreCardId = null): string
    {
        $base = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name), '-'));
        if ($base === '') { $base = 'udyam-team'; }
        do { $slug = substr($base, 0, 150) . '-' . bin2hex(random_bytes(3)); } while ($this->cards->slugExists($slug, $ignoreCardId));
        return $slug;
    }

    public function absoluteUrl(string $path): string
    {
        $relative = url($path);
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        if ($host !== '') {
            $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
            return ($https ? 'https' : 'http') . '://' . $host . $relative;
        }
        $configured = rtrim((string) ($_ENV['APP_URL'] ?? ''), '/');
        if ($configured === '') { return $relative; }
        $parts = parse_url($configured);
        $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? 'localhost') . (isset($parts['port']) ? ':' . $parts['port'] : '');
        return $origin . $relative;
    }

    public function publicUrl(array $card, bool $qrSource = false): string
    {
        return $this->absoluteUrl('/card/' . rawurlencode((string) $card['public_slug'])) . ($qrSource ? '?source=qr' : '');
    }

    public function visitorHash(string $ip, string $agent): string
    {
        $key = (string) ($_ENV['APP_KEY'] ?? $_ENV['APP_URL'] ?? 'udyam-card-analytics');
        return hash_hmac('sha256', date('Y-m-d') . '|' . $ip . '|' . $agent, $key);
    }

    public function vcard(array $card): string
    {
        $escape = static fn (string $value): string => str_replace(["\\", ";", ",", "\r\n", "\n", "\r"], ["\\\\", "\\;", "\\,", "\\n", "\\n", "\\n"], $value);
        $fullName = trim((string) $card['display_name']);
        $lines = ['BEGIN:VCARD', 'VERSION:3.0', 'N:' . $escape((string) $card['last_name']) . ';' . $escape((string) $card['first_name']) . ';;;', 'FN:' . $escape($fullName), 'ORG:Udyam Ventures', 'TITLE:' . $escape((string) $card['designation'])];
        if (trim((string) $card['department']) !== '') { $lines[] = 'X-DEPARTMENT:' . $escape((string) $card['department']); }
        if (trim((string) $card['mobile']) !== '') { $lines[] = 'TEL;TYPE=CELL:' . $escape((string) $card['mobile']); }
        if (trim((string) $card['office_extension']) !== '') { $lines[] = 'TEL;TYPE=WORK:' . $escape((string) $card['office_extension']); }
        if (trim((string) $card['email']) !== '') { $lines[] = 'EMAIL;TYPE=INTERNET,WORK:' . $escape((string) $card['email']); }
        if (trim((string) $card['website_url']) !== '') { $lines[] = 'URL;TYPE=WORK:' . $escape((string) $card['website_url']); }
        if (trim((string) $card['address']) !== '') { $lines[] = 'ADR;TYPE=WORK:;;' . $escape((string) $card['address']) . ';;;;'; }
        $lines[] = 'URL;TYPE=PROFILE:' . $escape($this->publicUrl($card));
        $lines[] = 'REV:' . gmdate('Ymd\THis\Z');
        $lines[] = 'END:VCARD';
        return implode("\r\n", $lines) . "\r\n";
    }
}
