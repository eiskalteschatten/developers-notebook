<?php

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;

class FoldersProjects
{
	protected $em;
	protected $repository;
	protected $folderProjectId;
	protected $itemId;

	public function __construct(EntityManager $entityManager) {
	    $this->em = $entityManager;
	}
	
	public function init($repository, $folderProjectId, $itemId) {
		$this->setRepository($repository);
		$this->setFolderProjectId($folderProjectId);
		$this->setItemId($itemId);
	}
	
	public function moveItemToFolder() {
		$items = $this->em->getRepository($this->getRepository())->find($this->getItemId());
		$items->setFolder($this->getFolderProjectId());
		$this->em->flush();

		$response = new JsonResponse(array('folder' => $items->getFolder()));
		return $response;
	}
	
	public function moveItemToProject() {
		$items = $this->em->getRepository($this->getRepository())->find($this->getItemId());
		$items->setProject($this->getFolderProjectId());
		$this->em->flush();

		$response = new JsonResponse(array('project' => $items->getProject()));
		return $response;
	}
	
	public function setRepository($repository) {
		$this->repository = $repository;
	}
	
	public function getRepository() {
		return $this->repository;
	}
	
	public function setFolderProjectId($folderProjectId) {
		$this->folderProjectId = $folderProjectId;
	}
	
	public function getFolderProjectId() {
		return $this->folderProjectId;
	}
	
	public function setItemId($itemId) {
		$this->itemId = $itemId;
	}
	
	public function getItemId() {
		return $this->itemId;
	}
}