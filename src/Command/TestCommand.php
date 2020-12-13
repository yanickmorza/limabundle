<?php

namespace App\LimaBundle;

use Symfony\Component\HttpFoundation\Response;

class TestCommand 
{
    public function postPackageInstall():Response
    {
        return new Response('Oups !!!');
    }
}
