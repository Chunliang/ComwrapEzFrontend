<?php

namespace Comwrap\Bundle\ComwrapEzFrontendBundle\Service;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\DependencyInjection\Container;

class FrontendHandler
{
    /**
     * @var Container
     *
     */
    protected $container;
    private $frontendPath;
    private $componentsSource;
    private $componentsDestination;
    
    const messages = [
        'ERROR_FRONTEND_CONFIG_NOT_FOUND' => "Frontend config was not found, please add the following settings in the 'config.yml' and set their values in 'parameters.yml':\n\ncomwrap_ez_frontend:\n    frontend:\n        source: '%frontend_source_path%'\n        destination: '%backend_components_path%'\n",
        'ERROR_FRONTEND_DIR_NOT_FOUND' => "Frontend Components Directory Not Found: ",
        'ERROR_FRONTEND_COMP_DIR_NOT_FOUND' => "Frontend Components Directory Not Found: ",
        'ERROR_FRONTEND_COMP_DIR_EMPTY' => "Frontend Components Directory Is Empty: ",
        'ERROR_BACKEND_CONFIG_NOT_FOUND' => "Backend config was not found, please add the following settings in the 'config.yml' and set their values in 'parameters.yml':\n\ncomwrap_ez_frontend:\n    frontend:\n        source: '%frontend_source_path%'\n        destination: '%backend_components_path%'\n",
        'ERROR_BACKEND_DIR_NOT_EXISTS_OR_WRITABLE' => "Backend Components Directory Not Exists Or Not Writable: ",
    ];

    const defaultNodePackages = [
        '@symfony/webpack-encore' => '^0.28.2',
        'webpack-notifier' => '^1.6.0',
        'node-sass' => '^4.13.0',
        'sass-loader' => '^7.0.1'
    ];

    public function __construct(Container $container, $path, $to)
    {

        if(!$path || $path === '' ){
            throw new \Exception(self::messages['ERROR_FRONTEND_CONFIG_NOT_FOUND']);
        }else
        if(!$to || $to === '' ){
            throw new \Exception(self::messages['ERROR_BACKEND_CONFIG_NOT_FOUND']);
        }else{

            $this->container = $container;
            // init project path
            $projectPath = $this->container->get('kernel')->getProjectDir();
            // init frontend path
            $frontendPath = realpath($projectPath.'/'.$path);
            if(file_exists($frontendPath) && is_dir($frontendPath)){
                $this->frontendPath = $frontendPath;
                $frontendComponentsPath = $frontendPath.'/src/components';
                if(file_exists($frontendComponentsPath) && is_dir($frontendComponentsPath)){
                    $componentDirectories = array_filter(glob($frontendComponentsPath.'/*'), 'is_dir');
                    if(is_array($componentDirectories) && count($componentDirectories)>0){
                        $this->componentsSource = $frontendComponentsPath;
                    }else{
                        throw new \Exception(self::messages['ERROR_FRONTEND_COMP_DIR_EMPTY'].$frontendComponentsPath);
                    }
                }else{
                    throw new \Exception(self::messages['ERROR_FRONTEND_COMP_DIR_NOT_FOUND'].$frontendComponentsPath);
                }
            }else{
                throw new \Exception(self::messages['ERROR_FRONTEND_DIR_NOT_FOUND'].$frontendPath);
            }
            // init backend path
            $destinationPath = $projectPath.'/'.$to;
            if(file_exists($destinationPath) && is_dir($destinationPath) && is_writable($destinationPath)){
                $this->componentsDestination = $destinationPath;
            }else{
                throw new \Exception(self::messages['ERROR_BACKEND_DIR_NOT_EXISTS_OR_WRITABLE'].$destinationPath);
            }
        }
    }

    /**
     * Init encore webpack configs for frontend
     */
    public function init($output)
    {
        $projectPath = $this->container->get('kernel')->getProjectDir();

        // generate/update the encore webpack config files
        $frontendConfigPath = $projectPath.'/comwrap.ezfrontend.config.js';
        #$frontendConfigContent = $this->container->get('twig')->render('ComwrapEzFrontendBundle:Webpack:frontend.config.js.twig',['path'=>$this->frontendPath]);
        $frontendConfigContent =  file_get_contents(__DIR__.'/../Resources/views/Webpack/frontend.config.js.twig');
        $frontendConfigContent = str_replace('{{ path | raw }}',$this->frontendPath,$frontendConfigContent);
        if(file_put_contents($frontendConfigPath, $frontendConfigContent)){
            $output->writeln("\n<info>Frontend config file was created :\n".$frontendConfigPath."</info>");
        }

        $frontendWebpackConfigPath = $projectPath.'/comwrap.ezfrontend.webpack.config.js';
        //$frontendWebpackConfigContent = $this->container->get('twig')->render('ComwrapEzFrontendBundle:Webpack:frontend.webpack.config.js.twig',[]);
        $frontendWebpackConfigContent =  file_get_contents(__DIR__.'/../Resources/views/Webpack/frontend.webpack.config.js.twig');
        if(file_put_contents($frontendWebpackConfigPath, $frontendWebpackConfigContent)){
            $output->writeln("\n<info>Frontend encore webpack config file was created:\n".$frontendWebpackConfigPath."</info>\n");
        }

        $webpackConfigPath = $projectPath.'/webpack.config.js';
        $webpackConfigExists = false;
        if(!file_exists($webpackConfigPath)){
            //$webpackConfigContent = $this->container->get('twig')->render('ComwrapEzFrontendBundle:Webpack:webpack.config.js.twig',[]);
            $webpackConfigContent =  file_get_contents(__DIR__.'/../Resources/views/Webpack/webpack.config.js.twig');
            if(file_put_contents($webpackConfigPath, $webpackConfigContent)){
                $output->writeln("<info>Encore webpack config file was created:\n".$webpackConfigPath."</info>\n");
            }
        }else{
            $webpackConfigExists = true;
        }

        // generate/update the package json files
        $frontendNodeJsonPath = $this->frontendPath.'/package.json';
        $frontendDependencies = [];
        if(file_exists($frontendNodeJsonPath)){
            $frontendNodeData = json_decode(file_get_contents($frontendNodeJsonPath), true);
            if($frontendNodeData && is_array($frontendNodeData) && isset($frontendNodeData['dependencies'])){
                if(is_array($frontendNodeData['dependencies']) && !empty($frontendNodeData['dependencies']) ){
                    foreach($frontendNodeData['dependencies'] as $repo => $version) {
                        $frontendDependencies[$repo] = $version;
                    }
                }
            }
        }

        $backendNodeJsonPath  = $projectPath.'/package.json';
        $backendNodeData = [
            'devDependencies' => self::defaultNodePackages
        ];
        if(file_exists($backendNodeJsonPath)){
            $backendNodeData = json_decode(file_get_contents($backendNodeJsonPath), true);
            if($backendNodeData && is_array($backendNodeData)){
                if(!isset($backendNodeData['devDependencies'])){
                    $backendNodeData['devDependencies'] = self::defaultNodePackages;
                }
                $backendRepos = array_keys($backendNodeData['devDependencies']);
                foreach(self::defaultNodePackages as $repo=>$version){
                    if(!in_array($repo,$backendRepos)){
                        $backendNodeData['devDependencies'][$repo] = $version;
                    }
                }
                if(count($frontendDependencies)>0){
                   foreach($frontendDependencies as $repo=>$version){
                       if(!in_array($repo,$backendRepos)){
                           $backendNodeData['devDependencies'][$repo] = $version;
                       }
                   }
                }
            }
        }else{
            if(count($frontendDependencies)>0){
                foreach($frontendDependencies as $repo=>$version){
                    if(!in_array($repo,self::defaultNodePackages)){
                        $backendNodeData['devDependencies'][$repo] = $version;
                    }
                }
            }
        }
        if(file_put_contents($backendNodeJsonPath, stripslashes(json_encode($backendNodeData, JSON_PRETTY_PRINT)))){
            $output->writeln("<info>Node JS package.json file was created/updated :\n".$backendNodeJsonPath."</info>\n");
        }

        // run yarn install
        $process = new Process('yarn install');
        $output->writeln("<fg=cyan>Running 'yarn install' to update frontend dependencies...</fg=cyan>\n");
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        //$output->writeln('<info>'.$process->getOutput().'</info>');

        if($webpackConfigExists == false){
            // run yarn encore dev
            $process = new Process('yarn encore dev --config-name comwrap_ez_frontend');
            $output->writeln("<fg=cyan>Running 'yarn encore dev' to generate frontend assets...</fg=cyan>");
            $process->run();
            // executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            //$output->writeln('<info>'.$process->getOutput().'</info>');
            $output->writeln('');    
        }
        
        // output layout tags
        $libScript = [];
        $libCss = [];
        $frontendDataJsPath = $this->frontendPath.'/data.js';
        if(file_exists($frontendDataJsPath)){
            $frontendDataJsLines = file($frontendDataJsPath);
            if($frontendDataJsLines && is_array($frontendDataJsLines) && count($frontendDataJsLines)>0){
                $flagScript = false;
                $flagCss = false;
                foreach($frontendDataJsLines as $line){

                    if(strpos($line, '],') !== false || strpos($line, ']') !== false){
                        $flagCss = false;
                        $flagScript = false;
                    }

                    if($flagScript || $flagCss){

                        $line = trim($line, "\t");
                        $line = trim($line,"',\n");
                        $line = trim($line,'\'');

                        if($flagScript && $line != '/assets/scripts/main.js'){
                            $libScript[] = $line;
                        }else
                        if($flagCss && $line != '/assets/styles/main.css' ){
                            $libCss[] = $line;
                        }
                    }

                    if(strpos($line, 'styles: [') !== false){
                        $flagCss = true;
                    }else
                    if(strpos($line, 'scripts: [') !== false){
                        $flagScript = true;
                    }
                }
            }
        }

        $output->writeln("<fg=cyan>*********************</fg=cyan>");
        $output->writeln("<fg=cyan>* Frontend Settings *</fg=cyan>");
        $output->writeln("<fg=cyan>*********************</fg=cyan>\n");

        // output css tags
        $output->writeln("<fg=cyan>1. CSS Tags</fg=cyan>\n");
        $output->writeln("{{ encore_entry_link_tags('app', null, 'comwrap_ez_frontend') }}");
        foreach($libCss as $css){
            $css = str_replace('/assets/', '/assets/frontend/build/', $css);
            $output->writeln("<link rel='stylesheet' href='".$css."'/>");
        }
        $output->writeln('');

        // output script tags
        $output->writeln("<fg=cyan>2. Script Tags</fg=cyan>\n");
        $output->writeln("{{ encore_entry_script_tags('app', null, 'comwrap_ez_frontend') }}");
        foreach($libScript as $js){
            $js = str_replace('/assets/', '/assets/frontend/build/', $js);
            $output->writeln("<script defer src='".$js."'></script>");
        }
        $output->writeln('');

        // output webpack configs
        if($webpackConfigExists===true){
            //$webpackEzConfigContent = $this->container->get('twig')->render('ComwrapEzFrontendBundle:Webpack:ez.webpack.config.js.twig',[]);
            $webpackEzConfigContent =  file_get_contents(__DIR__.'/../Resources/views/Webpack/ez.webpack.config.js.twig');
            $output->writeln("<fg=cyan>3. Load configs in the eZ Platform 'webpack.config.js'</fg=cyan>");
            $output->writeln("\n<fg=green>".$webpackEzConfigContent.'</fg=green>');
            $output->writeln("\n<fg=cyan>Then run 'yarn encore dev --config-name comwrap_ez_frontend' to update frontend assets.</fg=cyan>");
            $output->writeln('');
        }
    }

    /**
     * list all components in frontend
     */
    public function list($output)
    {
        $componentDirectories = array_filter(glob($this->componentsSource.'/*'), 'is_dir');
        if(is_array($componentDirectories) && count($componentDirectories)>0){
            $output->writeln("\n<fg=white>**************************</fg=white>");
            $output->writeln("<fg=white>* ".count($componentDirectories)." Frontend Components *</fg=white>");
            $output->writeln("<fg=white>**************************</fg=white>\n");
            foreach($componentDirectories as $index=>$componentDirectory){
                $output->writeln("<fg=white>".str_replace($this->componentsSource.'/','',$componentDirectory)."</fg=white>");
            }
            $output->writeln("\n<info>Use 'bin/console comwrap:frontend:import <Component ID>' to import a component into backend directory.</info>");
            $output->writeln("<comment>Use 'bin/console comwrap:frontend:compare <Component ID>' to compare components in frontend and backend.</comment>");
            $output->writeln("<fg=cyan>Use 'bin/console comwrap:frontend:meta <Component ID>' to get required data of a frontend component.</fg=cyan>\n");
        }
    }

    /**
     * copy a component from frontend to backend
     */
    public function copy($componentId, $output)
    {
        if(!$this->componentsDestination){
            throw new \Exception("Backend Components Direcotry Missing.");
        }else{
            $frontendComponentHtmlFile = $this->componentsSource.'/'.$componentId.'/'.$componentId.'.njk';
            if(file_exists($frontendComponentHtmlFile) && is_file($frontendComponentHtmlFile)){

                $componentsDestinationDirectory = $this->componentsDestination.'/'.$componentId;
                if(!file_exists($componentsDestinationDirectory)){
                    mkdir($componentsDestinationDirectory);
                }

                $backendComponentHtmlFile = $componentsDestinationDirectory.'/'.$componentId.'.njk';
                if(!file_exists($backendComponentHtmlFile)){
                    if(copy($frontendComponentHtmlFile, $backendComponentHtmlFile)){
                        $output->writeln("\n<info>Compoent was Created :".$backendComponentHtmlFile."</info>\n");
                    }else{
                        throw new \Exception("Error On Copying File.");
                    }
                }else{
                    $backendComponentHtmlFileTmp = $componentsDestinationDirectory.'/'.$componentId.'.new.njk';
                    if(copy($frontendComponentHtmlFile, $backendComponentHtmlFileTmp)){
                        $output->writeln("\n<info>Compoent was Created :".$backendComponentHtmlFileTmp."</info>\n");
                    }else{
                        throw new \Exception("Error On Copying File.");
                    }
                }
            }else{
                throw new \Exception("Frontend Component '".$componentId.".njk' File Not Found in Directory: ".$this->componentsSource.'/'.$componentId."/");
            }
        }
    }

    /**
     * compare a component in frontend and backend
     */
    public function compare($componentId, $output)
    {
        if(!$this->componentsDestination){
            throw new \Exception("Backend Components Direcotry Missing.");
        }else{
            $frontendComponentHtmlFile = $this->componentsSource.'/'.$componentId.'/'.$componentId.'.njk';
            if(file_exists($frontendComponentHtmlFile) && is_file($frontendComponentHtmlFile)){
                $componentsDestinationDirectory = $this->componentsDestination.'/'.$componentId;
                $backendComponentHtmlFile = $componentsDestinationDirectory.'/'.$componentId.'.njk';
                if(file_exists($backendComponentHtmlFile)){

                    exec('git diff '.$frontendComponentHtmlFile.' '.$backendComponentHtmlFile, $lines);
                    if($lines && is_array($lines) && count($lines)>0){
                        foreach($lines as $i=>$line){
                            if($i==3){
                                $output->writeln("\n<fg=black;bg=green>  </fg=black;bg=green> Frontend <fg=black;bg=cyan>  </fg=black;bg=cyan> Backend\n");
                            }else
                                if($i>3 && $i < count($lines)-1)
                                {
                                    $rowColor = '';
                                    $firstChar = substr($line, 0, 1);

                                    if($i==2){
                                        $rowColor = 'fg=black;bg=cyan';
                                    }else
                                        if($i==3){
                                            $rowColor = 'fg=black;bg=green';
                                        }else
                                            if($firstChar == '+')
                                            {
                                                $rowColor = 'fg=black;bg=cyan';
                                            }else
                                                if($firstChar == '-')
                                                {
                                                    $rowColor = 'fg=black;bg=green';
                                                }else{
                                                    $rowColor = 'comment';
                                                }
                                    $output->writeln('<'.$rowColor.'>'.$line.'</'.$rowColor.'>');
                                }
                        }
                    }else{
                        $output->writeln("\n<info>The Component has not different in Frontend and in Backend.</info>\n");
                    }
                }else{
                    throw new \Exception("Backend Component '".$componentId.".njk' File Not Found in Directory: ".$this->componentsDestination.'/'.$componentId."/");
                }
            }else{
                throw new \Exception("Frontend Component '".$componentId.".njk' File Not Found in Directory: ".$this->componentsSource.'/'.$componentId."/");
            }
        }
    }

    /**
     * get meta data of a component in frontend
     */
    public function meta($componentId, $output)
    {
        if(!$this->componentsDestination){
            throw new \Exception("Backend Components Direcotry Missing.");
        }else{
            $frontendComponentDataFile = $this->componentsSource.'/'.$componentId.'/'.$componentId.'.data.js';
            if(file_exists($frontendComponentDataFile) && is_file($frontendComponentDataFile)){
                $content = file_get_contents($frontendComponentDataFile);
                if($content){
                    $contentJs = str_replace('module.exports = ', '', $content);
                    try {
                        $contentJson = $this->parseJsData($contentJs);
                        if($contentJson && is_array($contentJson) && count($contentJson)>1){
                            //TODO: $outputtext = $this->var_export_short($contentJson[1],true);
                            //TODO: $output->writeln("\nData Required:\n<info>".$outputtext."</info>\n");
                        }else{
                            throw new \Exception("Frontend Component Data '".$componentId.".data.js' is Invalid.");
                        }
                    }catch(\Exception $e){
                        throw $e;
                    }
                }else{
                    throw new \Exception("Frontend Component Data '".$componentId.".data.js' is Emtpy.");
                }
            }else{
                throw new \Exception("Frontend Component Data '".$componentId.".data.js' File Not Found in Directory: ".$this->componentsSource.'/'.$componentId."/");
            }
        }
    }
}