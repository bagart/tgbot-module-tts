# TTS Module — SDD

> **Module:** `tgbot-module-tts` (`BAGArt\TelegramBotTts`)
> **Status:** 100% complete

---

## What Was Done

Text-to-speech module with `/voice` command, private auto-speak, provider presets, and SSRF guard.

### Core Components

- **Voice Command**: `/voice` — converts text to speech in Telegram voice message.
- **Private Auto-Speak**: Automatic TTS in private chats (configurable).
- **Provider Presets**: Pre-configured TTS provider settings.
- **Custom JSON**: User-defined TTS configuration with SSRF protection.
- **SSRF Guard**: Prevents SSRF attacks via malicious TTS provider URLs.
- **Cron Prune**: `tts:prune` — cleans up old TTS data.
- **i18n**: 5 locales (RU, EN, FR, ES, ZH). All 5 exposed in chat locale selector.
- **Version**: 0.2.0

### Key Decisions

- SSRF guard mandatory for custom provider URLs (security first).
- Provider presets simplify configuration (no manual JSON needed for common setups).
- Track B multipart delivery through core client (not separate HTTP calls).
- Empty `onException` overrides removed — relies on `TgProcessorDefaultTrait` no-op default.

### Follow-up Fixes (0.2.0)

- Removed stale `schedule_prune_enabled` from Readme (schedule is in `config/tg_modules.php`, not module config).
- Expanded locale setting from `ru|en` to `ru|en|fr|es|zh` matching the 5-locale I18n catalog.
- Fixed I18n docblock: "ru/en" → "ru/en/fr/es/zh string catalog".
- Removed 3 empty `onException` overrides (dead code relying on trait default).
- Version bumped to 0.2.0.

### Files

- `src/` — Domain logic, provider integration, SSRF guard, commands
- `docs/` — Module Readme with usage instructions
