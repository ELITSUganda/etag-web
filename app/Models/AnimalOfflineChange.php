<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalOfflineChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'local_id',
        'animal_ids',
        'change_type',
        'change_data',
        'changed_by_user_id',
        'processing_by_user_id',
        'status',
        'error_message',
        'timestamp',
        'processed_at',
    ];

    /**
     * Get animal IDs as array
     */
    public function getAnimalIdsArray()
    {
        return json_decode($this->animal_ids, true) ?? [];
    }

    /**
     * Get change data as array
     */
    public function getChangeDataArray()
    {
        return json_decode($this->change_data, true) ?? [];
    }

    /**
     * Set animal IDs from array
     */
    public function setAnimalIdsArray($ids)
    {
        $this->animal_ids = json_encode($ids);
    }

    /**
     * Set change data from array
     */
    public function setChangeDataArray($data)
    {
        $this->change_data = json_encode($data);
    }

    /**
     * Get user who made the change
     */
    public function changedBy()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'changed_by_user_id');
    }

    /**
     * Get user processing the change
     */
    public function processingBy()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'processing_by_user_id');
    }

    /**
     * Scope for pending changes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed changes
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for synced changes
     */
    public function scopeSynced($query)
    {
        return $query->where('status', 'synced');
    }

    /**
     * Scope for user's changes
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('changed_by_user_id', $userId);
    }
}
