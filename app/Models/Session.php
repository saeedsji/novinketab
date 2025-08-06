<?php

namespace App\Models;

use App\Lib\Helper\JalaliClass;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class Session extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sessions';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_activity' => 'datetime',
    ];

    /**
     * Get the user that the session belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted last activity time in Jalali.
     *
     * @return string
     */
    public function last_activity(): string
    {
        return Jalalian::fromCarbon($this->last_activity)->format('Y/m/d (H:i:s)');
    }

    /**
     * Parses the user agent string to get platform and browser information.
     *
     * @param bool $getParsedData If true, returns an array of parsed data instead of a formatted string.
     * @return string|array
     */
    public function user_agent(bool $getParsedData = false)
    {
        $userAgent = $this->user_agent;

        $platform = 'Unknown OS';
        if (stripos($userAgent, 'Windows NT 10.0') !== false) $platform = 'Windows 10';
        elseif (stripos($userAgent, 'Windows NT 6.3') !== false) $platform = 'Windows 8.1';
        elseif (stripos($userAgent, 'Windows NT 6.1') !== false) $platform = 'Windows 7';
        elseif (stripos($userAgent, 'Mac OS X') !== false) $platform = 'macOS';
        elseif (stripos($userAgent, 'Linux') !== false) $platform = 'Linux';
        elseif (stripos($userAgent, 'Android') !== false) $platform = 'Android';
        elseif (stripos($userAgent, 'iPhone') !== false) $platform = 'iOS';

        $browser = 'Unknown Browser';
        if (preg_match('/Edg\/([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'Edge ' . $matches[1];
        } elseif (preg_match('/Chrome\/([0-9\.]+)/i', $userAgent, $matches) && !str_contains(strtolower($userAgent), 'opr')) {
            $browser = 'Chrome ' . $matches[1];
        } elseif (preg_match('/Firefox\/([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'Firefox ' . $matches[1];
        } elseif (preg_match('/Safari\/([0-9\.]+)/i', $userAgent, $matches) && !str_contains(strtolower($userAgent), 'chrome')) {
            $browser = 'Safari ' . $matches[1];
        } elseif (preg_match('/MSIE ([0-9\.]+)/i', $userAgent, $matches)) {
            $browser = 'Internet Explorer ' . $matches[1];
        }

        if ($getParsedData) {
            return ['platform' => $platform, 'browser' => $browser];
        }

        return "$platform - $browser";
    }
}
