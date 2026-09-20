<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    public function getIconAttribute(): string
    {
        return match (strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION))) {
            'pdf'           => '📄',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => '🖼️',
            'zip', 'rar'    => '🗜️',
            default         => '📎',
        };
    }
}
