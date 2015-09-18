<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JavascriptError
 */
class JavascriptError
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
    private $error;

    /**
     * @var string
     */
    private $userAgent;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $columnNumber;

    /**
     * @var string
     */
    private $lineNumber;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var string
     */
    private $scriptUrl;


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
     * @return JavascriptError
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
     * Set error
     *
     * @param string $error
     * @return JavascriptError
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return string 
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set userAgent
     *
     * @param string $userAgent
     * @return JavascriptError
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Get userAgent
     *
     * @return string 
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return JavascriptError
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set columnNumber
     *
     * @param string $columnNumber
     * @return JavascriptError
     */
    public function setColumnNumber($columnNumber)
    {
        $this->columnNumber = $columnNumber;

        return $this;
    }

    /**
     * Get columnNumber
     *
     * @return string 
     */
    public function getColumnNumber()
    {
        return $this->columnNumber;
    }

    /**
     * Set lineNumber
     *
     * @param string $lineNumber
     * @return JavascriptError
     */
    public function setLineNumber($lineNumber)
    {
        $this->lineNumber = $lineNumber;

        return $this;
    }

    /**
     * Get lineNumber
     *
     * @return string 
     */
    public function getLineNumber()
    {
        return $this->lineNumber;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     * @return JavascriptError
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
     * Set scriptUrl
     *
     * @param string $scriptUrl
     * @return JavascriptError
     */
    public function setScriptUrl($scriptUrl)
    {
        $this->scriptUrl = $scriptUrl;

        return $this;
    }

    /**
     * Get scriptUrl
     *
     * @return string 
     */
    public function getScriptUrl()
    {
        return $this->scriptUrl;
    }
}
