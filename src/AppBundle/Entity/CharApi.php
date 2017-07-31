<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CharApi
 *
 * @ORM\Table(name="char_api")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CharApiRepository")
 */
class CharApi
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
     * @var int
     *
     * @ORM\Column(name="charId", type="integer")
     */
    private $charId;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="refreshToken", type="string", length=255)
     */
    private $refreshToken;

    /**
     * @var string
     *
     * @ORM\Column(name="charName", type="string", length=255)
     */
    private $charName;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="apis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var boolean
     *
     */
    public $isValid = false;


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
     * Set charId
     *
     * @param integer $charId
     *
     * @return CharApi
     */
    public function setCharId($charId)
    {
        $this->charId = $charId;

        return $this;
    }

    /**
     * Get charId
     *
     * @return int
     */
    public function getCharId()
    {
        return $this->charId;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return CharApi
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set refreshToken
     *
     * @param string $refreshToken
     *
     * @return CharApi
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Get refreshToken
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Set charName
     *
     * @param string $charName
     *
     * @return CharApi
     */
    public function setCharName($charName)
    {
        $this->charName = $charName;

        return $this;
    }

    /**
     * Get charName
     *
     * @return string
     */
    public function getCharName()
    {
        return $this->charName;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return CharApi
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get userId
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }
}

