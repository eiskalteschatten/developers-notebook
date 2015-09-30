<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Issue
 */
class Issue
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
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var \DateTime
     */
    private $dateModified;

    /**
     * @var boolean
     */
    private $isCompleted;

    /**
     * @var \DateTime
     */
    private $dateCompleted;

    /**
     * @var \DateTime
     */
    private $datePlanned;

    /**
     * @var \DateTime
     */
    private $dateDue;

    /**
     * @var integer
     */
    private $folder;

    /**
     * @var integer
     */
    private $project;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $labels;

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
     * @return Issue
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
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     * @return Issue
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime 
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set dateModified
     *
     * @param \DateTime $dateModified
     * @return Issue
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * Get dateModified
     *
     * @return \DateTime 
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * Set isCompleted
     *
     * @param boolean $isCompleted
     * @return Issue
     */
    public function setIsCompleted($isCompleted)
    {
        $this->isCompleted = $isCompleted;

        return $this;
    }

    /**
     * Get isCompleted
     *
     * @return boolean 
     */
    public function getIsCompleted()
    {
        return $this->isCompleted;
    }

    /**
     * Set dateCompleted
     *
     * @param \DateTime $dateCompleted
     * @return Issue
     */
    public function setDateCompleted($dateCompleted)
    {
        $this->dateCompleted = $dateCompleted;

        return $this;
    }

    /**
     * Get dateCompleted
     *
     * @return \DateTime 
     */
    public function getDateCompleted()
    {
        return $this->dateCompleted;
    }

    /**
     * Set datePlanned
     *
     * @param \DateTime $datePlanned
     * @return Issue
     */
    public function setDatePlanned($datePlanned)
    {
        $this->datePlanned = $datePlanned;

        return $this;
    }

    /**
     * Get datePlanned
     *
     * @return \DateTime 
     */
    public function getDatePlanned()
    {
        return $this->datePlanned;
    }

    /**
     * Set dateDue
     *
     * @param \DateTime $dateDue
     * @return Issue
     */
    public function setDateDue($dateDue)
    {
        $this->dateDue = $dateDue;

        return $this;
    }

    /**
     * Get dateDue
     *
     * @return \DateTime 
     */
    public function getDateDue()
    {
        return $this->dateDue;
    }

    /**
     * Set folder
     *
     * @param integer $folder
     * @return Issue
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get folder
     *
     * @return integer 
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set project
     *
     * @param integer $project
     * @return Issue
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return integer 
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Issue
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Issue
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set labels
     *
     * @param string $labels
     * @return Issue
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * Get labels
     *
     * @return string 
     */
    public function getLabels()
    {
        return $this->labels;
    }
}
