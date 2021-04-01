<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Post extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'body',
        'category_id',
        'image',
        'url'
    ];

    /**
     * Validation rules
     */
    public function rules(): array
    {
        $create = [
            'title' => 'required|string|min:3|max:200',
            'body' => 'required|string|min:3|max:10000',
            'category_id' => 'required',
            'image' => 'sometimes|string|nullable'
        ];

        $update = [
            'title' => 'sometimes|string|min:3|max:200',
            'body' => 'sometimes|string|min:3|max:10000',
            'category_id' => 'sometimes|integer',
            'image' => 'sometimes|string|nullable'
        ];

        return isset($this->id) ? $update : $create;
    }

    public function authorize()
    {
        return $this->author->id == auth()->id();
    }

    /**
     *
     * Relationship methods
     *
     */

    /**
     * Get the author model.
     */
    public function author()
    {
        return $this->belongsTo(User::class);
    }
}
