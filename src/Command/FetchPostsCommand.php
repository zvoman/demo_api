<?php


namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Manager\PostManager;

class FetchPostsCommand extends Command
{
    protected static $defaultName = 'app:fetch-posts';
    private $postManager;

    public function __construct(PostManager $postManager){
        $this->postManager = $postManager;
        parent::__construct();
    }

    public function configure()
    {
        $this->setDescription('Fetches current state of posts data from API.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $newPosts = $this->postManager->synchronizePostData();

        return Command::SUCCESS;
    }
}