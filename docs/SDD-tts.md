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
- **i18n**: 5 locales (RU, EN, FR, ES, ZH).

### Key Decisions

- SSRF guard mandatory for custom provider URLs (security first).
- Provider presets simplify configuration (no manual JSON needed for common setups).
- Track B multipart delivery through core client (not separate HTTP calls).

### Files

- `src/` — Domain logic, provider integration, SSRF guard, commands
- `docs/` — Module Readme with usage instructions
