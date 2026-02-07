<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

// See README.md
class MetaModel extends Model
{
    protected $casts = [
        'metadata' => 'array',
    ];

    // Subclasses may override those variables
    protected array $metaDeny = [];     // default
    protected array $metaAllow = [];     // default
    protected string $metaColumn = 'metadata';

    /**
     * @throws Exception
     */
    public function setAttribute($key, $value)
    {
        $serialized = $this->isSerialized($key);

        // If it's a "real" attribute/cast/mutator, keep default behavior
        if (!$serialized) {
            return parent::setAttribute($key, $value);
        }

        // Otherwise store in metadata JSON
        $meta = parent::getAttribute($this->metaColumn) ?? [];
        $meta[$key] = $value;

        // Important: set the underlying JSON column so Eloquent marks it dirty
        return parent::setAttribute($this->metaColumn, $meta);
    }

    /**
     * @throws Exception
     */
    public function getAttribute($key)
    {
        $serialized = $this->isSerialized($key);

        if (!$serialized) {
            return parent::getAttribute($key);
        }

        // Fallback: read from metadata JSON
        $meta = parent::getAttribute($this->metaColumn) ?? [];
        return $meta[$key] ?? null;
    }


    public function unsetAttribute($key): void
    {
        $meta = parent::getAttribute($this->metaColumn) ?? [];
        unset($meta[$key]);
        parent::setAttribute($this->metaColumn, $meta);
    }

    /**
     * @param string $key
     * @return bool
     * @throws Exception
     */
    protected function isSerialized(string $key): bool
    {
        $usingMetaAllow = count($this->metaAllow) > 0;
        $usingMetaDeny = count($this->metaDeny) > 0;
        $inMetaAllow = in_array($key, $this->metaAllow);
        $inMetaDeny = in_array($key, $this->metaDeny);
        $specialCase = $key === $this->metaColumn ||
            $this->hasSetMutator($key) ||
            $this->hasAttributeMutator($key) ||
            $this->isClassCastable($key) ||
            array_key_exists($key, $this->attributes) ||
            $this->hasCast($key);

        if ($usingMetaAllow && $inMetaAllow) {
            $serialized = true;
        } else if ($specialCase) {
            $serialized = false;
        } else if ($usingMetaDeny && !$inMetaDeny) {
            $serialized = true;
        } else {
            $serialized = false;
        }

        return $serialized;
    }
}
