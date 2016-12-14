<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Tweet;
use HttpRequest;

class DefaultController extends Controller
{
	/**
	 * @Route("/", name="user_list")
	 * @Method("GET")
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();
		$users = $em->getRepository('AppBundle:User')->findAll();

		return $this->render('users_list.html.twig', array('users' => $users));
	}

    /**
     * Show search form
     *
     * @Route("/search", name="search")
     * @Method("GET")
     */
    public function showSearch()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')->findAll();

        return $this->render('search_tweets.html.twig', array('users' => $users));
    }

    /**
     * Search for tweets
     *
     * @Route("/search/tweets", name="search_tweets")
     * @Method("POST")
     */
    public function search(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $screen_name = $request->request->get('user');
        $search_term = $request->request->get('search_term');

        $user = $em->getRepository('AppBundle:User')->findOneBy(array('screen_name' => $screen_name));
        if(empty($user) || $user == null)
        {
            echo "Korisnik nije pronađen";
            die();
        }


    }

    /**
     * @Route("/{username}/{page}", defaults={"page" = 1}, name="user_tweets")
     * @Method("GET")
     */
    public function showUserAction($username, $page)
    {
    	$em = $this->getDoctrine()->getManager();
        $twitter = $this->get('twitter.api');

        // Check if user exists in database
        $user = $em->getRepository('AppBundle:User')->findOneBy(array('screen_name' => $username));
        if (empty($user) || $user == null)
        {
        	$twitterUser = $twitter->getUserByName($username);
	        // Check for errors
	        if (isset($twitterUser->errors))
	        {
	        	$message = $user->errors[0]->message;
	        	echo $message;
	        	die();
	        }
	        	$user = $this->storeUser($twitterUser);
        }

        // Check tweets and save to database
        if($page == 1) 
        {
            $userTweets = $twitter->getUserTweets($user->getScreenName(), $this->container->getParameter('number_of_tweets'));
            // Check for errors
            if(isset($userTweets->error)) {
                $message = $userTweets->error;
                echo $message;
                die();
            }
            $this->storeUserTweets($user, $userTweets);
        }

        $limit = $this->container->getParameter('pagination_limit');
        $tweets = $em->getRepository('AppBundle:Tweet')->getTweets($page, $user, $limit);
        $totalPages = ceil($tweets->count() / $limit);

        return $this->render('show_user.html.twig', array('user' => $user, 'tweets' => $tweets, 'page' => $page, 'total' => $totalPages));
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
        $user->setTwitterId((int)$twitterUser->id_str);
        $user->setName($twitterUser->name);
        $user->setScreenName($twitterUser->screen_name);
        $user->setCreatedAt(new \DateTime($twitterUser->created_at));

        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * Store user tweets
     *
     * @param $user AppBundle\Entity\User
     * @param $userTweets array
     *
     * @return void
     */
    private function storeUserTweets($user, $userTweets)
    {
    	$em = $this->getDoctrine()->getManager();
    	foreach ($userTweets as $tweet) {
    		$checkTweet = $em->getRepository('AppBundle:Tweet')->findOneBy(array('twitter_id' => (int)$tweet->id_str));

    		if (empty($checkTweet) || $checkTweet == null)
    		{
    			$newTweet = new Tweet;
    			$newTweet->setTwitterId((int)$tweet->id_str);
    			$newTweet->setTweetText($tweet->text);
    			$newTweet->setUser($user);
    			$newTweet->setCreatedAt(new \DateTime($tweet->created_at));

    			$em->persist($newTweet);
    			$em->flush();
    		}
    	}
    }
    
}
