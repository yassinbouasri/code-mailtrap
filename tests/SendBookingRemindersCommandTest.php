<?php

namespace App\Tests;

use App\Factory\BookingFactory;
use App\Factory\CustomerFactory;
use App\Factory\TripFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Console\Test\InteractsWithConsole;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Mailer\Test\InteractsWithMailer;
use Zenstruck\Mailer\Test\TestEmail;

class SendBookingRemindersCommandTest extends KernelTestCase
{
    use ResetDatabase, Factories, InteractsWithMailer, InteractsWithConsole;

    public function testNoRemindersSent()
    {
        $this->executeConsoleCommand('app:send-booking-reminders')
            ->assertSuccessful()
            ->assertOutputContains('Sent 0 booking reminders');
    }

    public function testRemindersSent()
    {
        $booking = BookingFactory::createOne([
            'trip' => TripFactory::new([
                'name' => 'Visit Mars',
                'slug' => 'iss',
            ]),
            'customer' => CustomerFactory::new([
                'email' => 'john@doe.com',
            ]),
            'date' => new \DateTimeImmutable('+4 days'),
        ]);

        $this->assertNull($booking->getReminderSentAt());

        $this->executeConsoleCommand('app:send-booking-reminders')
            ->assertSuccessful()
            ->assertOutputContains('Sent 1 booking reminders');

        $this->mailer()
             ->assertSentEmailCount(1)
             ->assertEmailSentTo('john@doe.com', function (TestEmail $email) {
                 $email
                     ->assertsubject('Booking Reminder for Visit Mars')
                     ->assertContains('Visit Mars')
                     ->assertContains('/booking/'.BookingFactory::first()->getUid());
             });

        $this->assertNotNull($booking->getReminderSentAt());
    }
}
