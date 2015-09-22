<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Todo
 */
class Todo
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
     * @var string
     */
    private $todo;

    /**
     * @var string
     */
    private $notes;

    /**
     * @var boolean
     */
    private $isCompleted;

    /**
     * @var \DateTime
     */
    private $dateCompleted;

    /**
     * @var \Date
     */
    private $datePlanned;

    /**
     * @var \Date
     */
    private $dateDue;

    /**
     * @var integer
     */
    private $priority;

    /**
     * @var integer
     */
    private $folder;

    /**
     * @var integer
     */
    private $project;


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
     * @return Todo
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
     * @return Todo
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
     * @return Todo
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
     * Set todo
     *
     * @param string $todo
     * @return Todo
     */
    public function setTodo($todo)
    {
        $this->todo = $todo;

        return $this;
    }

    /**
     * Get todo
     *
     * @return string 
     */
    public function getTodo()
    {
        return $this->todo;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return Todo
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set isCompleted
     *
     * @param boolean $isCompleted
     * @return Todo
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
     * @return Todo
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
     * @param \Date $datePlanned
     * @return Todo
     */
    public function setDatePlanned($datePlanned)
    {
        $this->datePlanned = $datePlanned;

        return $this;
    }

    /**
     * Get datePlanned
     *
     * @return \Date
     */
    public function getDatePlanned()
    {
        return $this->datePlanned;
    }

    /**
     * Set dateDue
     *
     * @param \Date $dateDue
     * @return Todo
     */
    public function setDateDue($dateDue)
    {
        $this->dateDue = $dateDue;

        return $this;
    }

    /**
     * Get dateDue
     *
     * @return \Date
     */
    public function getDateDue()
    {
        return $this->dateDue;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     * @return Todo
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set folder
     *
     * @param integer $folder
     * @return Todo
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
     * @return Todo
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
}
