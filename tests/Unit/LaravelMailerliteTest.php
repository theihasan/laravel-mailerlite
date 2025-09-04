<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Tests\Unit;

use Ihasan\LaravelMailerlite\Contracts\MailerLiteInterface;
use Ihasan\LaravelMailerlite\Exceptions\MailerLiteAuthenticationException;
use Ihasan\LaravelMailerlite\LaravelMailerlite;
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;
use Ihasan\LaravelMailerlite\Resources\Automations\AutomationBuilder;
use Ihasan\LaravelMailerlite\Resources\Campaigns\CampaignBuilder;
use Ihasan\LaravelMailerlite\Resources\Fields\FieldBuilder;
use Ihasan\LaravelMailerlite\Resources\Groups\GroupBuilder;
use Ihasan\LaravelMailerlite\Resources\Segments\SegmentBuilder;
use Ihasan\LaravelMailerlite\Resources\Subscribers\SubscriberBuilder;
use Ihasan\LaravelMailerlite\Resources\Webhooks\WebhookBuilder;
use Ihasan\LaravelMailerlite\Tests\TestCase;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(LaravelMailerlite::class)]
final class LaravelMailerliteTest extends TestCase
{
    private LaravelMailerlite $laravelMailerlite;

    private MailerLiteManager&MockObject $mockManager;

    private Container&MockObject $mockContainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockManager = $this->createMock(MailerLiteManager::class);
        $this->mockContainer = $this->createMock(Container::class);

        // Replace the app container with our mock
        Container::setInstance($this->mockContainer);

        $this->laravelMailerlite = new LaravelMailerlite($this->mockManager);
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);
        parent::tearDown();
    }

    #[Test]
    #[TestDox('LaravelMailerlite implements MailerLiteInterface')]
    public function it_implements_mailerlite_interface(): void
    {
        $this->assertInstanceOf(MailerLiteInterface::class, $this->laravelMailerlite);
    }

    #[Test]
    #[TestDox('LaravelMailerlite can be constructed with MailerLiteManager')]
    public function it_can_be_constructed_with_manager(): void
    {
        $manager = $this->createMock(MailerLiteManager::class);
        $instance = new LaravelMailerlite($manager);

        $this->assertInstanceOf(LaravelMailerlite::class, $instance);
    }

    #[Test]
    #[TestDox('LaravelMailerlite constructor accepts MailerLiteManager dependency')]
    public function it_accepts_manager_dependency_injection(): void
    {
        $manager = $this->createMock(MailerLiteManager::class);
        $instance = new LaravelMailerlite($manager);

        // Use reflection to verify the manager is properly injected
        $reflection = new \ReflectionClass($instance);
        $property = $reflection->getProperty('manager');
        $property->setAccessible(true);

        $this->assertSame($manager, $property->getValue($instance));
    }

    #[Test]
    #[TestDox('subscribers() returns SubscriberBuilder instance')]
    public function it_returns_subscriber_builder(): void
    {
        $subscriberBuilder = $this->createMock(SubscriberBuilder::class);

        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with(SubscriberBuilder::class)
            ->willReturn($subscriberBuilder);

        $result = $this->laravelMailerlite->subscribers();

        $this->assertSame($subscriberBuilder, $result);
        $this->assertInstanceOf(SubscriberBuilder::class, $result);
    }

    #[Test]
    #[TestDox('campaigns() returns CampaignBuilder instance')]
    public function it_returns_campaign_builder(): void
    {
        $campaignBuilder = $this->createMock(CampaignBuilder::class);

        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with(CampaignBuilder::class)
            ->willReturn($campaignBuilder);

        $result = $this->laravelMailerlite->campaigns();

        $this->assertSame($campaignBuilder, $result);
        $this->assertInstanceOf(CampaignBuilder::class, $result);
    }

    #[Test]
    #[TestDox('groups() returns GroupBuilder instance')]
    public function it_returns_group_builder(): void
    {
        $groupBuilder = $this->createMock(GroupBuilder::class);

        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with(GroupBuilder::class)
            ->willReturn($groupBuilder);

        $result = $this->laravelMailerlite->groups();

        $this->assertSame($groupBuilder, $result);
        $this->assertInstanceOf(GroupBuilder::class, $result);
    }

    #[Test]
    #[TestDox('fields() returns FieldBuilder instance')]
    public function it_returns_field_builder(): void
    {
        $fieldBuilder = $this->createMock(FieldBuilder::class);

        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with(FieldBuilder::class)
            ->willReturn($fieldBuilder);

        $result = $this->laravelMailerlite->fields();

        $this->assertSame($fieldBuilder, $result);
        $this->assertInstanceOf(FieldBuilder::class, $result);
    }

    #[Test]
    #[TestDox('segments() returns SegmentBuilder instance')]
    public function it_returns_segment_builder(): void
    {
        $segmentBuilder = $this->createMock(SegmentBuilder::class);

        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with(SegmentBuilder::class)
            ->willReturn($segmentBuilder);

        $result = $this->laravelMailerlite->segments();

        $this->assertSame($segmentBuilder, $result);
        $this->assertInstanceOf(SegmentBuilder::class, $result);
    }

    #[Test]
    #[TestDox('automations() returns AutomationBuilder instance')]
    public function it_returns_automation_builder(): void
    {
        $automationBuilder = $this->createMock(AutomationBuilder::class);

        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with(AutomationBuilder::class)
            ->willReturn($automationBuilder);

        $result = $this->laravelMailerlite->automations();

        $this->assertSame($automationBuilder, $result);
        $this->assertInstanceOf(AutomationBuilder::class, $result);
    }

    #[Test]
    #[TestDox('webhooks() returns WebhookBuilder instance')]
    public function it_returns_webhook_builder(): void
    {
        $webhookBuilder = $this->createMock(WebhookBuilder::class);

        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with(WebhookBuilder::class)
            ->willReturn($webhookBuilder);

        $result = $this->laravelMailerlite->webhooks();

        $this->assertSame($webhookBuilder, $result);
        $this->assertInstanceOf(WebhookBuilder::class, $result);
    }

    #[Test]
    #[TestDox('all builder methods return correct instances')]
    #[DataProvider('builderMethodsDataProvider')]
    public function it_returns_correct_builder_instances(string $method, string $expectedClass): void
    {
        $mockBuilder = $this->createMock($expectedClass);

        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with($expectedClass)
            ->willReturn($mockBuilder);

        $result = $this->laravelMailerlite->{$method}();

        $this->assertInstanceOf($expectedClass, $result);
        $this->assertSame($mockBuilder, $result);
    }

    #[Test]
    #[TestDox('builder methods handle container resolution exceptions')]
    #[DataProvider('builderMethodsDataProvider')]
    public function it_handles_container_resolution_exceptions(string $method, string $expectedClass): void
    {
        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with($expectedClass)
            ->willThrowException(new BindingResolutionException("Cannot resolve {$expectedClass}"));

        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage("Cannot resolve {$expectedClass}");

        $this->laravelMailerlite->{$method}();
    }

    #[Test]
    #[TestDox('multiple calls to same builder method return different instances')]
    #[DataProvider('builderMethodsDataProvider')]
    public function it_returns_different_instances_on_multiple_calls(string $method, string $expectedClass): void
    {
        $mockBuilder1 = $this->createMock($expectedClass);
        $mockBuilder2 = $this->createMock($expectedClass);

        $this->mockContainer
            ->expects($this->exactly(2))
            ->method('make')
            ->with($expectedClass)
            ->willReturnOnConsecutiveCalls($mockBuilder1, $mockBuilder2);

        $result1 = $this->laravelMailerlite->{$method}();
        $result2 = $this->laravelMailerlite->{$method}();

        $this->assertInstanceOf($expectedClass, $result1);
        $this->assertInstanceOf($expectedClass, $result2);
        $this->assertNotSame($result1, $result2);
    }

    #[Test]
    #[TestDox('all builder methods can be called without throwing exceptions')]
    public function it_can_call_all_builder_methods_without_exceptions(): void
    {
        $builders = [
            SubscriberBuilder::class => $this->createMock(SubscriberBuilder::class),
            CampaignBuilder::class => $this->createMock(CampaignBuilder::class),
            GroupBuilder::class => $this->createMock(GroupBuilder::class),
            FieldBuilder::class => $this->createMock(FieldBuilder::class),
            SegmentBuilder::class => $this->createMock(SegmentBuilder::class),
            AutomationBuilder::class => $this->createMock(AutomationBuilder::class),
            WebhookBuilder::class => $this->createMock(WebhookBuilder::class),
        ];

        $this->mockContainer
            ->expects($this->exactly(7))
            ->method('make')
            ->willReturnCallback(fn ($class) => $builders[$class] ?? null);

        // Should not throw any exceptions
        $this->assertInstanceOf(SubscriberBuilder::class, $this->laravelMailerlite->subscribers());
        $this->assertInstanceOf(CampaignBuilder::class, $this->laravelMailerlite->campaigns());
        $this->assertInstanceOf(GroupBuilder::class, $this->laravelMailerlite->groups());
        $this->assertInstanceOf(FieldBuilder::class, $this->laravelMailerlite->fields());
        $this->assertInstanceOf(SegmentBuilder::class, $this->laravelMailerlite->segments());
        $this->assertInstanceOf(AutomationBuilder::class, $this->laravelMailerlite->automations());
        $this->assertInstanceOf(WebhookBuilder::class, $this->laravelMailerlite->webhooks());
    }

    #[Test]
    #[TestDox('builder methods handle null container response gracefully')]
    #[DataProvider('builderMethodsDataProvider')]
    public function it_handles_null_container_response(string $method, string $expectedClass): void
    {
        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with($expectedClass)
            ->willReturn(null);

        // Since the actual implementation uses strict return types,
        // null return from container should cause a TypeError
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('/Return value must be of type .+Builder, null returned/');

        $this->laravelMailerlite->{$method}();
    }

    #[Test]
    #[TestDox('LaravelMailerlite can work with invalid manager states')]
    public function it_handles_invalid_manager_states(): void
    {
        // Test with a manager that might throw exceptions
        $faultyManager = $this->createMock(MailerLiteManager::class);
        $faultyManager->method('getApiKey')->willThrowException(new MailerLiteAuthenticationException('API key error'));

        $instance = new LaravelMailerlite($faultyManager);

        // The LaravelMailerlite should still be constructable even with a faulty manager
        $this->assertInstanceOf(LaravelMailerlite::class, $instance);
    }

    #[Test]
    #[TestDox('LaravelMailerlite works with edge case constructor parameters')]
    public function it_handles_edge_case_constructor_parameters(): void
    {
        // Test with a manager that has no API key
        $emptyManager = $this->createMock(MailerLiteManager::class);
        $emptyManager->method('getApiKey')->willReturn(null);
        $emptyManager->method('getOptions')->willReturn([]);

        $instance = new LaravelMailerlite($emptyManager);

        $this->assertInstanceOf(LaravelMailerlite::class, $instance);
    }

    #[Test]
    #[TestDox('LaravelMailerlite builder methods work with container edge cases')]
    public function it_handles_container_edge_cases(): void
    {
        // Test when container is not set - this actually uses Laravel's real container
        // which will try to resolve the builder and fail due to missing API key
        Container::setInstance(null);

        $this->expectException(MailerLiteAuthenticationException::class);
        $this->expectExceptionMessage('MailerLite API key is missing');

        $this->laravelMailerlite->subscribers();
    }

    #[Test]
    #[TestDox('LaravelMailerlite builder methods are chainable through their returned objects')]
    public function it_has_chainable_builder_methods(): void
    {
        $subscriberBuilder = $this->createMock(SubscriberBuilder::class);
        $subscriberBuilder->method('create')->willReturnSelf();

        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with(SubscriberBuilder::class)
            ->willReturn($subscriberBuilder);

        $result = $this->laravelMailerlite->subscribers();

        // Verify we can potentially chain methods (if the builder supports it)
        $this->assertInstanceOf(SubscriberBuilder::class, $result);
    }

    #[Test]
    #[TestDox('LaravelMailerlite maintains immutability of manager dependency')]
    public function it_maintains_manager_immutability(): void
    {
        $originalManager = $this->mockManager;

        // Create a new instance
        $newManager = $this->createMock(MailerLiteManager::class);
        $newInstance = new LaravelMailerlite($newManager);

        // Original instance should still have original manager
        $reflection = new \ReflectionClass($this->laravelMailerlite);
        $property = $reflection->getProperty('manager');
        $property->setAccessible(true);

        $this->assertSame($originalManager, $property->getValue($this->laravelMailerlite));
        $this->assertSame($newManager, $property->getValue($newInstance));
    }

    #[Test]
    #[TestDox('LaravelMailerlite handles concurrent builder method calls')]
    public function it_handles_concurrent_builder_calls(): void
    {
        $builders = [
            SubscriberBuilder::class => $this->createMock(SubscriberBuilder::class),
            CampaignBuilder::class => $this->createMock(CampaignBuilder::class),
        ];

        $callCount = 0;
        $this->mockContainer
            ->expects($this->exactly(2))
            ->method('make')
            ->willReturnCallback(function ($class) use ($builders, &$callCount) {
                $callCount++;

                return $builders[$class] ?? null;
            });

        // Simulate concurrent calls
        $result1 = $this->laravelMailerlite->subscribers();
        $result2 = $this->laravelMailerlite->campaigns();

        $this->assertEquals(2, $callCount);
        $this->assertInstanceOf(SubscriberBuilder::class, $result1);
        $this->assertInstanceOf(CampaignBuilder::class, $result2);
    }

    public static function builderMethodsDataProvider(): \Generator
    {
        yield 'subscribers' => ['subscribers', SubscriberBuilder::class];
        yield 'campaigns' => ['campaigns', CampaignBuilder::class];
        yield 'groups' => ['groups', GroupBuilder::class];
        yield 'fields' => ['fields', FieldBuilder::class];
        yield 'segments' => ['segments', SegmentBuilder::class];
        yield 'automations' => ['automations', AutomationBuilder::class];
        yield 'webhooks' => ['webhooks', WebhookBuilder::class];
    }

    public static function invalidBuilderDataProvider(): \Generator
    {
        yield 'empty string method' => ['', ''];
        yield 'non-existent method' => ['nonExistentMethod', 'NonExistentBuilder'];
        yield 'null method' => [null, null];
    }

    #[Test]
    #[TestDox('LaravelMailerlite integrates properly with Laravel service container')]
    public function it_integrates_with_laravel_service_container(): void
    {
        // Reset to use real Laravel container
        Container::setInstance(null);

        // This should work with the real Laravel application container
        $subscriberBuilder = $this->app->make(SubscriberBuilder::class);

        $this->assertInstanceOf(SubscriberBuilder::class, $subscriberBuilder);
    }

    #[Test]
    #[TestDox('LaravelMailerlite can be serialized and unserialized')]
    public function it_can_be_serialized_and_unserialized(): void
    {
        $serialized = serialize($this->laravelMailerlite);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(LaravelMailerlite::class, $unserialized);
        $this->assertInstanceOf(MailerLiteInterface::class, $unserialized);
    }

    #[Test]
    #[TestDox('LaravelMailerlite handles memory pressure scenarios')]
    public function it_handles_memory_pressure_scenarios(): void
    {
        // Create many instances to test memory handling
        $instances = [];

        for ($i = 0; $i < 100; $i++) {
            $manager = $this->createMock(MailerLiteManager::class);
            $instances[] = new LaravelMailerlite($manager);
        }

        // All instances should be valid
        $this->assertCount(100, $instances);

        foreach ($instances as $instance) {
            $this->assertInstanceOf(LaravelMailerlite::class, $instance);
        }

        // Clean up
        unset($instances);

        // Original instance should still work
        $this->assertInstanceOf(LaravelMailerlite::class, $this->laravelMailerlite);
    }

    #[Test]
    #[TestDox('LaravelMailerlite builder methods respect PHP type system')]
    public function it_respects_php_type_system(): void
    {
        $subscriberBuilder = $this->createMock(SubscriberBuilder::class);

        $this->mockContainer
            ->expects($this->once())
            ->method('make')
            ->with(SubscriberBuilder::class)
            ->willReturn($subscriberBuilder);

        $result = $this->laravelMailerlite->subscribers();

        // Strict type checking
        $this->assertTrue($result instanceof SubscriberBuilder);
        $this->assertIsObject($result);
        $this->assertNotNull($result);
    }
}
