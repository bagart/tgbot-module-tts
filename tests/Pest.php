<?php

declare(strict_types=1);

/*
 * TTS Feature tests exercise the chat-scoped legacy enablement path:
 * TtsSettingsService::patch() writes tg_module_enablements rows directly,
 * and reads must reflect them through the chat → bot → platform chain. The
 * migration chain drops that table (2026_09_20) and the platform driver is
 * 'engine' (bot-level only), so recreate the table and pin BOTH contracts
 * to the legacy service — same pattern as the summarizer and antispam
 * Feature suites. The root bootstrap requires this file (Pest auto-loads it
 * only for standalone package runs).
 */

pest()->beforeEach(function () {
    \BAGArt\TelegramBotMenu\Tests\Support\LegacyEnablementSchema::ensure();
})->in(__DIR__.'/Feature');

pest()->beforeEach(function () {
    $legacyService = app(\BAGArt\TelegramBotManagement\Services\TgModuleEnablementService::class);
    app()->instance(\BAGArt\TelegramBot\Contracts\Modules\ModuleEnablementContract::class, $legacyService);
    app()->instance(\BAGArt\TelegramBot\Contracts\Modules\ModuleSettingsContract::class, $legacyService);
})->in(__DIR__.'/Feature');
