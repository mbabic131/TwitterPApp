<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tweet
 *
 * @ORM\Table(name="tweets", options={"engine":"MyISAM"}, indexes={@ORM\Index(columns={"tweet_text"},flags={"fulltext"})}, uniqueConstraints={@ORM\UniqueConstraint(name="twitter_id", columns={"twitter_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TweetRepository")
 */
class Tweet
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

   /**
     * @var string
     *
     * @ORM\Column(name="twitter_id", type="bigint")
     */
    private $twitter_id;

    /**
     * @var string
     *
     * @ORM\Column(name="tweet_text", type="text", length=65000, nullable=false)
     */
    private $tweet_text;

    /**
     * Tweet have one user
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="tweets")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set twitterId
     *
     * @param integer $twitterId
     *
     * @return Tweet
     */
    public function setTwitterId($twitterId)
    {
        $this->twitter_id = $twitterId;

        return $this;
    }

    /**
     * Get twitterId
     *
     * @return integer
     */
    public function getTwitterId()
    {
        return $this->twitter_id;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Tweet
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Tweet
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \datatime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Tweet
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set tweetText
     *
     * @param string $tweetText
     *
     * @return Tweet
     */
    public function setTweetText($tweetText)
    {
        $this->tweet_text = $tweetText;

        return $this;
    }

    /**
     * Get tweetText
     *
     * @return string
     */
    public function getTweetText()
    {
        return $this->tweet_text;
    }
}
