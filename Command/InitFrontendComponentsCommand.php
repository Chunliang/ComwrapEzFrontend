<?php

// Comwrap/EzFrontendBundle/Command/InitFrontendComponentsCommand.php
namespace Comwrap\Bundle\ComwrapEzFrontendBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitFrontendComponentsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'comwrap:frontend:init';

    protected function configure()
    {
        $this->setDescription('Init encore webpack configs for frontend css and javascript files.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try{
            $frontend = $this->getApplication()->getKernel()->getContainer()->get('Comwrap\Bundle\ComwrapEzFrontendBundle\Service\FrontendHandler');
            $frontend->init($output);
        }catch(\Exception $e){
            $output->writeln("\n<error>".$e->getMessage()."</error>\n");
        }
    }
}