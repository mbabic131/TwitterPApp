<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use HttpRequest;

class DefaultController extends Controller
{
    /**
     * @Route("/{username}")
     */
    public function showUserAction($username)
    {
    	$em = $this->getDoctrine()->getManager();
        $twitter = $this->get('twitter.api');

        $twitterUser = $twitter->getUserByName($username);

        // Check for errors
        if (isset($twitterUser->errors))
        {
        	$message = $user->errors[0]->message;
        	echo $message;
        	die();
        }

        // Check if user exists in database
        $user = $em->getRepository('AppBundle:User')->findOneBy(array('twitter_id' => (int)$twitterUser->id_str));

        if (empty($user))
        {
        	$user = $this->storeUser($twitterUser);
        }



        return new Response("OK");
    }

    /**
     * Store new user
     *
     * @param stdClass
     *
     * @return AppBundle\Entity\User
     */
    private function storeUser($twitterUser)
    {
    	$em = $this->getDoctrine()->getManager();

		$user = new User;
        $user->setTwitterId((int)$user->id_str);
        $user->setName($user->name);
        $user->setScreenName($user->screen_name);
        $user->setCreatedAt(new \DateTime($user->created_at));

        $em->persist($user);
        $em->flush();

        return $user;
    }
    
}
