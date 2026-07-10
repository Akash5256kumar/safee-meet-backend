<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberSearchCount extends Model
{
    protected $table = 'member_search_counts';

    protected $fillable = [
        'searcher_id', 'member_id', 'search_count', 'last_searched_at',
    ];

    protected function casts(): array
    {
        return [
            // Keep these string-shaped in API responses regardless of the
            // underlying users.id column type (char ULID or bigint) — the
            // mobile client expects string ids everywhere.
            'id' => 'string',
            'searcher_id' => 'string',
            'member_id' => 'string',
            'last_searched_at' => 'datetime',
        ];
    }
}
