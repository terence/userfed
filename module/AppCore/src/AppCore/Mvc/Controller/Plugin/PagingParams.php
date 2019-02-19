<?php

namespace AppCore\Mvc\Controller\Plugin;

class PagingParams extends AbstractPlugin
{

	/**
	 * Extract offset/limit from page : page, item per page
	 * @param int $totalItems
	 * @return array of offset and limit
	 */
	public function get($totalItems = 0)
	{
		$itemsPerPageValues = $this->getItemsPerPageValues();

		$controller = $this->getController();

		$request = $controller->getRequest();
		$params = $request->getQuery();

		$currentPageNumber = (isset($params['page']) ? (int) $params['page'] : 1);

        if($currentPageNumber < 1) {
			$currentPageNumber = 1;
        }
        
        /* @var $controller \HtApplication\Controller\AbstractActionController */
        $config = $controller->getConfig();
        
        $defaultItemPerPage = $itemsPerPageValues[1];
        if (isset($config['view_manager']['pagination']['itemPerPage'])) {
            $defaultItemPerPage = (int) $config['view_manager']['pagination']['itemPerPage'];
        }
		
        // Store old "items per page" and "page" to re-assign current page when user change "items per page" number
		$oldItemPerPage = isset($_COOKIE['oldItemsPerPage']) ? (int) $_COOKIE['oldItemsPerPage'] : $defaultItemPerPage;
		$oldPage = isset($_COOKIE['oldPage']) ? (int) $_COOKIE['oldPage'] : $currentPageNumber;
        
		$numberItemPerPage = (isset($params['ipp']) ? (int) $params['ipp'] : $oldItemPerPage);

		if(!in_array($numberItemPerPage, $itemsPerPageValues)) {
			$numberItemPerPage = $itemsPerPageValues[0];
		}

		// Re-assign current page
		if($oldItemPerPage != $numberItemPerPage) {
			$oldOffset = ($oldPage - 1) * $oldItemPerPage;
			$currentPageNumber = (int) ($oldOffset / $numberItemPerPage) + 1;
		}

		$offset = ($currentPageNumber - 1) * $numberItemPerPage;

		// Check if current offset greater than total items
		if($totalItems != 0 && $offset >= $totalItems) {
			$offset = $totalItems - $numberItemPerPage;
			if($offset < 0) {
				$offset = 0;
			}
			$currentPageNumber = (int) ($offset / $numberItemPerPage) + 1;
		}

		$pagingParams = array(
			'page' => $currentPageNumber,
			'itemPerPage' => $numberItemPerPage,
			'offset' => $offset,
			'limit' => $numberItemPerPage
		);

		return $pagingParams;
	}

	protected $itemsPerPageValues = array(5, 10, 20, 30);

	public function getItemsPerPageValues()
	{
		return $this->itemsPerPageValues;
	}

	public function setItemsPerPageValues($itemsPerPageValues)
	{
		$this->itemsPerPageValues = $itemsPerPageValues;
		return $this;
	}

}