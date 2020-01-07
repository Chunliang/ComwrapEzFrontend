<?php

// Comwrap/EzFrontendBundle/Command/CompareFrontendComponentsCommand.php
namespace Comwrap\Bundle\ComwrapEzFrontendBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CompareFrontendComponentsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'comwrap:frontend:compare';

    protected function configure()
    {
        $this->setDescription('Compare a frontend component with backend component.')
             ->addArgument('component', InputArgument::REQUIRED, 'Component ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $componentId = strtolower($input->getArgument('component'));

        try{
            $frontend = $this->getApplication()->getKernel()->getContainer()->get('Comwrap\Bundle\ComwrapEzFrontendBundle\Service\FrontendHandler');
            $frontend->compare($componentId, $output);
        }catch(\Exception $e){
            $output->writeln("<error>".$e->getMessage()."</error>");
        }
    }
}