<?php

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;

class GetFoldersProjects
{
	protected $em;
	protected $userId;
	protected $area;

	public function __construct(EntityManager $entityManager) {
	    $this->em = $entityManager;
	}
	
	public function init($userId, $area) {
		$this->setUserId($userId);
		$this->setArea($area);
	} 
	
	public function getFolders() {
		$foldersResult = $this->em
        ->getRepository('AppBundle:Folders')
        ->findBy(
			array('userId' => $this->getUserId(), 'area' => $this->getArea()),
			array('name' => 'ASC')
		);
		
		$folders = array();
		
		foreach ($foldersResult as $folder) {
			$folders[] = array(
				'id' => $folder->getId(),
				'name' => $folder->getName()
			);
		}
		
		return $folders;
	}
	
	public function getProjects() {
		$projectsResult = $this->em
        ->getRepository('AppBundle:Project')
        ->findBy(
			array('userId' => $this->getUserId()),
			array('name' => 'ASC')
		);
		
		$projects = array();
		
		foreach ($projectsResult as $project) {
			if (!$project->getIsCompleted()) {
				$projects[] = array(
					'id' => $project->getId(),
					'name' => $project->getName()
				);
			}
		}
		
		return $projects;
	}
	
	public function setUserId($userId) {
		$this->userId = $userId;
	}
	
	public function getUserId() {
		return $this->userId;
	}
	
	public function setArea($area) {
		$this->area = $area;
	}
	
	public function getArea() {
		return $this->area;
	}
}