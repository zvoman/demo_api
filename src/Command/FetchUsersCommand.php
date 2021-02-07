<?php


namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Manager\UserManager;
use Throwable;

/**
 * Class FetchUsersCommand
 * @package App\Command
 */
class FetchUsersCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:fetch-users';
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * FetchUsersCommand constructor.
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager){
        $this->userManager = $userManager;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Fetches current state of user data from API.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $dbUsers = $this->userManager->fetchUserDataFromDatabase();
            $apiUsers = $this->userManager->fetchUserDataFromAPI();
        } catch (Throwable $e) {
            $output->writeLn('There was error in getting data');
            return Command::FAILURE;
        }

        $synchronizeUserData = $this->userManager->synchronizeUserData($apiUsers, $dbUsers);

        if ($synchronizeUserData > 0){
            $output->writeLn('There was error in synchronizing '.$synchronizeUserData.' users');
            return Command::FAILURE;
        }

        $output->writeLn('Users successfully synchronized');
        return Command::SUCCESS;
    }
}