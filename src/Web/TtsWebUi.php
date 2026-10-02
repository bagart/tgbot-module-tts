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
            title: 't:tts.title',
            icon: '🔊',
            kind: UiKind::Setting,
            minAudience: UiAudience::Admin,
            description: 't:tts.description',
            entry: UiEntry::schema([
                UiGroup::of('speech', 't:tts.group.speech', [
                    UiField::bool('auto_speak', 't:tts.field.auto_speak', default: false),
                    UiField::enum('provider_key', 't:tts.field.provider_key', options: self::providerOptions(), default: 'edge-tts'),
                    new UiField('voice', 't:tts.field.voice', UiFieldType::String, default: '', extra: ['maxLength' => 128], help: 't:tts.help.voice'),
                    UiField::enum('caption', 't:tts.field.caption', options: [
                        ['value' => TtsSettings::CAPTION_NONE, 'label' => 't:tts.option.caption_none'],
                        ['value' => TtsSettings::CAPTION_ORIGINAL, 'label' => 't:tts.option.caption_original'],
                        ['value' => TtsSettings::CAPTION_TRUNCATED, 'label' => 't:tts.option.caption_truncated'],
                    ], default: TtsSettings::CAPTION_ORIGINAL),
                ]),
                UiGroup::of('limits', 't:tts.group.limits', [
                    new UiField('max_chars', 't:tts.field.max_chars', UiFieldType::Int, default: TtsSettings::DEFAULT_MAX_CHARS, extra: ['min' => 1, 'max' => 4000]),
                    new UiField('daily_quota', 't:tts.field.daily_quota', UiFieldType::Int, default: TtsSettings::DEFAULT_DAILY_QUOTA, extra: ['min' => 0, 'max' => 10000]),
                    UiField::enum('on_error', 't:tts.field.on_error', options: [
                        ['value' => TtsSettings::ERROR_MODE_AUTO, 'label' => 't:tts.option.error_auto'],
                        ['value' => TtsSettings::ERROR_MODE_SILENT, 'label' => 't:tts.option.error_silent'],
                        ['value' => TtsSettings::ERROR_MODE_EMOJI, 'label' => 't:tts.option.error_emoji'],
                        ['value' => TtsSettings::ERROR_MODE_MESSAGE, 'label' => 't:tts.option.error_message'],
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
            'en' => [
                'title' => 'Text → Voice',
                'description' => 'Speak text with TTS voices',
                'group.speech' => 'Speech',
                'field.auto_speak' => 'Auto-speak my messages',
                'field.provider_key' => 'Provider',
                'field.voice' => 'Voice',
                'help.voice' => 'Provider voice id, e.g. ru-RU-DmitryNeural; empty = provider default',
                'field.caption' => 'Caption',
                'option.caption_none' => 'No caption',
                'option.caption_original' => 'Original text',
                'option.caption_truncated' => 'Truncated text',
                'group.limits' => 'Limits and errors',
                'field.max_chars' => 'Max chars per voice message',
                'field.daily_quota' => 'Daily quota per chat',
                'field.on_error' => 'On error',
                'option.error_auto' => 'Auto (emoji in groups, message in private)',
                'option.error_silent' => 'Silent',
                'option.error_emoji' => 'Emoji reaction',
                'option.error_message' => 'Error message',
            ],
            'ru' => [
                'title' => 'Текст → голос',
                'description' => 'Озвучка текста TTS-голосами',
                'group.speech' => 'Речь',
                'field.auto_speak' => 'Озвучивать мои сообщения',
                'field.provider_key' => 'Провайдер',
                'field.voice' => 'Голос',
                'help.voice' => 'ID голоса провайдера, напр. ru-RU-DmitryNeural; пусто = голос по умолчанию',
                'field.caption' => 'Подпись',
                'option.caption_none' => 'Без подписи',
                'option.caption_original' => 'Оригинальный текст',
                'option.caption_truncated' => 'Усечённый текст',
                'group.limits' => 'Лимиты и ошибки',
                'field.max_chars' => 'Макс. символов в голосовом',
                'field.daily_quota' => 'Дневная квота на чат',
                'field.on_error' => 'При ошибке',
                'option.error_auto' => 'Авто (эмодзи в группах, сообщение в личке)',
                'option.error_silent' => 'Тихо',
                'option.error_emoji' => 'Реакция эмодзи',
                'option.error_message' => 'Сообщение об ошибке',
            ],
            'fr' => [
                'title' => 'Texte → Voix',
                'description' => 'Lire le texte à voix haute via TTS',
                'group.speech' => 'Parole',
                'field.auto_speak' => 'Lire automatiquement mes messages',
                'field.provider_key' => 'Fournisseur',
                'field.voice' => 'Voix',
                'help.voice' => 'ID voix du fournisseur, ex. ru-RU-DmitryNeural; vide = voix par défaut',
                'field.caption' => 'Légende',
                'option.caption_none' => 'Pas de légende',
                'option.caption_original' => 'Texte original',
                'option.caption_truncated' => 'Texte tronqué',
                'group.limits' => 'Limites et erreurs',
                'field.max_chars' => 'Caractères max. par message vocal',
                'field.daily_quota' => 'Quota quotidien par chat',
                'field.on_error' => "En cas d'erreur",
                'option.error_auto' => 'Auto (emoji en groupes, message en privé)',
                'option.error_silent' => 'Silencieux',
                'option.error_emoji' => 'Réaction emoji',
                'option.error_message' => "Message d'erreur",
            ],
            'es' => [
                'title' => 'Texto → Voz',
                'description' => 'Leer texto en voz alta con TTS',
                'group.speech' => 'Habla',
                'field.auto_speak' => 'Leer automáticamente mis mensajes',
                'field.provider_key' => 'Proveedor',
                'field.voice' => 'Voz',
                'help.voice' => 'ID de voz del proveedor, ej. ru-RU-DmitryNeural; vacío = voz predeterminada',
                'field.caption' => 'Subtítulo',
                'option.caption_none' => 'Sin subtítulo',
                'option.caption_original' => 'Texto original',
                'option.caption_truncated' => 'Texto truncado',
                'group.limits' => 'Límites y errores',
                'field.max_chars' => 'Máx. caracteres por mensaje de voz',
                'field.daily_quota' => 'Cuota diaria por chat',
                'field.on_error' => 'En caso de error',
                'option.error_auto' => 'Auto (emoji en grupos, mensaje en privado)',
                'option.error_silent' => 'Silencioso',
                'option.error_emoji' => 'Reacción emoji',
                'option.error_message' => 'Mensaje de error',
            ],
            'zh' => [
                'title' => '文字 → 语音',
                'description' => '使用TTS语音朗读文字',
                'group.speech' => '语音',
                'field.auto_speak' => '自动朗读我的消息',
                'field.provider_key' => '服务商',
                'field.voice' => '语音',
                'help.voice' => '服务商语音ID，如 ru-RU-DmitryNeural；空 = 默认语音',
                'field.caption' => '标题',
                'option.caption_none' => '无标题',
                'option.caption_original' => '原始文本',
                'option.caption_truncated' => '截断文本',
                'group.limits' => '限制和错误',
                'field.max_chars' => '语音消息最大字符数',
                'field.daily_quota' => '每聊每日配额',
                'field.on_error' => '出错时',
                'option.error_auto' => '自动（群组中emoji，私聊中消息）',
                'option.error_silent' => '静默',
                'option.error_emoji' => 'emoji反应',
                'option.error_message' => '错误消息',
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

            if (! (new ProviderRegistry())->has($providerKey)) {
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

        foreach ((new ProviderRegistry())->all() as $preset) {
            $options[] = ['value' => $preset->key, 'label' => $preset->name];
        }

        return $options;
    }
}
