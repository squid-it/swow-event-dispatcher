<?php

declare(strict_types=1);

namespace Event\Listener;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SquidIT\Event\Event\EventInterface;
use SquidIT\Event\Listener\AbstractCoroutineListener;
use SquidIT\Tests\Event\Classes\Events\Payment\PaymentReceivedEvent;
use Swow\Channel;
use Swow\Coroutine;

class AbstractCoroutineListenerTest extends TestCase
{
    public const string PAYMENT_NODE = 'node01';

    public function testActionHandlerIsRunInsideOfACoroutineContext(): void
    {
        $channel              = new Channel(1);
        $event1               = new PaymentReceivedEvent();
        $event2               = new PaymentReceivedEvent();
        $firstCoroutineDelay  = 10;
        $secondCoroutineDelay = 5;

        $setPaymentNodeListener1 = $this->getPaymentNodeListener($channel, $firstCoroutineDelay);
        $setPaymentNodeListener2 = $this->getPaymentNodeListener($channel, $secondCoroutineDelay);

        // test no coroutine active (getAll should return the main coroutine context only)
        self::assertCount(1, Coroutine::getAll());

        // invoke listeners (this will start new coroutines that will sleep for 10ms and 5ms)
        $setPaymentNodeListener1($event1);
        $setPaymentNodeListener2($event2);
        self::assertGreaterThan(2, Coroutine::getAll()); // we should have 3 coroutines active

        // get event back from the coroutine channel
        $firstReturnedEvent  = $channel->pop();
        $secondReturnedEvent = $channel->pop();
        $channel->close();

        msleep(1); // $channel pop switches context, let's wait 1 ms so our coroutine listeners can complete

        // the first returned event should be the 2 second fired of coroutine
        self::assertSame(
            sprintf('%s.%s', self::PAYMENT_NODE, $secondCoroutineDelay),
            $firstReturnedEvent->paymentNode
        );

        self::assertSame(
            sprintf('%s.%s', self::PAYMENT_NODE, $firstCoroutineDelay),
            $secondReturnedEvent->paymentNode
        );

        // All beside our main coroutine context should be active
        self::assertCount(1, Coroutine::getAll());
    }

    private function getPaymentNodeListener(Channel $channel, int $mSleep = 10): AbstractCoroutineListener
    {
        return new class($channel, $mSleep) extends AbstractCoroutineListener {
            public function __construct(
                private readonly Channel $channel,
                private readonly int $mSleep,
            ) {}

            public function coroutineExecute(PaymentReceivedEvent|EventInterface $event): void
            {
                if ($event instanceof PaymentReceivedEvent === false) {
                    throw new RuntimeException('Expected instance of PaymentReceivedEvent');
                }

                msleep($this->mSleep);

                $event->paymentNode = sprintf('%s.%s', AbstractCoroutineListenerTest::PAYMENT_NODE, $this->mSleep);
                $this->channel->push($event);
            }
        };
    }
}
