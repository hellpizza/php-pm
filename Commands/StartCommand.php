<?php

namespace PHPPM\Commands;

use PHPPM\ProcessManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('start')
            ->addArgument('working-directory', InputArgument::OPTIONAL, 'The root of your appplication.', './')
            ->addOption('bridge', null, InputOption::VALUE_OPTIONAL, 'The bridge we use to convert a ReactPHP-Request to your target framework.', 'symfony')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'Load-Balancer port. Default is 8080', 8080)
            ->addOption('workers', null, InputOption::VALUE_OPTIONAL, 'Worker count. Default is 8. Should be minimum equal to the number of CPU cores.', 8)
            ->addOption('app-env', null, InputOption::VALUE_OPTIONAL, 'The environment setting your app will be passed', 'prod')
            ->setDescription('Starts the server')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($workingDir = $input->getArgument('working-directory')) {
            chdir($workingDir);
        }

        $config = [];
        if (file_exists('./ppm.json')) {
            $config = json_decode(file_get_contents('./ppm.json'), true);
        }

        $bridge  = isset($config['bridge'])  ? $config['bridge']  : $input->getOption('bridge');
        $port    = isset($config['port'])    ? $config['port']    : (int) $input->getOption('port');
        $workers = isset($config['workers']) ? $config['workers'] : (int) $input->getOption('workers');
        $appenv  = isset($config['app-env']) ? $config['app-env'] : $input->getOption('app-env');

        $handler = new ProcessManager($port, $workers);

        $handler->setBridge($bridge);
        $handler->setAppEnv($appenv);

        $handler->run();
    }

}
