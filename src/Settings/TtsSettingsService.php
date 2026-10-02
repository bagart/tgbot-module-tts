<?php

declare(strict_types=1);

namespace BAGArt\TelegramBotTts\Settings;

use BAGArt\TelegramBot\Contracts\Modules\ModuleEnablementContract;
use BAGArt\TelegramBot\Contracts\Modules\ModuleSettingsContract;
use BAGArt\TelegramBotTts\TtsModuleId;

/**
 * Reads effective TTS settings and persists chat-level patches through
 * ModuleSettingsContract (driver-agnostic: legacy enablement rows or engine
 * activation rows). The reserved `enabled` patch key flips chat enablement
 * instead of landing in the settings map — the contract owns that mirror
 * and the cache refresh behind it.
 */
class TtsSettingsService
{
    public function __construct(
        private readonly ModuleSettingsContract $settings,
        private readonly ModuleEnablementContract $enablement,
    ) {
    }

    public function get(string $botId, int $chatId): TtsSettings
    {
        return TtsSettings::fromArray(
            $this->settings->settingsFor(TtsModuleId::ID, $botId, $chatId),
        );
    }

    public function isEnabled(string $botId, int $chatId): bool
    {
        return $this->enablement->isEnabled(TtsModuleId::ID, $botId, $chatId);
    }

    /**
     * @param  array<string, mixed|null>  $patch  settings keys to merge at the chat scope; null removes a key, `enabled` flips enablement
     */
    public function patch(string $botId, int $chatId, array $patch): void
    {
        $this->settings->patchSettings(TtsModuleId::ID, $botId, $chatId, $patch);
    }
}
