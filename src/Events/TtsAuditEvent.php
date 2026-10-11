<?php

declare(strict_types=1);

namespace BAGArt\TelegramBotTts\Events;

use BAGArt\TelegramBot\Contracts\Modules\ModuleAuditEventContract;

/**
 * Audit events dispatched by the text-to-speech module. One class per module:
 * the operation vocabulary lives in constants so the dispatch sites stay
 * typo-free and greppable, and the module registers this single class
 * through TgModuleRegistrar::auditEvent().
 */
final class TtsAuditEvent implements ModuleAuditEventContract
{
    public const string SETTINGS_CHANGED = 'tts.settings.changed';

    public const string VOICE_CHANGED = 'tts.voice.changed';

    public const string CAPTION_CHANGED = 'tts.caption.changed';

    public const string ERROR_HANDLING_CHANGED = 'tts.error_handling.changed';

    public const string PROVIDER_CHANGED = 'tts.provider.changed';

    public const string PROVIDER_CONFIG_CHANGED = 'tts.provider.config.changed';

    public const string PROVIDER_TOKEN_CREATED = 'tts.provider.token.created';

    public const string CHAT_VOICE_CHANGED = 'tts.chat.voice.changed';

    public const string NOTICE_FLAG_CHANGED = 'tts.notice.flag.changed';

    public const string AUDIO_CACHE_PURGED = 'tts.audio.cache.purged';

    /**
     * @param  array<string, mixed>|null  $oldState
     * @param  array<string, mixed>|null  $newState
     * @param  array<string, mixed>  $context  structured non-secret facts only — credentials/token values are forbidden
     */
    public function __construct(
        public readonly string $operation,
        public readonly string|int|null $botId = null,
        public readonly ?string $subjectType = null,
        public readonly string|int|null $subjectId = null,
        public readonly string|int|null $actorId = null,
        public readonly ?string $actorType = null,
        public readonly ?array $oldState = null,
        public readonly ?array $newState = null,
        public readonly array $context = [],
    ) {
    }

    public function moduleId(): string
    {
        return 'tts';
    }

    public function operation(): string
    {
        return $this->operation;
    }

    public function botId(): ?string
    {
        return $this->botId !== null ? (string) $this->botId : null;
    }

    public function subjectType(): ?string
    {
        return $this->subjectType;
    }

    public function subjectId(): ?string
    {
        return $this->subjectId !== null ? (string) $this->subjectId : null;
    }

    public function actorId(): ?string
    {
        return $this->actorId !== null ? (string) $this->actorId : null;
    }

    public function actorType(): ?string
    {
        return $this->actorType;
    }

    public function oldState(): ?array
    {
        return $this->oldState;
    }

    public function newState(): ?array
    {
        return $this->newState;
    }

    public function context(): array
    {
        return $this->context;
    }
}
