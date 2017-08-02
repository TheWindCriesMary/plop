<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use nullx27\ESI\Models\GetCorporationsCorporationIdOk;

/**
 * user
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\userRepository")
 */
class User
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="char_id", type="integer", unique=true)
     */
    private $charId;

    /**
     * @var int
     *
     * @ORM\Column(name="corp_id", type="integer")
     */
    private $corpId;

    /**
     * @var int
     *
     * @ORM\ManyToMany(targetEntity="Groupe", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */

    private $groupes;

    /**
     * @var GetCorporationsCorporationIdOk
     */
    public $corp;

    /**
     * @ORM\OneToMany(targetEntity="CharApi", mappedBy="user")
     */
    private $apis;



    public function __construct()
    {

        $this->groupes = new  ArrayCollection();
        $this->apis = new ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return user
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set charId
     *
     * @param integer $charId
     *
     * @return user
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
     * Set corpId
     *
     * @param integer $corpId
     *
     * @return user
     */
    public function setCorpId($corpId)
    {
        $this->corpId = $corpId;

        return $this;
    }

    /**
     * Get corpId
     *
     * @return int
     */
    public function getCorpId()
    {
        return $this->corpId;
    }

    /**
     * Set groupe
     *
     * @param integer $groupe
     * @return User
     */
    public function setGroupes($groupes)
    {
        $this->groupes = $groupes;

        return $this;
    }

    /**
     * @param $groupes
     * @return User
     */
    public function setGroupe($groupes)
    {
        return $this->setGroupes($groupes);
    }

    /**
     * Get groupe
     *
     * @return integer
     */
    public function getGroupes()
    {
        return $this->groupes;
    }

    /**
     * Get groupe
     *
     * @return integer
     */
    public function getGroupe()
    {
        return $this->getGroupes();
    }

    public function __toString() {
        return $this->id.'';
    }
}
