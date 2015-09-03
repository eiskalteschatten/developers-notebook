<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EditorSettings
 */
class EditorSettings
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
    private $defaultTheme;

    /**
     * @var boolean
     */
    private $highlightActiveLine;

    /**
     * @var boolean
     */
    private $wrapSearch;

    /**
     * @var boolean
     */
    private $caseSensitiveSearch;

    /**
     * @var boolean
     */
    private $matchWholeWordsSearch;

    /**
     * @var boolean
     */
    private $isRegexSearch;

    /**
     * @var boolean
     */
    private $skipCurrentLineSearch;

    /**
     * @var string
     */
    private $defaultSyntaxModeCode;

    /**
     * @var string
     */
    private $defaultSyntaxModeJournal;

    /**
     * @var string
     */
    private $defaultSyntaxModeNotebook;


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
     * @return EditorSettings
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
     * Set defaultTheme
     *
     * @param string $defaultTheme
     * @return EditorSettings
     */
    public function setDefaultTheme($defaultTheme)
    {
        $this->defaultTheme = $defaultTheme;

        return $this;
    }

    /**
     * Get defaultTheme
     *
     * @return string 
     */
    public function getDefaultTheme()
    {
        return $this->defaultTheme;
    }

    /**
     * Set highlightActiveLine
     *
     * @param boolean $highlightActiveLine
     * @return EditorSettings
     */
    public function setHighlightActiveLine($highlightActiveLine)
    {
        $this->highlightActiveLine = $highlightActiveLine;

        return $this;
    }

    /**
     * Get highlightActiveLine
     *
     * @return boolean 
     */
    public function getHighlightActiveLine()
    {
        return $this->highlightActiveLine;
    }

    /**
     * Set wrapSearch
     *
     * @param boolean $wrapSearch
     * @return EditorSettings
     */
    public function setWrapSearch($wrapSearch)
    {
        $this->wrapSearch = $wrapSearch;

        return $this;
    }

    /**
     * Get wrapSearch
     *
     * @return boolean 
     */
    public function getWrapSearch()
    {
        return $this->wrapSearch;
    }

    /**
     * Set caseSensitiveSearch
     *
     * @param boolean $caseSensitiveSearch
     * @return EditorSettings
     */
    public function setCaseSensitiveSearch($caseSensitiveSearch)
    {
        $this->caseSensitiveSearch = $caseSensitiveSearch;

        return $this;
    }

    /**
     * Get caseSensitiveSearch
     *
     * @return boolean 
     */
    public function getCaseSensitiveSearch()
    {
        return $this->caseSensitiveSearch;
    }

    /**
     * Set matchWholeWordsSearch
     *
     * @param boolean $matchWholeWordsSearch
     * @return EditorSettings
     */
    public function setMatchWholeWordsSearch($matchWholeWordsSearch)
    {
        $this->matchWholeWordsSearch = $matchWholeWordsSearch;

        return $this;
    }

    /**
     * Get matchWholeWordsSearch
     *
     * @return boolean 
     */
    public function getMatchWholeWordsSearch()
    {
        return $this->matchWholeWordsSearch;
    }

    /**
     * Set isRegexSearch
     *
     * @param boolean $isRegexSearch
     * @return EditorSettings
     */
    public function setIsRegexSearch($isRegexSearch)
    {
        $this->isRegexSearch = $isRegexSearch;

        return $this;
    }

    /**
     * Get isRegexSearch
     *
     * @return boolean 
     */
    public function getIsRegexSearch()
    {
        return $this->isRegexSearch;
    }

    /**
     * Set skipCurrentLineSearch
     *
     * @param boolean $skipCurrentLineSearch
     * @return EditorSettings
     */
    public function setSkipCurrentLineSearch($skipCurrentLineSearch)
    {
        $this->skipCurrentLineSearch = $skipCurrentLineSearch;

        return $this;
    }

    /**
     * Get skipCurrentLineSearch
     *
     * @return boolean 
     */
    public function getSkipCurrentLineSearch()
    {
        return $this->skipCurrentLineSearch;
    }

    /**
     * Set defaultSyntaxModeCode
     *
     * @param string $defaultSyntaxModeCode
     * @return EditorSettings
     */
    public function setDefaultSyntaxModeCode($defaultSyntaxModeCode)
    {
        $this->defaultSyntaxModeCode = $defaultSyntaxModeCode;

        return $this;
    }

    /**
     * Get defaultSyntaxModeCode
     *
     * @return string 
     */
    public function getDefaultSyntaxModeCode()
    {
        return $this->defaultSyntaxModeCode;
    }

    /**
     * Set defaultSyntaxModeJournal
     *
     * @param string $defaultSyntaxModeJournal
     * @return EditorSettings
     */
    public function setDefaultSyntaxModeJournal($defaultSyntaxModeJournal)
    {
        $this->defaultSyntaxModeJournal = $defaultSyntaxModeJournal;

        return $this;
    }

    /**
     * Get defaultSyntaxModeJournal
     *
     * @return string 
     */
    public function getDefaultSyntaxModeJournal()
    {
        return $this->defaultSyntaxModeJournal;
    }

    /**
     * Set defaultSyntaxModeNotebook
     *
     * @param string $defaultSyntaxModeNotebook
     * @return EditorSettings
     */
    public function setDefaultSyntaxModeNotebook($defaultSyntaxModeNotebook)
    {
        $this->defaultSyntaxModeNotebook = $defaultSyntaxModeNotebook;

        return $this;
    }

    /**
     * Get defaultSyntaxModeNotebook
     *
     * @return string 
     */
    public function getDefaultSyntaxModeNotebook()
    {
        return $this->defaultSyntaxModeNotebook;
    }
}
