<?php

namespace App\Services\Traits;


trait HandlesTitleId
{
    /**
     * @param string $provider
     * @param string $type
     * @param string|integer $id
     * @return string
     */
    public function encodeId($provider, $type, $id)
    {
        return base64_encode("$provider|$type|$id");
    }

    public function decodeId($id)
    {
        $id = base64_decode($id);
        $parts = explode('|', $id);
        if (count($parts) === 3) {
            return ['provider' => $parts[0], 'type' => $parts[1], 'id' => $parts[2]];
        } else {
            return ['provider' => null, 'type' => null, 'id' => null];
        }
    }
}