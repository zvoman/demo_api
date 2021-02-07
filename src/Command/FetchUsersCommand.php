<?php


namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Manager\UserManager;

class FetchUsersCommand extends Command
{
    protected static $defaultName = 'app:fetch-users';
    private $userManager;

    public function __construct(UserManager $userManager){
        $this->userManager = $userManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Fetches current state of user data from API.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $newUsers = $this->userManager->synchronizeUserData();

        return Command::SUCCESS;
    }
}