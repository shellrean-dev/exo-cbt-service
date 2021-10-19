<?php

namespace App\Services\Setting;

interface SettingServiceInterface
{
    public function getSetting();
    public function setSetting(string $value);
}
