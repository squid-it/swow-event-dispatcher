<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Unit\Event\Listener;

use PHPUnit\Framework\TestCase;
use SquidIT\Event\Event\EventInterface;
use SquidIT\Event\Listener\ListenerInfo;
use SquidIT\Event\Listener\ListenerInterface;
use SquidIT\Tests\Event\Classes\Events\Payment\PaymentPaidEvent;
use SquidIT\Tests\Event\Classes\Events\SignIn\CheckNewSignInLocationListener;
use SquidIT\Tests\Event\Classes\Events\SignIn\InvalidInvokeMethodListener;
use SquidIT\Tests\Event\Classes\Events\SignIn\LogLastSignInTimeListener;
use SquidIT\Tests\Event\Classes\Events\SignIn\LogLastSignInTimeUnSupportedListener;
use SquidIT\Tests\Event\Classes\Events\SignIn\UserSignInEvent;
use Throwable;
use UnexpectedValueException;

class ListenerInfoTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testSettingListenerInfoValuesSucceeds(): void
    {
        $eventName1  = UserSignInEvent::class;
        $listerName1 = LogLastSignInTimeListener::class;
        $priority1   = 100;

        $eventName2  = UserSignInEvent::class;
        $listerName2 = CheckNewSignInLocationListener::class;

        $listenerInfo1 = new ListenerInfo($eventName1, $listerName1, $priority1);
        $listenerInfo2 = new ListenerInfo($eventName2, $listerName2);

        self::assertSame($eventName1, $listenerInfo1->eventName);
        self::assertSame($listerName1, $listenerInfo1->listenerName);
        self::assertSame($priority1, $listenerInfo1->priority);
        self::assertFalse($listenerInfo1->useCoroutine);

        self::assertSame($eventName2, $listenerInfo2->eventName);
        self::assertSame($listerName2, $listenerInfo2->listenerName);
        self::assertSame(ListenerInfo::DEFAULT_PRIORITY, $listenerInfo2->priority);
        self::assertTrue($listenerInfo2->useCoroutine);
    }

    /**
     * @throws Throwable
     */
    public function testCreateThrowsUnexpectedValueExceptionOnNonExistingEvent(): void
    {
        /** @var class-string<object> $nonExistingClass */
        $nonExistingClass = 'NonExistingClass';

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf('"%s" class could not be found', $nonExistingClass));

        new ListenerInfo($nonExistingClass, LogLastSignInTimeListener::class);
    }

    /**
     * @throws Throwable
     */
    public function testCreateThrowsUnexpectedValueExceptionOnUnsupportedListener(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf(
            '"%s" does not implement %s',
            LogLastSignInTimeUnSupportedListener::class,
            ListenerInterface::class
        ));

        new ListenerInfo(UserSignInEvent::class, LogLastSignInTimeUnSupportedListener::class);
    }

    /**
     * @throws Throwable
     */
    public function testCreateThrowsUnexpectedValueExceptionOnInvalidInvokeMethodListener(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf(
            '"%s" __invoke() method is missing the required union type definition: [EventClassName]|%s',
            InvalidInvokeMethodListener::class,
            EventInterface::class
        ));

        new ListenerInfo(UserSignInEvent::class, InvalidInvokeMethodListener::class);
    }

    /**
     * @throws Throwable
     */
    public function testCreateThrowsUnexpectedValueExceptionOnUnsupportedEvent(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf(
            'The supplied listener "%s" is not compatible with event: %s',
            LogLastSignInTimeListener::class,
            PaymentPaidEvent::class
        ));

        new ListenerInfo(PaymentPaidEvent::class, LogLastSignInTimeListener::class);
    }
}
