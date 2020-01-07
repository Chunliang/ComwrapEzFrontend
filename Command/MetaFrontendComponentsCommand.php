<?php

// Comwrap/EzFrontendBundle/Command/MetaFrontendComponentsCommand.php
namespace Comwrap\Bundle\ComwrapEzFrontendBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class MetaFrontendComponentsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'comwrap:frontend:meta';

    protected function configure()
    {
        $this->setDescription('Get data required by a frontend component.')
             ->addArgument('compoent', InputArgument::REQUIRED, 'Component ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $componentId = strtolower($input->getArgument('compoent'));

        try{
            $frontend = $this->getApplication()->getKernel()->getContainer()->get('Comwrap\Bundle\ComwrapEzFrontendBundle\Service\FrontendHandler');
            $frontend->meta($componentId, $output);
        }catch(\Exception $e){
            $output->writeln("<error>".$e->getMessage()."</error>");
        }
    }
}