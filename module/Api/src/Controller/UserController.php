<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Controller\AbstractController;
use Application\Model;
use Application\TableGateway;

class UserController extends AbstractController
{

    public function uploadAction()
    {
        if($_FILES) { 

            try {
                
                // Undefined | Multiple Files | $_FILES Corruption Attack
                // If this request falls under any of them, treat it invalid.
                if (
                    !isset($_FILES['croppedImage']['error']) ||
                    is_array($_FILES['croppedImage']['error'])
                ) {
                    error_log('Invalid parameters.');
                    throw new RuntimeException('Invalid parameters.');
                }

                error_log($_FILES['croppedImage']['size']);

                // Check $_FILES['croppedImage']['error'] value.
                switch ($_FILES['croppedImage']['error']) {
                    case UPLOAD_ERR_OK:
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        error_log('No file sent.');
                        throw new RuntimeException('No file sent.');
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        error_log('Exceeded filesize limit.');
                        throw new RuntimeException('Exceeded filesize limit.');
                    default:
                        error_log('Unknown errors.');
                        throw new RuntimeException('Unknown errors.');
                }

                // You should also check filesize here.
                if ($_FILES['croppedImage']['size'] > 5000000) {
                    error_log('Exceeded filesize limit.');
                    throw new RuntimeException('Exceeded filesize limit.');
                }

                // DO NOT TRUST $_FILES['croppedImage']['mime'] VALUE !!
                // Check MIME Type by yourself.
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                if (false === $ext = array_search(
                    $finfo->file($_FILES['croppedImage']['tmp_name']),
                    array(
                        'jpg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                    ),
                    true
                )) {
                    throw new RuntimeException('Invalid file format.');
                }

                // You should name it uniquely.
                // DO NOT USE $_FILES['croppedImage']['name'] WITHOUT ANY VALIDATION !!
                // On this example, obtain safe unique name from its binary data.
                if (!move_uploaded_file(
                    $_FILES['croppedImage']['tmp_name'],
                    sprintf(getcwd() . '/public/img/avatars/%s.%s',
                        md5($this->getUser()->email . $this->getUser()->id),
                        'png'
                    )
                )) {
                error_log('Failed to move uploaded file.');
                    throw new RuntimeException('Failed to move uploaded file.');
                }

                echo 'File is uploaded successfully.';

            } catch (RuntimeException $e) {
                error_log($e->getMessage());
                echo $e->getMessage();

            }

            $view = new ViewModel(array(
                'result'   => [
                    'success'  => true
                ]
            ));

            $view->setTerminal(true);
            $view->setTemplate('api/default/json.phtml');
            return $view;
        }
    }

    public function deleteHolidayAction()
    {
        $id = $this->params('id', null);
        $holiday = $this->holidayTable->find($id);
        $result = false;
        if (($holiday = $this->holidayTable->find($id)) && $holiday->userId == $this->getUser()->id) {
            $holiday = $this->holidayTable->find($id);
            $this->holidayTable->delete($holiday);
            $result = true;
        }

        $view = new ViewModel(array(
            'result'   => [
                'success'  => $result
            ]
        ));

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }

    public function grantAction()
    {
        $userId  = $this->params('userId', null);
        $groupId = $this->params('groupId', null);
        $status  = $this->params('status', null);

        $userGroupTable = $this->get(TableGateway\UserGroup::class);
        $userGroup = $userGroupTable->fetchOne(['userId' => $userId, 'groupId' => $groupId]);

        $userGroup->admin = $status;
        $userGroupTable->save($userGroup);

        $view = new ViewModel(array(
            'result'   => [
                'success'  => true
            ]
        ));

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }

    public function paramsAction()
    {
        $notifId = $this->params('id', null);
        $status = $this->params('status', null);

        $notifTable = $this->get(TableGateway\Notification::class);
        $notif = $notifTable->find($notifId);
        $notif->status = $status;
        $notifTable->save($notif);

        $view = new ViewModel(array(
            'result'   => [
                'success'  => true
            ]
        ));

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }

    public function displayAction()
    {
        $userTable = $this->get(TableGateway\User::class);

        $display  = $this->params('display', null);
        $this->getUser()->display = $display;
        $userTable->save($this->getUser());

        $view = new ViewModel(array(
            'result'   => [
                'success'  => true
            ]
        ));

        $view->setTerminal(true);
        $view->setTemplate('api/default/json.phtml');
        return $view;
    }
}