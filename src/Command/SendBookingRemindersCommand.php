<?php

namespace App\Command;

use App\Email\BookingEmailFactory;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsCommand(
    name: 'app:send-booking-reminders',
    description: 'Send bookings reminders emails',
)]
class SendBookingRemindersCommand extends Command
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private BookingEmailFactory $emailFactory
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Send bookings reminders');
        $bookings = $this->bookingRepository->findBookingsToRemind();
        foreach ($io->progressIterate( $bookings) as $booking) {

            $this->mailer->send($this->emailFactory->createBookingReminder($booking));

            $booking->setReminderSentAt(new \DateTimeImmutable('now'));
        }
        $this->entityManager->flush();

        $io->success(sprintf('Sent %d booking reminders', count($bookings)));

        return Command::SUCCESS;
    }
}
