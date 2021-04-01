<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

abstract class BaseModel extends Model
{
    protected $messages = [];
    protected $customAttributes = [];

    protected static function boot()
    {
        parent::boot();

        /**
         * Methods are part of HasEvents trait:
         *
         * retrieved : after a record has been retrieved.
         * creating : before a record has been created.
         * created : after a record has been created.
         * updating : before a record is updated.
         * updated : after a record has been updated.
         * saving : before a record is saved (either created or updated).
         * saved : after a record has been saved (either created or updated).
         * deleting : before a record is deleted or soft-deleted.
         * deleted : after a record has been deleted or soft-deleted.
         * restoring : before a soft-deleted record is going to be restored.
         * restored : after a soft-deleted record has been restored.
         *
         */

        static::saving(function ($model) {
            // Validating
            $model->checkValidation();
        });

        static::updating(function ($model) {
            // Authorizing
            $model->checkAuthorization();
        });

        static::deleting(function ($model) {
            // Authorizing
            $model->checkAuthorization();
        });
    }

    abstract protected function authorize();
    abstract protected function rules();

    private function checkValidation()
    {
        $validator = Validator::make($this->getDirty(), $this->rules(), $this->messages, $this->customAttributes);

        if ($validator->fails()) {

            throw new Exception($validator->errors()->first(), 422);
        }
    }

    private function checkAuthorization()
    {
        // if user is owner
        if ($this->authorize()) {
            return;
        }

        // if the user is admin or owner allow operation
        if (auth()->check() && auth()->user()->is('administrator')) {
            return;
        }

        // else
        throw new HttpResponseException(
            response(['message' => 'Not authorized!'], 422)
        );
    }
}
