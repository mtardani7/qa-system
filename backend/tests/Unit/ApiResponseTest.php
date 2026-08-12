<?php

namespace Tests\Unit;

use App\Support\ApiResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_response_uses_the_standard_envelope(): void
    {
        $response = ApiResponse::success(['id' => 1], 'Created.', 201);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'message' => 'Created.',
            'data' => ['id' => 1],
        ], $response->getData(true));
    }

    public function test_error_response_uses_the_standard_envelope(): void
    {
        $response = ApiResponse::error('Validation failed.', ['code' => ['The code is required.']], 422);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => ['code' => ['The code is required.']],
        ], $response->getData(true));
    }
}
