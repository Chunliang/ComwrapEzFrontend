<?php

namespace Comwrap\Bundle\ComwrapEzFrontendBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Comwrap\Bundle\ComwrapEzFrontendBundle\DependencyInjection\ComwrapEzFrontendExtension;

class ComwrapEzFrontendBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ComwrapEzFrontendExtension();
    }
}
