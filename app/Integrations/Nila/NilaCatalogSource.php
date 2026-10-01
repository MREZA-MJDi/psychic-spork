<?php

namespace App\Integrations\Nila;

use App\Integrations\Nila\Data\ExternalProductData;

interface NilaCatalogSource
{
    /**
     * The adapter owns HTTP/auth/endpoint/payload details.
     * The importer only consumes normalized DTOs.
     *
     * @return iterable<ExternalProductData>
     */
    public function fetchProducts(): iterable;
}
