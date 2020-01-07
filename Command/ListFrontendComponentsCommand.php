<?php

// Comwrap/EzFrontendBundle/Command/ListFrontendComponentsCommand.php
namespace Comwrap\Bundle\ComwrapEzFrontendBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListFrontendComponentsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'comwrap:frontend:ls';

    protected function configure()
    {
        $this->setDescription('List all frontend components.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try{
            $frontend = $this->getApplication()->getKernel()->getContainer()->get('Comwrap\Bundle\ComwrapEzFrontendBundle\Service\FrontendHandler');
            $frontend->list($output);
        }catch(\Exception $e){
            $output->writeln("\n<error>".$e->getMessage()."</error>\n");
        }
    }
}