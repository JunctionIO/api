<?php

namespace Junction\Api\Test\Integration\Http;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

final class TestResponse
{
    private ?array $parsedBody = null;

    public function __construct(private readonly ResponseInterface $response) {}

    public function assertStatus(int $status): self
    {
        $actual = $this->response->getStatusCode();

        Assert::assertEquals(
            $status,
            $actual,
            "Expected status [{$status}], got [{$actual}]"
        );

        return $this;
    }

    public function assertOk(): self
    {
        return $this->assertStatus(200);
    }

    public function assertCreated(): self
    {
        return $this->assertStatus(201);
    }

    public function assertAccepted(): self
    {
        return $this->assertStatus(202);
    }

    public function assertNoContent(): self
    {
        return $this->assertStatus(204);
    }

    public function assertBadRequest(): self
    {
        return $this->assertStatus(400);
    }

    public function assertUnauthorized(): self
    {
        return $this->assertStatus(401);
    }

    public function assertNotFound(): self
    {
        return $this->assertStatus(404);
    }

    public function assertUnprocessable(): self
    {
        return $this->assertStatus(422);
    }

    public function assertHeader(string $name, string $value): self
    {
        $actual = $this->response->getHeaderLine($name);

        Assert::assertEquals(
            $value,
            $actual,
            "Failed asserting header [{$name}] has value [{$value}]"
        );

        return $this;
    }

    private function parseDotNotation(string $attribute): mixed
    {
        $data = $this->getResponseBody();

        foreach (explode('.', $attribute) as $part) {
            if (!is_array($data) || !array_key_exists($part, $data)) {
                Assert::fail("Attribute [{$attribute}] does not exist in the response");
            }

            $data = $data[$part];
        }

        return $data;
    }

    public function assertAttributeExists(string $attribute): self
    {
        $this->parseDotNotation($attribute);

        return $this;
    }

    public function assertAttributeEquals(string $attribute, mixed $value): self
    {
        $actual = $this->parseDotNotation($attribute);

        Assert::assertEquals(
            $value,
            $actual,
            "Failed asserting attribute [{$attribute}] equals the expected value"
        );

        return $this;
    }

    public function assertAttributeType(string $attribute, string $type): self
    {
        $t = gettype($this->parseDotNotation($attribute));

        $actual = match ($t) {
            'NULL'   => 'null',
            'double' => 'float',
            default  => $t
        };

        Assert::assertEquals(
            $type,
            $actual,
            "Failed asserting attribute [{$attribute}] is type [{$type}]"
        );

        return $this;
    }

    /**
     * @param array<string, mixed> $expected
     */
    public function assertBodyEquals(array $expected): self
    {
        Assert::assertEquals(
            $expected,
            $this->getResponseBody(),
            'Failed asserting the response body matches the expected value'
        );

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getResponseBody(): array
    {
        if (null !== $this->parsedBody) {
            return $this->parsedBody;
        }

        $decoded = json_decode((string) $this->response->getBody(), true);

        return $this->parsedBody = is_array($decoded) ? $decoded : [];
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
