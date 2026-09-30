<?php

namespace App\Services;

interface NilaCatalogSource
{
    /**
     * Return normalized product records from the real Nila/Holoo adapter.
     *
     * Transport, authentication, endpoint paths and payload mapping belong
     * to the adapter implementation, not the catalog importer.
     */
    public function fetchProducts(): iterable;
}
