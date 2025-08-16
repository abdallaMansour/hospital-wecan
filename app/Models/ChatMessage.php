<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = ['chat_room_id', 'user_id', 'message', 'message_type', 'attachment_path', 'is_read'];
    protected static function booted()
    {
        static::saving(function (ChatMessage $model) {
            // لازم يكون فيه يا رسالة يا مرفق
            if (empty($model->message) && empty($model->attachment_path)) {
                throw new \InvalidArgumentException(__('Either message or attachment must be provided.'));
            }

            // لو لسه متحددتش، احسبها تلقائيًا
            if (empty($model->message_type)) {
                $model->message_type = $model->attachment_path
                    ? $model->guessTypeFromExtension($model->attachment_path)
                    : 'text';
            }
        });
    }

    // لما يتغير المرفق، حدّد النوع فورًا
    public function setAttachmentPathAttribute($value): void
    {
        // لو في يوم اتفعّل multiple خلي بالك
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        $this->attributes['attachment_path'] = $value;

        if (!empty($value)) {
            $this->attributes['message_type'] = $this->guessTypeFromExtension($value);
        } elseif (!empty($this->attributes['message'])) {
            $this->attributes['message_type'] = 'text';
        }
    }

    // لو اتحطت رسالة من غير مرفق، خليه text
    public function setMessageAttribute($value): void
    {
        $this->attributes['message'] = $value;

        if (!empty($value) && empty($this->attributes['attachment_path'])) {
            $this->attributes['message_type'] = 'text';
        }
    }

    public function guessTypeFromExtension(?string $path): string
    {
        if (!$path) return 'text';

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $image = ['jpg','jpeg','png','gif','bmp','webp','svg'];
        $video = ['mp4','avi','mov','wmv','flv','webm','mkv'];

        if (in_array($ext, $image, true))  return 'image';
        if (in_array($ext, $video, true))  return 'video';
        return 'document';
    }
    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}