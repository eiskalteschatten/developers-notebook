<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeneralSettings
 */
class GeneralSettings
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $userId;

    /**
     * @var string
     */
    private $weatherLocation;

    /**
     * @var string
     */
    private $weatherUnit;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return GeneralSettings
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set weatherLocation
     *
     * @param string $weatherLocation
     * @return GeneralSettings
     */
    public function setWeatherLocation($weatherLocation)
    {
        $this->weatherLocation = $weatherLocation;

        return $this;
    }

    /**
     * Get weatherLocation
     *
     * @return string 
     */
    public function getWeatherLocation()
    {
        return $this->weatherLocation;
    }

    /**
     * Set weatherUnit
     *
     * @param string $weatherUnit
     * @return GeneralSettings
     */
    public function setWeatherUnit($weatherUnit)
    {
        $this->weatherUnit = $weatherUnit;

        return $this;
    }

    /**
     * Get weatherUnit
     *
     * @return string 
     */
    public function getWeatherUnit()
    {
        return $this->weatherUnit;
    }
}
