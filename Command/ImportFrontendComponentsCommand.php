<?php

// Comwrap/EzFrontendBundle/Command/ImportFrontendComponentsCommand.php
namespace Comwrap\Bundle\ComwrapEzFrontendBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ImportFrontendComponentsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'comwrap:frontend:import';

    protected function configure()
    {
        $this->setDescription('Import a frontend components.')
             ->addArgument('component', InputArgument::REQUIRED, 'Component ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $componentId = strtolower($input->getArgument('component'));

        try{
            $frontend = $this->getApplication()->getKernel()->getContainer()->get('Comwrap\Bundle\ComwrapEzFrontendBundle\Service\FrontendHandler');
            $frontend->copy($componentId, $output);
        }catch(\Exception $e){
            $output->writeln("<error>".$e->getMessage()."</error>");
        }
    }
}