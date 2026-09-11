<?php

declare(strict_types=1);

namespace BAGArt\TelegramBotTts\Web;

use BAGArt\TelegramBotMenu\Contracts\TgSettingsFormContract;
use BAGArt\TelegramBotMenu\Contracts\TgWebUiContract;
use BAGArt\TelegramBotMenu\Manifest\TgWebUiManifest;
use BAGArt\TelegramBotMenu\Manifest\UiAudience;
use BAGArt\TelegramBotMenu\Manifest\UiEntry;
use BAGArt\TelegramBotMenu\Manifest\UiField;
use BAGArt\TelegramBotMenu\Manifest\UiFieldType;
use BAGArt\TelegramBotMenu\Manifest\UiGroup;
use BAGArt\TelegramBotMenu\Manifest\UiKind;
use BAGArt\TelegramBotTts\Provider\ProviderRegistry;
use BAGArt\TelegramBotTts\Settings\TtsSettings;
use BAGArt\TelegramBotTts\TtsModuleId;
use InvalidArgumentException;

/**
 * Menu-hub settings surface for TTS (menu_integration.md M-3b): the /voice
 * in-chat panel mirrored as a declarative schema manifest + §8.3 settings
 * form, one class serving both contracts (§18). Provider API keys stay out
 * of the schema (encrypted at rest, in-chat flow only — §8.5).
 */
final class TtsWebUi implements TgSettingsFormContract, TgWebUiContract
{
    public static function manifest(): TgWebUiManifest
    {
        return new TgWebUiManifest(
            moduleId: TtsModuleId::ID,
            title: 'Text → Voice',
            icon: '🔊',
            kind: UiKind::Setting,
            minAudience: UiAudience::Admin,
            description: 'Speak text with TTS voices',
            entry: UiEntry::schema([
                UiGroup::of('speech', 'Speech', [
                    UiField::bool('auto_speak', 'Auto-speak my messages', default: false),
                    UiField::enum('provider_key', 'Provider', options: self::providerOptions(), default: 'edge-tts'),
                    new UiField('voice', 'Voice', UiFieldType::String, default: '', extra: ['maxLength' => 128], help: 'Provider voice id, e.g. ru-RU-DmitryNeural; empty = provider default'),
                    UiField::enum('caption', 'Caption', options: [
                        ['value' => TtsSettings::CAPTION_NONE, 'label' => 'No caption'],
                        ['value' => TtsSettings::CAPTION_ORIGINAL, 'label' => 'Original text'],
                        ['value' => TtsSettings::CAPTION_TRUNCATED, 'label' => 'Truncated text'],
                    ], default: TtsSettings::CAPTION_ORIGINAL),
                ]),
                UiGroup::of('limits', 'Limits and errors', [
                    new UiField('max_chars', 'Max chars per voice message', UiFieldType::Int, default: TtsSettings::DEFAULT_MAX_CHARS, extra: ['min' => 1, 'max' => 4000]),
                    new UiField('daily_quota', 'Daily quota per chat', UiFieldType::Int, default: TtsSettings::DEFAULT_DAILY_QUOTA, extra: ['min' => 0, 'max' => 10000]),
                    UiField::enum('on_error', 'On error', options: [
                        ['value' => TtsSettings::ERROR_MODE_AUTO, 'label' => 'Auto (emoji in groups, message in private)'],
                        ['value' => TtsSettings::ERROR_MODE_SILENT, 'label' => 'Silent'],
                        ['value' => TtsSettings::ERROR_MODE_EMOJI, 'label' => 'Emoji reaction'],
                        ['value' => TtsSettings::ERROR_MODE_MESSAGE, 'label' => 'Error message'],
                    ], default: TtsSettings::ERROR_MODE_AUTO),
                ]),
            ]),
            sortKey: 'tts',
            memberReadVisible: true,
        );
    }

    /** @return array<string, array<string, string>> */
    public static function translations(): array
    {
        return [
            'ru' => [
                'Text → Voice' => 'Текст → голос',
                'Speak text with TTS voices' => 'Озвучка текста TTS-голосами',
                'Auto-speak my messages' => 'Озвучивать мои сообщения',
                'Provider' => 'Провайдер',
                'Voice' => 'Голос',
                'Caption' => 'Подпись',
                'Max chars per voice message' => 'Макс. символов в голосовом',
                'Daily quota per chat' => 'Дневная квота на чат',
                'On error' => 'При ошибке',
            ],
            'fr' => [
                'Text → Voice' => 'Texte → Voix',
                'Speak text with TTS voices' => 'Lire le texte à voix haute via TTS',
                'Auto-speak my messages' => 'Lire automatiquement mes messages',
                'Provider' => 'Fournisseur',
                'Voice' => 'Voix',
                'Caption' => 'Légende',
                'Max chars per voice message' => 'Caractères max. par message vocal',
                'Daily quota per chat' => 'Quotidienne quota par chat',
                'On error' => "En cas d'erreur",
            ],
            'es' => [
                'Text → Voice' => 'Texto → Voz',
                'Speak text with TTS voices' => 'Leer texto en voz alta con TTS',
                'Auto-speak my messages' => 'Leer automáticamente mis mensajes',
                'Provider' => 'Proveedor',
                'Voice' => 'Voz',
                'Caption' => 'Subtítulo',
                'Max chars per voice message' => 'Máx. caracteres por mensaje de voz',
                'Daily quota per chat' => 'Cuota diaria por chat',
                'On error' => 'En caso de error',
            ],
            'zh' => [
                'Text → Voice' => '文字 → 语音',
                'Speak text with TTS voices' => '使用TTS语音朗读文字',
                'Auto-speak my messages' => '自动朗读我的消息',
                'Provider' => '服务商',
                'Voice' => '语音',
                'Caption' => '标题',
                'Max chars per voice message' => '语音消息最大字符数',
                'Daily quota per chat' => '每聊每日配额',
                'On error' => '出错时',
            ],
        ];
    }

    public function validate(array $raw): array
    {
        $patch = [];

        if (array_key_exists('auto_speak', $raw)) {
            $patch['auto_speak'] = (bool) $raw['auto_speak'];
        }

        if (array_key_exists('provider_key', $raw)) {
            $providerKey = (string) $raw['provider_key'];

            if (! (new ProviderRegistry)->has($providerKey)) {
                throw new InvalidArgumentException('Unknown provider_key value.');
            }

            $patch['provider_key'] = $providerKey;
        }

        if (array_key_exists('voice', $raw)) {
            $voice = trim((string) $raw['voice']);
            $patch['voice'] = $voice === '' ? null : mb_substr($voice, 0, 128);
        }

        if (array_key_exists('caption', $raw)) {
            $caption = (string) $raw['caption'];

            if (! in_array($caption, [TtsSettings::CAPTION_NONE, TtsSettings::CAPTION_ORIGINAL, TtsSettings::CAPTION_TRUNCATED], true)) {
                throw new InvalidArgumentException('Invalid caption value.');
            }

            $patch['caption'] = $caption;
        }

        foreach (['max_chars' => [1, 4000], 'daily_quota' => [0, 10000]] as $key => [$min, $max]) {
            if (array_key_exists($key, $raw)) {
                $patch[$key] = max($min, min($max, (int) $raw[$key]));
            }
        }

        if (array_key_exists('on_error', $raw)) {
            $onError = (string) $raw['on_error'];

            if (! in_array($onError, [TtsSettings::ERROR_MODE_SILENT, TtsSettings::ERROR_MODE_EMOJI, TtsSettings::ERROR_MODE_MESSAGE, TtsSettings::ERROR_MODE_AUTO], true)) {
                throw new InvalidArgumentException('Invalid on_error value.');
            }

            $patch['on_error'] = $onError;
        }

        return $patch;
    }

    /**
     * The default provider is keyless self-hosted edge-tts, so the module is
     * operational out of the box — needs_setup never blocks the web surface.
     */
    public function isConfigured(array $settings): bool
    {
        return true;
    }

    /** @return list<array{value: string, label: string}> */
    private static function providerOptions(): array
    {
        $options = [];

        foreach ((new ProviderRegistry)->all() as $preset) {
            $options[] = ['value' => $preset->key, 'label' => $preset->name];
        }

        return $options;
    }
}
