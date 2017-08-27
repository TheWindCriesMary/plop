<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Item
 *
 * @ORM\Table(name="item")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ItemRepository")
 */
class Item
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="volume", type="float")
     */
    private $volume;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="ItemGroup")
     */
    private $itemGroup;

    /**
     * @var int
     *
     * @ORM\Column(name="iconId", type="integer", nullable=true)
     */
    private $iconId;


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
     * @return Item
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
     * Set volume
     *
     * @param float $volume
     *
     * @return Item
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume
     *
     * @return float
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set iconId
     *
     * @param integer $iconId
     *
     * @return Item
     */
    public function setIconId($iconId)
    {
        $this->iconId = $iconId;

        return $this;
    }

    /**
     * Get iconId
     *
     * @return int
     */
    public function getIconId()
    {
        return $this->iconId;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Item
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set itemGroup
     *
     * @param \AppBundle\Entity\ItemGroup $itemGroup
     *
     * @return Item
     */
    public function setItemGroup(\AppBundle\Entity\ItemGroup $itemGroup = null)
    {
        $this->itemGroup = $itemGroup;

        return $this;
    }

    /**
     * Get itemGroup
     *
     * @return \AppBundle\Entity\ItemGroup
     */
    public function getItemGroup()
    {
        return $this->itemGroup;
    }
}
