<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConnectorTodosIssues
 */
class ConnectorTodosIssues
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
     * @var integer
     */
    private $issue;

    /**
     * @var integer
     */
    private $todo;

    /**
     * @var \DateTime
     */
    private $dateCreated;


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
     * @return ConnectorTodosIssues
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
     * Set issue
     *
     * @param integer $issue
     * @return ConnectorTodosIssues
     */
    public function setIssue($issue)
    {
        $this->issue = $issue;

        return $this;
    }

    /**
     * Get issue
     *
     * @return integer 
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * Set todo
     *
     * @param integer $todo
     * @return ConnectorTodosIssues
     */
    public function setTodo($todo)
    {
        $this->todo = $todo;

        return $this;
    }

    /**
     * Get todo
     *
     * @return integer 
     */
    public function getTodo()
    {
        return $this->todo;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     * @return ConnectorTodosIssues
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
}
