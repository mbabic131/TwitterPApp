<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use HttpRequest;

class DefaultController extends Controller
{
    /**
     * @Route("/test")
     */
    public function test()
    {
        $twitter = $this->get('twitter.api');
        $result = $twitter->getUserTweets();

        dump($result);
        die();
    }
    
}
