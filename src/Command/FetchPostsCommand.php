<?php


namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Manager\PostManager;
use Throwable;

/**
 * Class FetchPostsCommand
 * @package App\Command
 */
class FetchPostsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:fetch-posts';
    /**
     * @var PostManager
     */
    private $postManager;

    /**
     * FetchPostsCommand constructor.
     * @param PostManager $postManager
     */
    public function __construct(PostManager $postManager){
        $this->postManager = $postManager;
        parent::__construct();
    }

    /**
     *
     */
    public function configure()
    {
        $this->setDescription('Fetches current state of posts data from API.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $dbPosts = $this->postManager->fetchPostDataFromDatabase();
            $apiPosts = $this->postManager->fetchPostDataFromAPI();
        } catch (Throwable $e) {
            $output->writeLn('There was error in getting data');
            return Command::FAILURE;
        }

        $synchronizePostData = $this->postManager->synchronizePostData($apiPosts, $dbPosts);

        if ($synchronizePostData > 0){
            $output->writeLn('There was error in synchronizing '.$synchronizePostData.' posts');
            return Command::FAILURE;
        }

        $output->writeLn('Posts successfully synchronized');
        return Command::SUCCESS;
    }
}