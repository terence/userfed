<?php

namespace HtAdmin\Controller;

use HtApplication\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Zend\Db\Sql\Where;

abstract class AbstractUserRestfulController extends AbstractRestfulController
{
    /**
     * Get user via userId (if userId not set, It will be get from route.)
     * return a \Zend\View\Model\JsonModel if no user found.
     * @param int $userId
     * @return \HtUser\Model\User | \Zend\View\Model\JsonModel
     */
    protected function getUser($userId = null)
    {
        if ($userId === null) {
            $userId = $this->params('id', null);
        }

        /* @var $user \HtUser\Model\User */
        $user = $this->getUserModel();

        if (!$user->load($userId)) {
            return new JsonModel(array(
                'success' => false,
                'message' => $this->translate('No user found'),
            ));
        }
        return $user;
    }
    
    
    /**
     * get an user's info
     * @param int $id
     * @return \Zend\View\Model\JsonModel
     */
    public function get($id)
    {
        $user = $this->getUser($id);
        if ($user instanceof JsonModel) {
            return $user;
        }
        
        return new JsonModel(array(
            'success' => true,
            'user' => $user->toArray(),
        ));
    }
    
    /**
     * Get list users.
     * @return \Zend\View\Model\JsonModel
     */
    public function getList()
    {
        $sl = $this->getServiceLocator();
        /* @var $userCollecttion \HtUser\Model\UserCollection */
        $userCollecttion = $sl->get('UserCollection');

        $where = new Where();
        $page = $this->params()->fromQuery('draw');
        $offset = $this->params()->fromQuery('start');
        $limit = $this->params()->fromQuery('length');
        $search = $this->params()->fromQuery('search');
        $keyword = $search['value'];

        $columns = $this->params()->fromQuery('columns');
        $order = $this->params()->fromQuery('order');
        $orders = null;
        $notOrderableColumn = array('status', 'app_count', 'org_count', 'log_url', 'role');
        if (!empty($order)) {
            foreach ($order as $orderInfo) {
                $columnName = $columns[$orderInfo['column']]['data'];
                if (!in_array($columnName, $notOrderableColumn)) {
                    $orders .= $columnName . " " . $orderInfo['dir'];
                }
            }
        }

        $status = $this->params()->fromQuery('status');
        if ($status) {
            $where = $userCollecttion->getQueryByStatus($status, $where);
        }

        if (!empty($keyword)) {
            $where->nest()
                    ->like('firstname', "%{$keyword}%")
                    ->or->like('lastname', "%{$keyword}%")
                    ->or->like('email', "%{$keyword}%")
                    ->unnest();
        }

        $count = $userCollecttion->count($where);

        $allUsers = $userCollecttion->getAll($where, $offset, $limit, $orders);
        $allUsers->buffer();

        $userIds = array();
        foreach ($allUsers as $user) {
            $userIds[] = $user->user_id;
        }

        /**
         * Count number application this user can use.
         */
        $userAppNumber = array();
        $userOrgNumber = array();
        if (!empty($userIds)) {
            /* @var $userOrgCollection \HtAuthentication\Model\UserOrganisationCollection */
            $userOrgCollection = $sl->get('UserOrgCollection');
            $rowset = $userOrgCollection->countOrganisation($userIds);
            foreach ($rowset as $row) {
                $userOrgNumber[$row->user_id] = $row->count_org;
            }

            /* @var $userApplicationCollection \HtAuthentication\Model\UserApplicationCollection */
            $userApplicationCollection = $sl->get('UserAppCollection');

            $results = $userApplicationCollection->countApplication($userIds);
            foreach ($results as $result) {
                $userAppNumber[$result->user_id] = $result->count_apps;
            }
        }

        $items = array();
        foreach ($allUsers as $user) {
            if (array_key_exists($user->user_id, $userOrgNumber)) {
                $user->org_count = $userOrgNumber[$user->user_id];
            } else {
                $user->org_count = 0;
            }

            if (array_key_exists($user->user_id, $userAppNumber)) {
                $user->app_count = $userAppNumber[$user->user_id];
            } else {
                $user->app_count = 0;
            }

            //user's name
            $name = $user->firstname . ' ' . $user->lastname;
            $name = trim($name);
            if (empty($name)) {
                $name = $user->email;
            }

            if (empty($name)) {
                $name = $user->user_id;
            }
            $user->name = $name;

            //user's status
            if ($user->is_deleted) {
                $user->status = $this->translate("deleted");
            } else if ($user->is_enabled) {
                $user->status = $this->translate("enabled");
            } else {
                $user->status = $this->translate("disabled");
            }

            //user's role
            $user->role = $user->role_id;
            $user->log_url = "log";

            $items[] = $user;
        }

        /*
         * Output
         */
        return new JsonModel(array(
            "draw" => intval($page),
            "recordsTotal" => intval($count),
            "recordsFiltered" => intval($count),
            "data" => $items
        ));
    }

    /**
     * Get user model
     * @return \HtUser\Model\User
     */
    protected function getUserModel()
    {
        return $this->getServiceLocator()->get('User');
    }
}
